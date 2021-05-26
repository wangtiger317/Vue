<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, minimal-ui">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="theme-color" content="{{ $website['theme']['primaryColor'] }}">
  <link href="{{ url('css/campaign.css?' . config('versions.cache_version_campaign')) }}" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css?family=Roboto:100,300,400,500,700,900|Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css" integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
  <link rel="icon" type="image/png" href="{{ url('favicon-32x32.png') }}" sizes="32x32" />
  <link rel="icon" type="image/png" href="{{ url('favicon-16x16.png') }}" sizes="16x16" />
  <title>{{ $website['name'] }}<?php if ($website->content['campaignHeadline'] ?? null !== null) echo ' - ' . $website->content['campaignHeadline']; ?></title>

  <!-- web app manifest -->
  <link rel="manifest" href="/webmanifest/{{$website['slug']}}/manifest.json"/>
  <link rel="apple-touch-icon" href="/webmanifest/{{$website['slug']}}/icon-180.png">
  <!-- END web app manifest -->
  <!-- Offline web app warning -->
  <script src="/offline-warning/offline.js"></script>

  <link rel="stylesheet" href="/offline-warning/themes/offline-theme-slide.css" />
  <link rel="stylesheet" href="/offline-warning/themes/offline-language-portuguese.css" />
  <!-- Offline web app warning -->

  <script>
  window.initCampaign = {!! json_encode($website) !!};
  window.initConfig = {!! json_encode($config) !!};
  </script>
  <style type="text/css">
    #nprogress .bar {background: {{ $website['theme']['textColor'] }} !important;}
    #app-loader{display:flex;align-items:center;justify-content:center;position:fixed;width:100%;height:100%;background-color:#fff;z-index:999999}.lds-ring{display:inline-block;position:relative;width:64px;height:64px}.lds-ring div{box-sizing:border-box;display:block;position:absolute;width:51px;height:51px;margin:6px;border:4px solid {{ $website['theme']['textColor'] }};border-radius:50%;animation:lds-ring 1.2s cubic-bezier(.5,0,.5,1) infinite;border-color:{{ $website['theme']['textColor'] }} transparent transparent transparent}.lds-ring div:nth-child(1){animation-delay:-.45s}.lds-ring div:nth-child(2){animation-delay:-.3s}.lds-ring div:nth-child(3){animation-delay:-.15s}@keyframes lds-ring{0%{transform:rotate(0)}100%{transform:rotate(360deg)}}
  </style>
  <!-- Google Tag Manager -->
  <script src="https://unpkg.com/vue-tag-manager@x.x.x/lib/TagManager.js"></script>
  <script>
      VueTagManager.initialize({
          gtmId: 'GTM-5GLBW6W'
      })
  </script>
  <!-- End Google Tag Manager -->
</head>
<body>
  <div id="app-loader">
    <div class="lds-ring"><div></div><div></div><div></div><div></div></div>
  </div>
  <div id="app">
    <app></app>
  </div>
  <script>
      if ('serviceWorker' in navigator) {
          window.addEventListener('load', () => {
              navigator.serviceWorker.register('/sw.js');
          });
      }
  </script>
  <script src="{{ url('js/manifest.js?' . config('versions.cache_version_vendor')) }}"></script>
  <script src="{{ url('js/vendor.js?' . config('versions.cache_version_vendor')) }}"></script>
  <script src="{{ url('js/campaign.js?' . config('versions.cache_version_campaign')) }}"></script>
  <script>
      window.TagManager.push({'gtm.start': new Date().getTime(), event: "gtm.js"})
  </script>
</body>
</html>
