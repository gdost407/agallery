<!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>AGallery</title>

    <!-- CSS FILES -->                
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,400;0,500;0,700;1,400&display=swap" rel="stylesheet">
    <link href="{{ asset('assets/websites/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/websites/css/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/websites/css/templatemo-tiya-golf-club.css') }}" rel="stylesheet">
  </head>
<body>

  @include('website.components.header')

  <main>
      @yield('content')
  </main>

  @include('website.components.footer')

</body>
</html>