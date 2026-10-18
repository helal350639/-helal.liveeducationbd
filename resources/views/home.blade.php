<!DOCTYPE html>
<html lang="en">

<head>

    @include('partials.styles')


    {{-- Homepage Old Design CSS --}}
    <link rel="stylesheet" href="{{ asset('css/homepage-Old.css') }}">


    @yield('stylesheets')


    <style>

        .content-wrapper{
            background: rgb(0,3,36);
            background: linear-gradient(
                90deg,
                rgba(0,3,36,1) 0%,
                rgba(9,73,121,1) 53%,
                rgba(7,128,153,1) 100%
            );
        }


        .content-header,
        .content-header > .breadcrumb > li > a{
            color:#fff;
        }


    </style>


    <script src="https://www.wiris.net/demo/plugins/app/WIRISplugins.js?viewer=image"></script>

    <script src="/ckeditor/ckeditor.js"></script>


</head>



<body class="layout-top-nav">


<div class="wrapper no-padding"
     style="width:100%;padding-bottom:50px">


    {{-- Header --}}

    @include('homepage.header')



    {{-- Page Content --}}

    @yield('content')



    {{-- Footer --}}

    @include('homepage.footer')



</div>





{{-- Core JS --}}

@include('partials.scripts')



@yield('scripts')





<script type="text/javascript">

$().ready(function(){

    if(typeof demo !== 'undefined'){

        demo.checkFullPageBackgroundImage();

    }


    setTimeout(function(){

        $('.card').removeClass('card-hidden');

    },700);


});


</script>





<script>

CKEDITOR.on('instanceReady', function (ev) {


    if(typeof WirisPlugin !== 'undefined') {


        WirisPlugin.listeners.fire(
            'onTargetReady',
            {
                editor: ev.editor
            }
        );


    }


});


</script>



</body>

</html>