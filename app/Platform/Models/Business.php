<?php

namespace Platform\Models;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Support\GeneratesUuid;
use Platform\Controllers\Core;
use App\Scopes\AccountScope;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class Business extends Model implements HasMedia
{
    use GeneratesUuid;
    use HasMediaTrait;

    protected $table = 'businesses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'account_id',
      'active',
      'name',
    ];

    /**
     * Appended columns.
     *
     * @var array
     */
    protected $appends = [
      'logo'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * Field mutators.
     *
     * @var array
     */
    protected $casts = [
      'uuid' => 'uuid',
      'content' => 'json',
      'social' => 'json',
      'settings' => 'json',
      'tags' => 'json',
      'attributes' => 'json',
      'meta' => 'json'
    ];

    public function registerMediaCollections() {
      $this
        ->addMediaCollection('logo')
        ->singleFile();
    }

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
        }
      });

      // On create
      self::creating(function ($model) {
        if (auth()->check()) {
          $model->account_id = auth()->user()->account_id;
          $model->created_by = auth()->user()->id;
        }
      });
    }

    /**
     * Form for creating a new record, per role
     *
     * @return array
     */
    public static function getCreateForm() {
      $owner = [
        'tab1' => [
          'text' => __('Geral'),
          'subs' => [
            'sub1' => [
              'items' => [
                ['type' => 'relation', 'relation' => ['type' => 'hasOne', 'permission' => 'all', 'with' => 'industry', 'pk' => 'id', 'val' => 'name', 'orderBy' => 'name', 'order' => 'asc'], 'column' => 'industry_id', 'text' => __('Setor'), 'validate' => 'required', 'required' => true],
                ['type' => 'text', 'column' => 'name', 'text' => __('Nome do estabelecimento'), 'validate' => 'required|max:32', 'required' => true],
                ['type' => 'image', 'image' => ['thumb_max_width' => '140px', 'thumb_max_height' => '100px'], 'column' => 'logo', 'text' => __('Logótipo'), 'validate' => 'nullable', 'icon' => 'attach_file'],
                /*['type' => 'boolean', 'default' => false, 'column' => 'is_online_business', 'text' => __('Is online business'), 'validate' => 'nullable']*/
              ]
            ]
          ]
        ],
        'tab2' => [
          'text' => __('Contactos'),
          'subs' => [
            'sub1' => [
              'text' => __('Geral'),
              'items' => [
                ['type' => 'email', 'column' => 'email', 'text' => __('Endereço de e-mail'), 'validate' => 'required|email|max:64', 'icon' => 'mail', 'required' => true],
                ['type' => 'text', 'column' => 'phone', 'text' => __('Telefone'), 'validate' => 'nullable|max:32', 'icon' => 'phone'],
                ['type' => 'text', 'column' => 'website', 'text' => __('Website'), 'validate' => 'nullable|max:64', 'icon' => 'language']
              ]
            ],
            'sub2' => [
              'text' => __('Morada'),
              'items' => [
                ['type' => 'text', 'column' => 'street1', 'text' => __('Rua'), 'validate' => 'nullable|max:64'],
                ['type' => 'text', 'column' => 'postal_code', 'text' => __('Código postal'), 'validate' => 'nullable|max:32'],
                ['type' => 'text', 'column' => 'city', 'text' => __('Cidade'), 'validate' => 'nullable|max:64'],
                ['type' => 'text', 'column' => 'state', 'text' => __('Distrito'), 'validate' => 'nullable|max:64']
              ]
            ]
          ]
        ],
        'tab3' => [
          'text' => __('Social'),
          'description' => __('Links para as suas redes sociais. Isto será mostrado no rodapé do site da campanha.'),
          'subs' => [
            'sub1' => [
              'items' => [
                ['type' => 'text', 'column' => 'social->text', 'default' => 'Siga-nos nas Redes Sociais', 'text' => 'Text', 'validate' => 'nullable|max:250', 'hint' => 'Este texto é mostrado no rodapé do site da
campanha.'],
                ['type' => 'text', 'column' => 'social->facebook', 'text' => 'Facebook', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-facebook'],
                ['type' => 'text', 'column' => 'social->youtube', 'text' => 'YouTube', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-youtube'],
                ['type' => 'text', 'column' => 'social->vimeo', 'text' => 'Vimeo', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-vimeo'],
                ['type' => 'text', 'column' => 'social->whatsapp', 'text' => 'WhatsApp', 'placeholder' => 'https://wa.me/<number>', 'validate' => 'nullable|url: {require_protocol: true }|max:64', 'icon' => 'fab fa-whatsapp'],
                ['type' => 'text', 'column' => 'social->instagram', 'text' => 'Instagram', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-instagram'],
                ['type' => 'text', 'column' => 'social->twitter', 'text' => 'Twitter', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-twitter'],
                ['type' => 'text', 'column' => 'social->linkedin', 'text' => 'LinkedIn', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-linkedin'],
                ['type' => 'text', 'column' => 'social->tumblr', 'text' => 'Tumblr', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-tumblr'],
                ['type' => 'text', 'column' => 'social->snapchat', 'text' => 'Snapchat', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-snapchat'],
                ['type' => 'text', 'column' => 'social->pinterest', 'text' => 'Pinterest', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-pinterest'],
                ['type' => 'text', 'column' => 'social->telegram', 'text' => 'Telegram', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-telegram'],
                ['type' => 'text', 'column' => 'social->medium', 'text' => 'Medium', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'fab fa-medium']
              ]
            ]
          ]
        ],
        'tab4' => [
          'text' => __('Links'),
          'description' => __('Os links que serão mostrados no rodapé do site da campanha. Por exemplo links para a home do seu
site ou para uma página com os termos e condições desta campanha de fidelização.'),
          'subs' => [
            'sub1' => [
              'items' => [
                ['type' => 'text', 'column' => 'content->text1', 'text' => 'Texto para o link #1',  'validate' => 'nullable|max:120', 'icon' => 'title'],
                ['type' => 'text', 'column' => 'content->href1', 'text' => 'Url do link #1', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'link'],
                ['type' => 'text', 'column' => 'content->text2', 'default' => 'Aplicação', 'text' => 'Texto para o link #2', 'validate' => 'nullable|max:120', 'icon' => 'title'],
                ['type' => 'text', 'column' => 'content->href2', 'default' => 'https://cartaocliente.com/more/app.html', 'text' => 'Url do link #2', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'link'],
                ['type' => 'text', 'column' => 'content->text3', 'default' => 'Criado com CartaoCliente.com', 'text' => 'Texto para o link #3', 'validate' => 'nullable|max:120', 'icon' => 'title'],
                ['type' => 'text', 'column' => 'content->href3', 'default' => 'https://cartaocliente.com/more/', 'text' => 'Url do link #3', 'validate' => 'nullable|url: {require_protocol: true }|max:512', 'icon' => 'link']
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
      return 'businesses';
    }

    /**
     * Extra columns used in select queries, exposed in json response
     *
     * @return array
     */
    public static function getExtraSelectColumns() {
      $owner = ['uuid'];
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
      $owner = ['id', 'created_by'];
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
      $owner = ['select_all' => true, 'actions' => true, 'create' => true, 'actions_width' => '90px', 'dialog_height' => 375];
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
        'items' => __('Estabelecimentos'),
        'edit_item' => __('Editar estabelecimento'),
        'create_item' => __('Criar estabelecimento'),
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
        ['visible' => true, 'value' => 'name', 'text' => __('Estabelecimento'), 'align' => 'left', 'sortable' => true],
        ['visible' => true, 'value' => 'email', 'text' => __('E-mail'), 'align' => 'left', 'sortable' => true],
        ['visible' => true, 'value' => "address|CONCAT(street1, ' ', postal_code, ' ', city) as address", 'text' => __('Morada'), 'align' => 'left', 'sortable' => false],
        ['visible' => true, 'value' => 'phone', 'text' => __('Telefone'), 'align' => 'left', 'sortable' => false],
        /*['visible' => true, 'value' => 'is_online_business', 'text' => __('Online business'), 'align' => 'center', 'sortable' => true, 'type' => 'boolean']*/
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
      $owner = ['name', 'email'];
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
     * Get contact methods.
     *
     * @return array
     */
    public function getContactMethods() {
      $contactMethods = [];

      if ($this->phone !== null) {
        $contactMethods[] = [
          'icon' => 'phone',
          'title' => $this->phone,
          'subtitle' => 'call_us',
          'link' => 'tel:' . str_replace([' ', '(', ')', '-'], '', $this->phone),
          'target' => '_self'
        ];
      }

      if ($this->email !== null) {
        $contactMethods[] = [
          'icon' => 'mail',
          'title' => $this->email,
          'subtitle' => 'send_us_a_mail',
          'link' => 'mailto:' . $this->email,
          'target' => '_self'
        ];
      }

      if ($this->website !== null) {
        $naked_url = str_replace(['http://', 'https://'], '', $this->website);

        $contactMethods[] = [
          'icon' => 'language',
          'title' => $naked_url,
          'subtitle' => 'visit_our_website',
          'link' => '//' . $naked_url,
          'target' => '_blank'
        ];
      }

      if ($this->street1 !== null && $this->city !== null) {
        $address = $this->city;
        if ($this->postal_code !== null) $address .= ', ' . $this->postal_code;

        $contactMethods[] = [
          'icon' => 'location_on',
          'translate' => false,
          'title' => $this->street1,
          'subtitle' => $address,
          'link' => 'https://www.google.com/maps/search/?api=1&query=' . urlencode($this->street1 . ' ' . $address),
          'target' => '_blank'
        ];
      }

      return $contactMethods;
    }

    /**
     * Get social links.
     *
     * @return array
     */
    public function getSocialLinks() {
      $socialLinks = [];

      if (isset($this->social['facebook'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-facebook',
          'href' => $this->social['facebook']
        ];
      }

      if (isset($this->social['youtube'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-youtube',
          'href' => $this->social['youtube']
        ];
      }

      if (isset($this->social['vimeo'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-vimeo',
          'href' => $this->social['vimeo']
        ];
      }

      if (isset($this->social['whatsapp'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-whatsapp',
          'href' => $this->social['whatsapp']
        ];
      }

      if (isset($this->social['instagram'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-instagram',
          'href' => $this->social['instagram']
        ];
      }

      if (isset($this->social['twitter'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-twitter',
          'href' => $this->social['twitter']
        ];
      }

      if (isset($this->social['linkedin'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-linkedin',
          'href' => $this->social['linkedin']
        ];
      }

      if (isset($this->social['tumblr'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-tumblr',
          'href' => $this->social['tumblr']
        ];
      }

      if (isset($this->social['snapchat'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-snapchat',
          'href' => $this->social['snapchat']
        ];
      }

      if (isset($this->social['pinterest'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-pinterest',
          'href' => $this->social['pinterest']
        ];
      }

      if (isset($this->social['telegram'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-telegram',
          'href' => $this->social['telegram']
        ];
      }

      if (isset($this->social['medium'])) {
        $socialLinks[] = [
          'icon' => 'fab fa-medium',
          'href' => $this->social['medium']
        ];
      }

      return $socialLinks;
    }

    /**
     * Get external links for top of campaign website.
     *
     * @return array
     */
    public function getExternalLinks() {
      $links = [];

      for ($i = 1; $i <= 3; $i++) {
        if (isset($this->content['text' . $i]) && isset($this->content['href' . $i])) {
          $links[] = [
            'text' => $this->content['text' . $i],
            'href' => $this->content['href' . $i]
          ];
        }
      }

      return $links;
    }

    /**
     * Images
     * -------------
     */

    public function getLogoAttribute() {
      return ($this->getFirstMediaUrl('logo') !== '') ? $this->getFirstMediaUrl('logo') : null;
    }

    /**
     * Relationships
     * -------------
     */

    public function account() {
      return $this->belongsTo(\App\User::class, 'account_id', 'id');
    }

    public function staff() {
      return $this->belongsToMany(\App\Staff::class, 'business_staff', 'business_id', 'staff_id');
    }

    public function industry() {
      return $this->hasOne(\Platform\Models\Industry::class, 'id', 'industry_id');
    }

    public function segments() {
      return $this->belongsToMany(\Platform\Models\Segment::class, 'business_segment', 'business_id', 'segment_id');
    }
}
