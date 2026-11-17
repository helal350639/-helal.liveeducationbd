{{-- {{dd($_SERVER['REQUEST_URI'] == '/login')}} --}}
<!DOCTYPE html>
<html lang="en">
<head>
    
    @include('partials.styles')

    @yield('stylesheets')
		<style>
			.layout-top-nav{
				background: rgb(0,3,36);
				background: linear-gradient(90deg, rgba(0,3,36,1) 0%, rgba(9,73,121,1) 53%, rgba(7,128,153,1) 100%);
			}
			.content-header, .content-header > .breadcrumb > li > a{color:#fff}
		</style>

    <link rel="stylesheet" href="{{ asset('css/lebd-global-desktop-75-scale.css') }}?v=1">
</head>

<body class="layout-top-nav" style="width:100%; {{$_SERVER['REQUEST_URI'] == '/login'? 'background:rgba(0,0,0,0.3)':''}}">
	<div class="wrapper" style="height:100%; width:100%;padding-bottom: 50px">
    @include('auth.login_header')

		@yield('content')

    @include('auth.login_footer')
	</div>

	<!--   Core JS Files   -->
	@include('partials.scripts')

	@yield('scripts')

	<script type="text/javascript">
    $().ready(function() {
    	demo.checkFullPageBackgroundImage();
    	setTimeout(function() {
    		// after 1000 ms we add the class animated to the login/register card
    		$('.card').removeClass('card-hidden');
    	}, 700)
    });
  </script>

</body>
</html>