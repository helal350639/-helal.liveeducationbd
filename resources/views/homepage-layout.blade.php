<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>
        @yield('title') | Live Education BD
    </title>


    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('css/font-awesome.min.css') }}">


    <!-- Homepage Old CSS -->
    <link rel="stylesheet" href="{{ asset('css/homepage-Old.css') }}">
    
    <link rel="stylesheet" href="/web/assets/css/style2b24.css?v=3.8.40">

    




    @yield('stylesheets')


</head>



<body>


    {{-- Header --}}

    @include('homepage.header')



    {{-- Main Content --}}

    @yield('content')



    {{-- Footer --}}

    @include('homepage.footer')





    <!-- Bootstrap 5 JS -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>



    @yield('scripts')


</body>

</html>