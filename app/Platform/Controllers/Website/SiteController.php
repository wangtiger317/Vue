<?php namespace Platform\Controllers\Website;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Platform\Controllers\Core;

class SiteController extends Controller {

  /*
   |--------------------------------------------------------------------------
   | Web routes for website
   |--------------------------------------------------------------------------
   |
   | Website logic
   |--------------------------------------------------------------------------
   */

  /**
   * Homepage
   *
   * @return \Illuminate\View\View
   */
  public function home() {
    if (!env('HOMEPAGE', true)) {
      return redirect('go');
    }
    $account = app()->make('account');
    $account = (array) $account->only('app_name', 'app_contact', 'app_headline', 'app_color', 'app_scheme', 'app_host', 'language', 'locale');
    $account['app_contact'] = Core\Secure::hideEmail($account['app_contact']);

    $plans = \Platform\Models\Plan::getPlansForSite();

    $website = [
      'theme' => [
        'color' => '#0D47A1',
        'primary' => 'blue darken-4 white--text',
        'outlined' => 'blue blue--text darken-4--text',
        'backgroundColor' => '#ffffff',
        'primaryColor' => '#ffffff',
        'primaryTextColor' => '#333',
        'textColor' => '#333333',
        'secondaryColor' => '#000',
        'secondaryTextColor' => '#ffffff',
        'drawer' => [
          'textColor' => '#eeeeee',
          'backgroundColor' => '#333333',
          'highlightTextColor' => '#ffffff',
          'highlightBackgroundColor' => '#222222'
        ]
      ],
      'home' => [
        'title' => 'Plataforma de Fidelização Digital'
      ],
      'features' => [
        "Crie clientes satisfeitos" => "Recompense os seus clientes fiéis.",
        "Sempre disponível" => "Pode aceder em qualquer lado através do browser.",
        "Comece já a usar" => "Não necessita de conhecimentos técnicos.",
        "Promova a sua campanha" => "Os clientes podem aderir e aceder diretamente.",
        "Otimizado para mobile" => "Funciona perfeitamente também em tablets e computadores.",
        "Clientes fiéis" => "Fidelize clientes e conheça o seu histórico.",
        "Segmente os seus clientes" => "Use segmentos para organizar os seus clientes.",
        "Investimento reduzido" => "Uma plataforma testada e pronta a usar.",
        "Transparência" => "Os clientes podem consultar o saldo de pontos em tempo real.",
        "Evite fraudes" => "Tenha o histórico de todos os pontos atribuídos e trocados.",
        "Modernize o seu negócio" => "Tudo digital, esqueça os cartões e carimbos.",
        "Aumente as vendas" => "Ofereça pontos adicionais a quem pagar pacotes de serviços.",

      ],
      'plans' => $plans,
      'pricingFaq' => [
        "Como funciona a subscrição?" => "O serviço é completamente gratuito durante " . config('system.trial_days') . " dias. Pode testar todas as funcionalidades sem qualquer custo e iniciar a sua primeira campanha. Após este período poderá subscrever o plano que desejar para continuar ou desistir.",
        "Se tiver dúvidas, alguém pode ajudar-me?" => "Sim, o nosso apoio ao cliente irá ajudá-lo em todas as suas questões. Só terá de enviar um e-mail para o e-mail indicado no rodapé.",
      ]
    ];

    return view('website.home', compact('account', 'website'));
  }

  /**
   * Get terms
   *
   * @return \Illuminate\View\View
   */
  public function getTerms() {
    $account = app()->make('account');
    $company = request()->get('company', $account->app_name);
    return view('website.terms', compact('account', 'company'));
  }
}
