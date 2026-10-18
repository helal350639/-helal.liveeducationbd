<!DOCTYPE html>
<html lang="bn">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        @yield('title') | Live Education BD
    </title>


    <link rel="stylesheet" href="{{ asset('css/homepage-Old.css') }}">


    @yield('stylesheets')

</head>


<body>


    {{-- Header --}}
    @include('homepage.header')


    {{-- Content --}}
    @yield('content')


    {{-- Footer --}}
    @include('homepage.footer')


    @yield('scripts')


</body>

</html>