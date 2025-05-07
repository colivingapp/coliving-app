<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ $meta['title'] }}</title>
        <meta name="description" content="{{ $meta['description'] }}"/>

        <meta property="og:title" content="{{ $meta['soc_title'] }}">
        <meta property="og:description" content="{{ $meta['soc_description'] }}">
        <meta property="og:site_name" content="Coliving App">
        <?php if (isset($meta['image_w'])) { ?>
            <meta property="og:image" content="{{env('APP_URL')}}/img/{{ $meta['image'] }}">
            <meta property="og:image:width" content="{{ $meta['image_w'] }}" />
            <meta property="og:image:height" content="{{ $meta['image_h'] }}" />
            <meta name="twitter:image" content="{{env('APP_URL')}}/img/{{ $meta['image'] }}">
        <?php } else { ?>
            <meta property="og:image" content="{{env('APP_URL')}}/storage/spaces/{{ $meta['image'] }}">
            <meta name="twitter:image" content="{{env('APP_URL')}}/storage/spaces/{{ $meta['image'] }}">
        <?php } ?>
        <meta property="og:url" content="{{Request::url()}}">

        <meta name="twitter:title" content="{{ $meta['soc_title'] }}">
        <meta name="twitter:description" content="{{ $meta['soc_description'] }}">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@colivingapp">
        <meta name="twitter:creator" content="@colivingapp">
        <meta name="twitter:image:alt" content="{{ $meta['soc_title'] }}">
        <meta name="twitter:domain" content="{{env('APP_URL')}}">

        <link rel="icon" href="/favicon.svg?v=017" type="image/svg+xml">
        <link rel="icon" href="/favicon.ico?v=017" type="image/x-icon">
        <link rel="apple-touch-icon" sizes="180x180" href="/img/favicon/apple-touch-icon.png?v=017">
        <link rel="icon" type="image/png" sizes="32x32" href="/img/favicon/favicon-32x32.png?v=017">
        <link rel="icon" type="image/png" sizes="16x16" href="/img/favicon/favicon-16x16.png?v=017">
        <link rel="mask-icon" href="/img/favicon/safari-pinned-tab.svg?v=017" color="#4d4d4d">
        <meta name="apple-mobile-web-app-title" content="Coliving App">
        <meta name="application-name" content="Coliving App">
        <meta name="msapplication-TileColor" content="#4d4d4d">
        <meta name="msapplication-config" content="/img/favicon/browserconfig.xml?v=017">
        <meta name="theme-color" content="#4d4d4d">

		<link rel="manifest" href="/manifest.json?v=018">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;700&display=swap" rel="stylesheet">
        <!-- <link href="https://fonts.googleapis.com/css2?family=Raleway:wght@400;700&family=Roboto:wght@400;70&family=PT+Sans:wght@400;700&family=Muli:wght@400;700&family=Cabin:wght@400;700&family=Open+Sans:wght@400;700&family=Source+Sans+Pro:wght@400;700&family=Noto+Sans:wght@400;700&family=Lato:wght@400;700&family=Work+Sans:wght@400;700&Montserrat:wght@400;700&display=swap" rel="stylesheet"> -->

        @vite('resources/css/app.css')

        <script>
            userVerified = null;
            mySpaces = null;
        </script>

        <?php if (isset($space)) { ?>
            <script>
                space = @json($space);
            </script>
        <?php } if (isset($mySpaces)) { ?>
            <script>
                mySpaces = @json($mySpaces);
            </script>
        <?php } if (isset($myColivingSpaces)) { ?>
            <script>
                myColivingSpaces = @json($myColivingSpaces);
            </script>
        <?php } if (isset($pastColivingSpaces)) { ?>
            <script>
                pastColivingSpaces = @json($pastColivingSpaces);
            </script>
        <?php } ?>

        <script>mate = null</script>

        <?php if (isset($mate)) { ?>
            <script>
                mate = @json($mate);
            </script>
        <?php } ?>
        
        <?php if (Auth::check()) { 
            $user = Auth::user();
            
            // Clone user and unset sensitive fields
            $safeUser = clone $user;
            $safeUser->makeHidden([
                'two_factor_confirmed_at',
                'two_factor_recovery_codes',
                'two_factor_secret'
            ]); ?>
            <script>
                userVerified = "<?php echo($user->hasVerifiedEmail()); ?>";
                userEmail = "<?php echo($user->email); ?>";
                userLoggedIn = "<?php echo(Auth::check()); ?>";
                caUser = {}
                caUser.user = <?php echo json_encode($safeUser); ?>;
                caUser.userLoggedIn = "<?php echo(Auth::check()); ?>";
                caUser.userEmail = "<?php echo($user->email); ?>";
                caUser.userId = "<?php echo($user->id); ?>";
            </script>
            
            <script src="https://maps.googleapis.com/maps/api/js?key={{ config('ca.google_api_key') }}&libraries=places"></script>
        <?php } else { ?>
            <script>
                userLoggedIn = false;
            </script>
        <?php } ?>        
        
        <!-- To access Vue methods -->
        <script>
            vueVM = null;
            vueVM3 = null;
        </script>

        @if (config('app.env') == 'live' && config('ca.analytics_enabled'))
        <!-- Google tag (gtag.js) -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('ca.ga_site_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());

            gtag('config', '{{ config('ca.ga_site_id') }}');
        </script>

        <!-- Matomo -->
        <script>
            var _paq = window._paq = window._paq || [];
            _paq.push(['trackPageView']);
            _paq.push(['enableLinkTracking']);
            (function() {
            var u="{{ config('ca.matomo_url') }}";
            _paq.push(['setTrackerUrl', u+'matomo.php']);
            _paq.push(['setSiteId', '{{ config('ca.matomo_site_id') }}']);
            var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
            g.async=true; g.src=u+'matomo.js'; s.parentNode.insertBefore(g,s);
            })();
        </script>
        @endif

        <script src="/multiavatar.js?v=003"></script>

    </head>
    <body>
        <div id="app" style="height:100%;"></div>
        
        @vite('resources/js/app.js')

        <!-- SVG icons -->
        <svg style="display: none">
            <defs>
                <symbol id="checkmark" viewBox="0 0 512 444.03">
                    <polygon points="202.62 444.03 0 257.38 70.51 180.82 191.97 292.67 431.44 0 512 65.92 202.62 444.03"></polygon> 
                </symbol>
            </defs>
        </svg>
    </body>
</html>
