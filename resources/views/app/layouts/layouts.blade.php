<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>AGallery App</title>
  <link rel="shortcut icon" type="image/png" href="{{ asset('assets/AGallery-Logo-Golden.png') }}" />
  <link rel="stylesheet" href="{{ asset('assets/app/css/styles.min.css') }}" />
  <link href="{{ asset('assets/websites/css/bootstrap-icons.css') }}" rel="stylesheet">
  <style>
    /* only this tiny block – everything else pure Bootstrap */
    .scroll-on-hover {
      overflow-y: auto;
      scrollbar-width: thin;
    }
    .scroll-on-hover::-webkit-scrollbar {
      width: 6px;
      background: transparent;
    }
    .scroll-on-hover::-webkit-scrollbar-thumb {
      background: transparent;
      border-radius: 10px;
    }
    .scroll-on-hover:hover::-webkit-scrollbar-thumb {
      background: #c1c7cd;
    }
    .scroll-on-hover:hover {
      scrollbar-color: #c1c7cd transparent;
    }
    /* optional: smooth transition */
    .scroll-on-hover {
      transition: scrollbar-color 0.2s;
    }
  </style>
</head>
<body style="background-color: #3d055d;">

  
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">
    <!-- Sidebar Start -->
     @include('app.components.sidebar')
    <!--  Sidebar End -->
    <!--  Main wrapper -->
    <div class="body-wrapper">
      <!--  Header Start -->
      @include('app.components.header')
      <!--  Header End -->
      <div class="container-fluid">
        <div class="card main-card">
          <div class="card-body scroll-on-hover position-relative" style="overflow-y: auto;">
            @yield('content')
          </div>
        </div>
      </div>
  </div>

  @include('app.components.footer')

</body>
</html>