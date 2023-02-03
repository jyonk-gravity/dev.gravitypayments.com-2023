<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="apple-touch-icon" sizes="180x180" href="@asset('images/favicons/apple-touch-icon.png')">
  <link rel="icon" type="image/png" sizes="32x32" href="@asset('images/favicons/favicon-32x32.png')">
  <link rel="icon" type="image/png" sizes="16x16" href="@asset('images/favicons/favicon-16x16.png')">
  <link rel="manifest" href="@asset('images/favicons/site.webmanifest')">
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/cookieconsent@3/build/cookieconsent.min.css" />

  @if ( is_page('reference') )
  <style>
    redoc[spec-url] * {
      font-family: proxima-nova, sans-serif;
    }
  </style>
  @endif

  <!-- Google Tag Manager -->
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-5PMH993');</script>
	<!-- End Google Tag Manager -->
  
  @php wp_head() @endphp
</head>