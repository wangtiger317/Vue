<?php

namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Platform\Controllers\Core;
use Platform\Controllers\App;
use App\Scopes\AccountScope;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;
use Spatie\Image\Manipulations;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Campaign extends Model implements HasMedia
{
    use GeneratesUuid;
    use HasMediaTrait;

    protected $table = 'campaigns';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
    ];

    /**
     * Appended columns.
     *
     * @var array
     */
    protected $appends = [
      'home_image',
      'home_item1_image',
      'home_item2_image',
      'home_item3_image',
      'earn_header_image',
      'rewards_header_image',
      'contact_header_image',
      'business_text',
      'url',
      'test_url',
      'customer_count',

    ];

    public function registerMediaCollections() {

      $this
        ->addMediaCollection('home_image')
        ->singleFile();

      $this
        ->addMediaCollection('home_item1_image')
        ->singleFile();

      $this
        ->addMediaCollection('home_item2_image')
        ->singleFile();

      $this
        ->addMediaCollection('home_item3_image')
        ->singleFile();

      $this
        ->addMediaCollection('earn_header_image')
        ->singleFile();

      $this
        ->addMediaCollection('rewards_header_image')
        ->singleFile();

      $this
        ->addMediaCollection('contact_header_image')
        ->singleFile();
    }

    public function registerMediaConversions(Media $media = null) {
        $this
          ->addMediaConversion('full_header')
          ->width(1280)
          ->height(1024)
          ->performOnCollections('home_image');

        $this
          ->addMediaConversion('item')
          ->width(640)
          ->height(480)
          ->performOnCollections('home_item1_image', 'home_item2_image', 'home_item3_image');

        $this
          ->addMediaConversion('header')
          ->width(1920)
          ->height(1280)
          ->performOnCollections('earn_header_image', 'rewards_header_image', 'contact_header_image');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['account'];

    /**
     * Field mutators.
     *
     * @var array
     */
    protected $casts = [
      'uuid' => 'uuid',
      'content' => 'json',
      'settings' => 'json',
      'tags' => 'json',
      'attributes' => 'json',
      'meta' => 'json'
    ];

    /**
     * Date/time fields that can be used with Carbon.
     *
     * @return array
     */
    public function getDates() {
      return ['created_at', 'updated_at'];
    }

    public static function boot() {
      parent::boot();

      static::addGlobalScope(new AccountScope(auth()->user()));

      // On update
      static::updating(function ($model) {
        if (auth()->check()) {
          $model->updated_by = auth()->user()->id;

          // Slug
          $model->slug = Str::slug($model->name, '-') . '-' . Core\Secure::staticHash($model->id);

          // Either old host or new host has a value
          if (($model->host !== null || $model->getOriginal('host') !== null) && ($model->host != $model->getOriginal('host'))) {

            if ($model->host === null) { // Delete old host, new host is empty
              App\ServerPilotController::deleteDomain($model->getOriginal('host'), $model->ssl_app_id);
              $model->ssl_app_id = null;

            } elseif ($model->getOriginal('host') === null) { // Old host was empty, new host has value
              // Set SP app id
              if ($model->ssl_app_id === null) {
                $model->ssl_app_id = config('system.serverpilot_app_id');
              }
              App\ServerPilotController::addDomain($model->host, $model->ssl_app_id);
            } else { // Both old and new host have a value
              // Remove $model->getOriginal('host') domain
              App\ServerPilotController::deleteDomain($model->getOriginal('host'), $model->ssl_app_id);

              // Update to latest ServerPilot App
              $model->ssl_app_id = config('system.serverpilot_app_id');

              // Add $model->host domain to ServerPilot
              App\ServerPilotController::addDomain($model->host, $model->ssl_app_id);
            }
          }
        }
      });

      // On create
      self::creating(function ($model) {
        if (auth()->check()) {
          $model->account_id = auth()->user()->account_id;
          $model->created_by = auth()->user()->id;
        }
      });

      // Created
      self::created(function ($model) {
        // Slug
        $model->slug = Str::slug($model->name, '-') . '-' . Core\Secure::staticHash($model->id);

        if (auth()->check()) {
          if (config('system.serverpilot_app_id') !== null) {
            // Check if app id is set for this campaign
            if ($model->ssl_app_id === null) {
              $model->ssl_app_id = config('system.serverpilot_app_id');
            }
            // Add domain to serverpilot
            App\ServerPilotController::addDomain($model->host, $model->ssl_app_id);
          }
        }
        $model->save();
      });

      // Deleted
      self::deleted(function ($model) {
        if (auth()->check()) {
          // Delete domain from ServerPilot SSL
          App\ServerPilotController::deleteDomain($model->host, $model->ssl_app_id);
        }
      });
    }

    /**
     * Form for creating a new record, per role
     *
     * @return array
     */
    public static function getCreateForm() {
      $account = app()->make('account');

      $owner = [
        'tab1' => [
          'text' => __('Geral'),
          'subs' => [
            'sub1' => [
              'items' => [
                ['type' => 'relation', 'relation' => ['type' => 'hasOne', 'with' => 'business', 'pk' => 'id', 'val' => 'name', 'orderBy' => 'name', 'order' => 'asc'], 'column' => 'business_id', 'text' => __('Estabelecimento'), 'validate' => 'required', 'required' => true],
                ['type' => 'text', 'column' => 'name', 'text' => __('Nome da campanha'), 'validate' => 'required|max:64', 'required' => true],
                ['type' => 'number', 'min' => 0, 'default' =>0, 'column' => 'signup_bonus_points', 'text' => __('Pontos que os clientes recebem ao registarem-se'), 'validate' => 'required|integer|min:0|max:10000', 'required' => true, 'hint' => __('Pode inserir 0 se não quiser atribuir pontos quando os clientes se registam.')],
                ['type' => 'text', 'column' => 'host', 'text' => __('Domínio personalizado'), 'validate' => 'nullable|url: {require_protocol: false }|max:128|not_in:domainconfigservice.com,www.domainconfigservice.com|unique:campaigns,host', 'hint' => __('Deixe este campo vazio para ter um endereço web (url) para a sua campanha de imediato. Se quiser, pode ter a sua campanha num domínio/endereço personalizado. Para isso, introduza aqui o seu (sub)domínío sem "http://", por exemplo "campanha.omeunegocio.pt", e adicione um registro CNAME nesse domínio a apontar para "cartaocliente.com". Se pretende que façamos isto por si, por favor envie-nos um email para: support@cartaocliente.com')],
                /*['type' => 'boolean', 'default' => true, 'column' => 'active', 'text' => __('Active'), 'validate' => 'nullable']*/
              ]
            ]
          ]
        ],
        'tab2' => [
          'text' => __('Prémios'),
          'subs' => [
            'sub1' => [
              'items' => [
                ['type' => 'relation', 'relation' => ['type' => 'belongsToMany', 'permission' => 'personal', 'with' => 'rewards', 'remote_pk' => 'reward_id', 'table' => 'rewards', 'pk' => 'id', 'val' => "CONCAT(title, ' (', points_cost ,')')", 'orderBy' => 'points_cost', 'order' => 'asc', 'where' => 'active = 1'], 'text' => __('Prémios'), 'validate' => 'required', 'required' => true]
              ]
            ]
          ]
        ],
        'tab3' => [
          'text' => __('Definições'),
          'subs' => [
            'sub1' => [
              'text' => __('Ganhar pontos'),
              'items' => [
                ['type' => 'description', 'text' => __('Se deseja remover alguma opção para atribuir pontos, pode fazê-lo abaixo. Fazer o scan do QR code é uma forma rápida de atribuir pontos, o cliente mostra o QR code no seu smartphone e o funcionário lê esse QR code com o seu smartphone para atribuir os pontos. É também possível atribuir pontos diretamente ao contacto através do número de cliente ou na lista de contactos.')],
                ['type' => 'boolean', 'default' => true, 'column' => 'settings->claimQr', 'text' => __('Funcionários fazem leitura do QR code'), 'validate' => 'nullable'],
                ['type' => 'boolean', 'default' => true, 'column' => 'settings->claimCode', 'text' => __('Clientes introduzem um código gerado pelo funcionário'), 'validate' => 'nullable'],
                ['type' => 'boolean', 'default' => false, 'column' => 'settings->claimMerchantCode', 'text' => __('O funcionário introduz um código no telemóvel do cliente'), 'validate' => 'nullable'],
                ['type' => 'boolean', 'default' => false, 'column' => 'settings->claimCustomerNumber', 'text' => __('O cliente diz o número de cliente ao funcionário'), 'validate' => 'nullable'],
                ['type' => 'text', 'default' =>null, 'column' => 'pre_points', 'text' => __('Ter o número de pontos a atribuir pré-preenchido?'), 'validate' => 'nullable|max:10000',  'hint' => __('Deixe vazio se não quiser ter pontos pré-preenchidos.')],
              ]
            ],
            'sub2' => [
              'text' => __('Trocar Prémios'),
              'description' => __('Como podem os clientes reclamar prémios?'),
              'items' => [
                ['type' => 'boolean', 'default' => true, 'column' => 'settings->redeemQr', 'text' => __('O funcionário lê o QR code no smartphone do cliente'), 'validate' => 'nullable'],
                ['type' => 'boolean', 'default' => false, 'column' => 'settings->redeemMerchantCode', 'text' => __('O funcionário introduz um código no smartphone do cliente'), 'validate' => 'nullable'],
                ['type' => 'boolean', 'default' => false, 'column' => 'settings->redeemCustomerNumber', 'text' => __('O cliente diz o número de cliente ao funcionário'), 'validate' => 'nullable']
              ]
            ],
            'sub3' => [
              'text' => __('QR CODE NO CARTÃO'),
              'items' => [
                ['type' => 'description', 'text' => __('Por defeito o QR code presente no cartão é para "Trocar Prémios". Selecione abaixo se pretende que o QR code seja para "Ganhar Pontos".')],
                ['type' => 'boolean', 'default' => false, 'column' => 'earn_point', 'text' => __('QR code presente no Cartão é para "Ganhar Pontos"'), 'validate' => 'nullable']
              ]
            ]
          ]
        ],
        'tab4' => [
          'text' => __('Conteúdo'),
          'subs' => [
            'sub1' => [
              'text' => __('Barra do topo'),
              'items' => [
                ['type' => 'text', 'column' => 'content->campaignTitle', 'text' => __('Título do topo'), 'hint' => __('Este título irá aparecer junto do logótipo (se existir) e irá substituir o nome da campanha, que é usado por defeito. Se não houver um logótipo importando no Estabelecimento, irá surgir este título.'), 'validate' => 'nullable|max:64'],
                ['type' => 'text', 'column' => 'content->campaignHeadline', 'text' => __('Linha por baixo do título'), 'validate' => 'nullable|max:64'],
              ]
            ],
            'sub2' => [
              'text' => __('Homepage'),
              'items' => [
                ['type' => 'text', 'column' => 'content->homeHeaderTitle', 'default' => 'Bem-vindo ao nosso programa de fidelização', 'text' => __('Título'), 'validate' => 'required|max:128', 'required' => true],
                ['type' => 'wysiwyg', 'column' => 'content->homeHeaderContent', 'default' => '<p>Poderá ganhar pontos sempre que compra connosco e depois poderá trocá-los por prémios do nosso catálogo abaixo.</p>', 'config' => ['toolbar' => ['bold', 'italic', '|', 'link']], 'text' => __('Header content'), 'validate' => 'nullable'],
                ['type' => 'image', 'column' => 'home_image', 'image' => ['thumb_max_width' => '180px', 'thumb_max_height' => '120px'], 'text' => __('Imagem do topo'), 'validate' => 'nullable', 'icon' => 'attach_file'],
                ['type' => 'description', 'text' => __('A homepage da campanha pode ter três (opcional) colunas com conteúdo. Cada coluna tem um título, um texto e uma imagem.')],
                ['type' => 'text', 'column' => 'content->homeBlocksTitle', 'text' => __('Título das colunas'), 'validate' => 'nullable|max:128'],
                ['type' => 'text', 'column' => 'content->homeBlock1Title', 'text' => __('Título da primeira coluna'), 'validate' => 'nullable|max:128'],
                ['type' => 'wysiwyg', 'column' => 'content->homeBlock1Text', 'config' => ['toolbar' => ['bold', 'italic', '|', 'link']], 'text' => __('Column one text'), 'validate' => 'nullable'],
                ['type' => 'image', 'column' => 'home_item1_image', 'image' => ['thumb_max_width' => '180px', 'thumb_max_height' => '120px'], 'text' => __('Imagem da primeira coluna'), 'validate' => 'nullable', 'icon' => 'attach_file'],
                ['type' => 'text', 'column' => 'content->homeBlock2Title', 'text' => __('Título da segunda coluna'), 'validate' => 'nullable|max:128'],
                ['type' => 'wysiwyg', 'column' => 'content->homeBlock2Text', 'config' => ['toolbar' => ['bold', 'italic', '|', 'link']], 'text' => __('Column two text'), 'validate' => 'nullable'],
                ['type' => 'image', 'column' => 'home_item2_image', 'image' => ['thumb_max_width' => '180px', 'thumb_max_height' => '120px'], 'text' => __('Imagem da segunda coluna'), 'validate' => 'nullable', 'icon' => 'attach_file'],
                ['type' => 'text', 'column' => 'content->homeBlock3Title', 'text' => __('Título da terceira coluna'), 'validate' => 'nullable|max:128'],
                ['type' => 'wysiwyg', 'column' => 'content->homeBlock3Text', 'config' => ['toolbar' => ['bold', 'italic', '|', 'link']], 'text' => __('Column three text'), 'validate' => 'nullable'],
                ['type' => 'image', 'column' => 'home_item3_image', 'image' => ['thumb_max_width' => '180px', 'thumb_max_height' => '120px'], 'text' => __('Imagem da terceira coluna'), 'validate' => 'nullable', 'icon' => 'attach_file'],
              ]
            ],
            'sub3' => [
              'text' => __('Pontos'),
              'items' => [
                ['type' => 'text', 'column' => 'content->earnHeaderTitle', 'default' => 'Ganhe Pontos', 'text' => __('Título'), 'validate' => 'required|max:128', 'required' => true],
                ['type' => 'wysiwyg', 'column' => 'content->earnHeaderContent', 'default' => '<p>Ganhe pontos por cada euro que compra.</p>', 'config' => ['toolbar' => ['bold', 'italic', '|', 'link']], 'text' => __('Header content'), 'validate' => 'nullable'],
                ['type' => 'image', 'column' => 'earn_header_image', 'image' => ['thumb_max_width' => '180px', 'thumb_max_height' => '120px'], 'text' => __('Imagem do topo'), 'validate' => 'nullable', 'icon' => 'attach_file'],
              ]
            ],
            'sub4' => [
              'text' => __('Prémios'),
              'items' => [
                ['type' => 'text', 'column' => 'content->rewardsHeaderTitle', 'default' => 'Prémios', 'text' => __('Título'), 'text' => __('Título'), 'validate' => 'required|max:128', 'required' => true],
                ['type' => 'wysiwyg', 'column' => 'content->rewardsHeaderContent', 'default' => '<p>Ganhe pontos e troque-os por estes prémios.</p>', 'config' => ['toolbar' => ['bold', 'italic', '|', 'link']], 'text' => __('Header content'), 'validate' => 'nullable'],
                ['type' => 'image', 'column' => 'rewards_header_image', 'image' => ['thumb_max_width' => '180px', 'thumb_max_height' => '120px'], 'text' => __('Imagem do topo'), 'validate' => 'nullable', 'icon' => 'attach_file'],
              ]
            ],
            'sub5' => [
              'text' => __('Contacto'),
              'items' => [
                ['type' => 'text', 'column' => 'content->contactHeaderTitle', 'default' => 'Contacte-nos', 'text' => __('Título'), 'validate' => 'required|max:128', 'required' => true],
                ['type' => 'wysiwyg', 'column' => 'content->contactHeaderContent', 'default' => '<p>Qualquer assunto, estes são os nossos contactos.</p>', 'config' => ['toolbar' => ['bold', 'italic', '|', 'link']], 'text' => __('Header content'), 'validate' => 'nullable'],
                ['type' => 'image', 'column' => 'contact_header_image', 'image' => ['thumb_max_width' => '180px', 'thumb_max_height' => '120px'], 'text' => __('Imagem do topo'), 'validate' => 'nullable', 'icon' => 'attach_file'],
              ]
            ]
          ]
        ],
        'tab5' => [
          'text' => __('Cores'),
          'subs' => [
            'sub1' => [
              'text' => __('Fundo'),
              'description' => __('Cores do fundo do site (background) e texto.'),
              'items' => [
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_backgroundColor', 'default' => '#EEEEEE', 'text' => __('Fundo'), 'validate' => 'required', 'required' => true],
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_textColor', 'default' => '#333333', 'text' => __('Texto'), 'validate' => 'required', 'required' => true]

              ]
            ],
            'sub2' => [
              'text' => __('Primária'),
              'description' => __('Barra do topo e rodapé'),
              'items' => [
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_primaryColor', 'default' => '#111111', 'text' => __('Fundo'), 'validate' => 'required', 'required' => true],
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_primaryTextColor', 'default' => '#ffffff', 'text' => __('Texto'), 'validate' => 'required', 'required' => true]

              ]
            ],
            'sub3' => [
              'text' => __('Secundária'),
              'description' => __('Fundo do cabeçalho e rodapé com os links das redes sociais. Transparência para a imagem.'),
              'items' => [
                ['type' => 'slider', 'min' => 0, 'max' => 100, 'step' => 1, 'column' => 'settings->theme_headerOpacity', 'default' => 85, 'text' => __('Transparência do topo'), 'validate' => 'required'],
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_secondaryColor', 'default' => '#0D47A1', 'text' => __('Background'), 'validate' => 'required', 'required' => true],
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_secondaryTextColor', 'default' => '#ffffff', 'text' => __('Texto'), 'validate' => 'required', 'required' => true]

              ]
            ],
            'sub4' => [
              'text' => __('Menu mobile'),
              'description' => __('Menu da versão mobile.'),
              'items' => [
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_drawer_backgroundColor', 'default' => '#333333', 'text' => __('Background'), 'validate' => 'required', 'required' => true],
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_drawer_textColor', 'default' => '#eeeeee', 'text' => __('Texto'), 'validate' => 'required', 'required' => true],
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_drawer_highlightBackgroundColor', 'default' => '#222222', 'text' => __('Menu ativo background'), 'validate' => 'required', 'required' => true],
                ['type' => 'color', 'mode' => 'hexa', 'column' => 'settings->theme_drawer_highlightTextColor', 'default' => '#ffffff', 'text' => __('Menu ativo texto'), 'validate' => 'required', 'required' => true],

              ]
            ]
          ]
        ]
      ];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Name used in plan limitations (optional)
     *
     * @return string
     */
    public static function getLimitationName() {
      return 'campaigns';
    }

    /**
     * Columns used for filters
     *
     * @return array
     */
    public static function getTableFilters() {
      $owner = [
        ['column' => 'business_id', 'text' => __('Todos os negócios'), 'icon' => 'filter_list', 'type' => 'relation', 'default' => null, 'relation' => ['type' => 'hasOne', 'permission' => 'personal', 'with' => 'business', 'table' => 'businesses', 'pk' => 'id', 'val' => 'name', 'orderBy' => 'name', 'order' => 'asc']]
      ];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Extra columns used in select queries, exposed in json response
     *
     * @return array
     */
    public static function getExtraSelectColumns() {
      $owner = ['uuid', 'business_id', 'host'];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Extra columns used in select queries, hidden from json response
     *
     * @return array
     */
    public static function getExtraQueryColumns() {
      $owner = ['id', 'account_id', 'created_by', 'slug'];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Extra with-queries used in select queries
     *
     * @return array
     */
    public static function getExtraWithQueries() {
      $owner = [];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Generic settings
     *
     * actions: add actions column (true / false)
     *
     * @return array
     */
    public static function getSettings() {
      $owner = ['select_all' => true, 'actions' => true, 'create' => true, 'actions_width' => '90px', 'dialog_width' => 640, 'dialog_height' => 340];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Language variables
     *
     * @return array
     */
    public static function getTranslations() {
      return [
        'items' => __('Campanhas'),
        'edit_item' => __('Editar campanha'),
        'create_item' => __('Criar campanha'),
      ];
    }

    /**
     * Define per role if and what they can see
     *
     * all: all records from all accounts
     * account: all records from the current account
     * personal: only records the current user has created
     * created_by: only records created by the user id defined like created_by:1
     * none: this role has no permission
     *
     * @return array
     */
    public static function getPermissions() {
      $owner = ['view' => 'all', 'delete' => 'all', 'update' => 'all', 'create' => true];
      $reseller = ['view' => 'account', 'delete' => 'account', 'update' => 'account', 'create' => false];
      $user = ['view' => 'personal', 'delete' => 'personal', 'update' => 'personal', 'create' => true];

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * The headers for the data table, per role
     *
     * @return array
     */
    public static function getHeaders() {
      $owner = [
        ['visible' => true, 'value' => 'business_text', 'exclude_from_select' => true, 'relation' => ['type' => 'hasOne', 'with' => 'business', 'table' => 'businesses', 'val' => 'name'], 'text' => __('Estabelecimento'), 'align' => 'left', 'sortable' => false],
        ['visible' => true, 'value' => 'name', 'text' => __('Campanha'), 'align' => 'left', 'sortable' => true],
        ['visible' => true, 'value' => 'signup_bonus_points', 'type' => 'number', 'text' => __('Bónus: cliente regista-se'), 'align' => 'right', 'sortable' => true],
        ['visible' => true, 'value' => 'customer_count', 'exclude_from_select' => true, 'type' => 'number', 'text' => __('Clientes'), 'align' => 'right', 'sortable' => false],
        ['visible' => true, 'value' => 'url', 'exclude_from_select' => true, 'type' => 'campaign_link', 'text' => __('Endereços da campanha'), 'align' => 'left', 'sortable' => false]
      ];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * The columns used for searching the table
     *
     * @return array
     */
    public static function getSearchColumns() {
      $owner = ['name'];
      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Available actions for data table row, per role
     *
     * @return array
     */
    public static function getActions() {
      $owner = [
        ['text' => __('Editar'), 'action' => 'edit', 'icon' => 'edit', 'color' => 'secondary', 'dark' => false],
        /*['divider'],*/
        ['text' => __('Eliminar'), 'action' => 'delete', 'icon' => 'delete', 'color' => 'secondary', 'dark' => true]
      ];

      $reseller = [
        ['text' => __('Editar'), 'action' => 'edit', 'icon' => 'edit', 'color' => 'secondary', 'dark' => false]
      ];

      $reseller = $owner;
      $user = $owner;

      return [
        1 => $owner,
        2 => $reseller,
        3 => $user
      ];
    }

    /**
     * Get business name
     *
     * @return string
     */
    public function getBusinessTextAttribute() {
      return ($this->business != null) ? $this->business->name : null;
    }

    /**
     * Get popular rewards for campaign
     *
     * @return array
     */
    public function getPopularRewards($limit = 4) {
      $rewards = $this->rewards()
        ->where('active_from', '<', \Carbon\Carbon::now()->toDateTimeString())
        ->where('expires_at', '>', \Carbon\Carbon::now()->toDateTimeString())
        ->where('active', 1)
        ->orderBy('number_of_times_redeemed', 'desc')
        ->orderBy('points_cost', 'desc')
        ->limit($limit)
        ->get();

      $rewards = $rewards->map(function ($record) {
        $record->img = $record->main_image_thumb ?? null;
        $record->points = $record->points_cost;
        $record->description = substr(strip_tags($record->description), 0, 60);
        return collect($record)->only('title', 'description', 'points', 'img');
      });

      return $rewards->toArray();
    }

    /**
     * Get all rewards for campaign
     *
     * @return array
     */
    public function getAllRewards($sortBy = 'points_cost', $sortDirection = 'desc') {
      $rewards = $this->rewards()
        ->where('active_from', '<', \Carbon\Carbon::now()->toDateTimeString())
        ->where('expires_at', '>', \Carbon\Carbon::now()->toDateTimeString())
        ->where('active', 1)
        ->orderBy($sortBy, $sortDirection)
        ->get();

      $rewards = $rewards->map(function ($record) {

        $images = [];
        if ($record->main_image !== null) $images[] = ['href' => $record->main_image, 'thumb' => $record->main_image_thumb];
        if ($record->image1 !== null) $images[] = ['href' => $record->image1, 'thumb' => $record->image1_thumb];
        if ($record->image2 !== null) $images[] = ['href' => $record->image2, 'thumb' => $record->image2_thumb];
        if ($record->image3 !== null) $images[] = ['href' => $record->image3, 'thumb' => $record->image3_thumb];
        if ($record->image4 !== null) $images[] = ['href' => $record->image4, 'thumb' => $record->image4_thumb];

        $record->images = $images;

        // In how many months does the reward expire?
        $expiresInMonths = $record->expires_at->diffInMonths(Carbon::now());
        $record->expiresInMonths = $expiresInMonths;

        $record->points = $record->points_cost;

        return collect($record)->only('uuid', 'title', 'description', 'points', 'expires_at', 'expiresInMonths', 'images');
      });

      return $rewards->toArray();
    }

    /**
     * Get active rewards for campaign
     *
     * @return string
     */
    public function getAciveRewards($sortBy = 'points_cost', $sortDirection = 'asc') {
      $rewards = $this->rewards()
        ->where('active_from', '<', \Carbon\Carbon::now()->toDateTimeString())
        ->where('expires_at', '>', \Carbon\Carbon::now()->toDateTimeString())
        ->where('active', 1)
        ->orderBy($sortBy, $sortDirection)
        ->get();

      return $rewards;
    }

    /**
     * Get campaign internal test url
     *
     * @return string
     */
    public function getTestUrlAttribute() {
      return ($this->account != null) ? '//' . $this->account->app_host . '/campaign/' . $this->slug : null;
    }

    /**
     * Get campaign url, returns test url if domain is not configured
     *
     * @return string
     */
    public function getUrlAttribute() {
      return ($this->host === null) ? $this->getTestUrlAttribute() : '//' . $this->host;
    }

    /**
     * Get the number of customers
     *
     * @return integer
     */
    public function getCustomerCountAttribute() {
      return $this->customers->count();
    }

    /**
     * Images
     * -------------
     */

    public function getHomeImageAttribute() {
      return ($this->getFirstMediaUrl('home_image') !== '') ? $this->getMedia('home_image')[0]->getUrl('full_header') : null;
      //return ($this->getFirstMediaUrl('home_image') !== '') ? $this->getFirstMediaUrl('home_image') : null;
    }

    public function getHomeImageThumbAttribute() {
      return ($this->getFirstMediaUrl('home_image') !== '') ? $this->getMedia('home_image')[0]->getUrl('thumb') : null;
    }

    public function getHomeItem1ImageAttribute() {
      return ($this->getFirstMediaUrl('home_item1_image') !== '') ? $this->getMedia('home_item1_image')[0]->getUrl('item') : null;
      //return ($this->getFirstMediaUrl('home_item1_image') !== '') ? $this->getFirstMediaUrl('home_item1_image') : null;
    }

    public function getHomeItem2ImageAttribute() {
      return ($this->getFirstMediaUrl('home_item2_image') !== '') ? $this->getMedia('home_item2_image')[0]->getUrl('item') : null;
      //return ($this->getFirstMediaUrl('home_item2_image') !== '') ? $this->getFirstMediaUrl('home_item2_image') : null;
    }

    public function getHomeItem3ImageAttribute() {
      return ($this->getFirstMediaUrl('home_item3_image') !== '') ? $this->getMedia('home_item3_image')[0]->getUrl('item') : null;
      //return ($this->getFirstMediaUrl('home_item3_image') !== '') ? $this->getFirstMediaUrl('home_item3_image') : null;
    }

    public function getEarnHeaderImageAttribute() {
      return ($this->getFirstMediaUrl('earn_header_image') !== '') ? $this->getMedia('earn_header_image')[0]->getUrl('header') : null;
      //return ($this->getFirstMediaUrl('earn_header_image') !== '') ? $this->getFirstMediaUrl('earn_header_image') : null;
    }

    public function getRewardsHeaderImageAttribute() {
      return ($this->getFirstMediaUrl('rewards_header_image') !== '') ? $this->getMedia('rewards_header_image')[0]->getUrl('header') : null;
      //return ($this->getFirstMediaUrl('rewards_header_image') !== '') ? $this->getFirstMediaUrl('rewards_header_image') : null;
    }

    public function getContactHeaderImageAttribute() {
      return ($this->getFirstMediaUrl('contact_header_image') !== '') ? $this->getMedia('contact_header_image')[0]->getUrl('header') : null;
      //return ($this->getFirstMediaUrl('contact_header_image') !== '') ? $this->getFirstMediaUrl('contact_header_image') : null;
    }

    /**
     * Get selected claim options
     *
     * @return array
     */
    public function getClaimOptions() {
      $options = [];

      if ((boolean) $this->settings['claimQr'] ?? false) $options[] = 'qr';
      if ((boolean) $this->settings['claimCode'] ?? false) $options[] = 'code';
      if ((boolean) $this->settings['claimMerchantCode'] ?? false) $options[] = 'merchant';
      if ((boolean) $this->settings['claimCustomerNumber'] ?? false) $options[] = 'customerNumber';

      return $options;
    }

    /**
     * Get selected redeem options
     *
     * @return array
     */
    public function getRedeemOptions() {
      $options = [];

      if ((boolean) $this->settings['redeemQr'] ?? false) $options[] = 'qr';
      if ((boolean) $this->settings['redeemMerchantCode'] ?? false) $options[] = 'merchant';
      if ((boolean) $this->settings['redeemCustomerNumber'] ?? false) $options[] = 'customerNumber';

      return $options;
    }

    /**
     * Get campaign homepage blocks
     *
     * @return array
     */
    public function getHomeBlocks() {
      $blocks = [];

      if (isset($this->content['homeBlock1Title']) || isset($this->content['homeBlock1Text']) || $this->home_item1_image !== null) {
        $blocks[] = [
          'img' => $this->home_item1_image,
          'title' => $this->content['homeBlock1Title'] ?? null,
          'text' => $this->content['homeBlock1Text'] ?? null,
        ];
      }

      if (isset($this->content['homeBlock2Title']) || isset($this->content['homeBlock2Text']) || $this->home_item2_image !== null) {
        $blocks[] = [
          'img' => $this->home_item2_image,
          'title' => $this->content['homeBlock2Title'] ?? null,
          'text' => $this->content['homeBlock2Text'] ?? null,
        ];
      }

      if (isset($this->content['homeBlock3Title']) || isset($this->content['homeBlock3Text']) || $this->home_item3_image !== null) {
        $blocks[] = [
          'img' => $this->home_item3_image,
          'title' => $this->content['homeBlock3Title'] ?? null,
          'text' => $this->content['homeBlock3Text'] ?? null,
        ];
      }

      return $blocks;
    }

    /**
     * Get array to generate campaign website
     *
     * @return array
     */
    public function getCampaignWebsite() {
      $root = $this->host;
      if ($root === null) $root = $this->account->app_host . '/campaign/' . $this->slug;

      $headerOpacity = $this->settings['theme_headerOpacity'] ?? 85;

      $topTitle = null;
      if ($this->business->logo === null) $topTitle = $this->name;
      if ($this->content['campaignTitle'] ?? null !== null) $topTitle = $this->content['campaignTitle'];

      $website = [
        'uuid' => $this->uuid,
        'name' => $this->name,
        'title' => $topTitle,
        'headline' => $this->content['campaignHeadline'] ?? null,
        'slug' => $this->slug,
        'scheme' => request()->getScheme(),
        'host' => $this->host,
        'root' => $root,
        'theme' => [
          'logo' => $this->business->logo,
          'backgroundColor' => $this->settings['theme_backgroundColor'] ?? '#EEEEEE',
          'textColor' => $this->settings['theme_textColor'] ?? '#333333',
          'primaryColor' => $this->settings['theme_primaryColor'] ?? '#111111',
          'primaryTextColor' => $this->settings['theme_primaryTextColor'] ?? '#FFFFFF',
          'secondaryColor' => $this->settings['theme_secondaryColor'] ?? '#0D47A1',
          'secondaryTextColor' => $this->settings['theme_secondaryTextColor'] ?? '#FFFFFF',
          'drawer' => [
            'backgroundColor' => $this->settings['theme_drawer_backgroundColor'] ?? '#333333',
            'textColor' => $this->settings['theme_drawer_textColor'] ?? '#EEEEEE',
            'highlightBackgroundColor' => $this->settings['theme_drawer_highlightBackgroundColor'] ?? '#222222',
            'highlightTextColor' => $this->settings['theme_drawer_highlightTextColor'] ?? '#FFFFFF'
          ]
        ],
        'business' => [
          'name' => $this->business->name
        ],
        'externalUrls' => $this->business->getExternalLinks(),
        'footer' => [
          'text' => $this->business->social['text'] ?? null,
          'links' => $this->business->getSocialLinks()
        ],
        'home' => [
          'headerHeight' => 360,
          'headerImg' => $this->home_image,
          'headerTitle' => $this->content['homeHeaderTitle'] ?? null,
          'headerContent' => $this->content['homeHeaderContent'] ?? null,
          'rewardsTitle' => __('Prémios'),
          'rewardsImgRatio' => 1.75,
          'rewards' => $this->getPopularRewards(),
          'blocksTitle' => $this->content['homeBlocksTitle'] ?? null,
          'blocksImgRatio' => 1.77,
          'blocks' => $this->getHomeBlocks()
        ],
        'claimOptions' => $this->getClaimOptions(),
        'earn' => [
          'headerHeight' => 200,
          'headerOpacity' => $headerOpacity / 100,
          'headerImg' => $this->earn_header_image,
          'headerTitle' => $this->content['earnHeaderTitle'] ?? null,
          'headerContent' => $this->content['earnHeaderContent'] ?? null,
          'pageTitle' => __('Ganhe pontos')
        ],
        'redeemOptions' => $this->getRedeemOptions(),
        'rewards' => [
          'headerHeight' => 200,
          'headerOpacity' => $headerOpacity / 100,
          'headerImg' => $this->rewards_header_image,
          'headerTitle' => $this->content['rewardsHeaderTitle'] ?? null,
          'headerContent' => $this->content['rewardsHeaderContent'] ?? null,
          'imageRatio' => 1.75,
          'list' => $this->getAllRewards()
        ],
        'contact' => [
          'headerHeight' => 200,
          'headerOpacity' => $headerOpacity / 100,
          'headerImg' => $this->contact_header_image,
          'headerTitle' => $this->content['contactHeaderTitle'] ?? null,
          'headerContent' => $this->content['contactHeaderContent'] ?? null,
          'methods' => $this->business->getContactMethods()/*,
          'features' => [
            [
              'icon' => 'local_activity',
              'title' => 'Takes Reservations',
              'value' => 'Yes'
            ],
          ]*/
        ],
        'card' => [
            'headerHeight' => 112,
            'headerOpacity' => $headerOpacity / 100,
            'headerImg' => $this->contact_header_image,
            'headerTitle' => $this->content['cardHeaderTitle'] ?? null,
            'headerContent' => $this->content['contactHeaderContent'] ?? null,
            'methods' => $this->business->getContactMethods()/*,
        'features' => [
          [
            'icon' => 'local_activity',
            'title' => 'Takes Reservations',
            'value' => 'Yes'
          ],
        ]*/
        ]
      ];

      return $website;
    }

    /**
     * Relationships
     * -------------
     */

    public function account() {
      return $this->belongsTo(\App\User::class, 'account_id', 'id');
    }

    public function user() {
      return $this->belongsTo(\App\User::class, 'created_by', 'id');
    }

    public function customers() {
      return $this->hasMany(\App\Customer::class, 'campaign_id', 'id');
    }

    public function business() {
      return $this->hasOne(\Platform\Models\Business::class, 'id', 'business_id');
    }

    public function rewards() {
      return $this->belongsToMany(\Platform\Models\Reward::class, 'campaign_reward', 'campaign_id', 'reward_id');
    }
}
