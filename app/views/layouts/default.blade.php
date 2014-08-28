<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8" />
		<title> 
			Scheduler - 
			@section('title') 
			@show 
		</title>

		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<?php echo Assets::css(); ?>

		<style>
		@section('styles')
			body {
				padding-top: 60px;
				padding-bottom: 60px;
			}
		@show
		</style>

		<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
		<!--[if lt IE 9]>
		<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
		<![endif]-->

	
	</head>

	<body>
		<!-- Navbar -->
		<div class="navbar navbar-inverse navbar-fixed-top">
	      <div class="container">
	        <div class="navbar-header">
	          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	            <span class="icon-bar"></span>
	          </button>
	        </div>
	        <div class="collapse navbar-collapse">
	          <ul class="nav navbar-nav">
				<li ><img src="{{asset('/img/zen_logo.png')}}" class="img-responsive" /></li>
				@if (Sentry::check() && Sentry::getUser()->hasAccess('admin'))@endif
				<li {{ (Request::is('project/create') ? 'class="active"' : '') }}><a href="{{ action('ProjectController@create') }}">{{trans('pages.create')}}</a></li>

				<li {{ (Request::is('project/editor') ? 'class="active"' : '') }}><a href="{{ action('ProjectController@editor') }}">{{trans('pages.edit')}}</a></li>
				
				<li {{ (Request::is('scheduler') ? 'class="active"' : '') }}><a href="{{ action('ProjectController@scheduler') }}">{{trans('pages.scheduler')}}
				<span style="margin-left:5px;{{Task::getAllUnscheduled() == 0 ? 'display:none;' : ''}}"  class="badge pull-right master-tasks">{{Task::getAllUnscheduled()}}</span>
				</a></li>
	          </ul>
	          <ul class="nav navbar-nav navbar-right">
				
	            @if (Sentry::check())
				<li {{ (Request::is('users/show/' . Session::get('userId')) ? 'class="active"' : '') }}><a href="{{ URL::to('users') }}/{{ Session::get('userId') }}">{{ Session::get('email') }}</a></li>
				<li><a href="{{ URL::to('logout') }}">{{trans('pages.logout')}}</a></li>
				@else
				<li {{ (Request::is('login') ? 'class="active"' : '') }}><a href="{{ URL::to('login') }}">{{trans('pages.login')}}</a></li>
				
				@endif
	          </ul>
	        </div><!--/.nav-collapse -->
	      </div>
	    </div>
		<!-- ./ navbar -->

		<!-- Container -->
		<div class="container">
			<div class="row" style="margin-bottom: 15px;">
				<div class="col-md-12 text-right"><h2 style="margin:0px; padding: 0px;">{{date('l, F d, Y', time())}}</h2></div>
			</div>
			<!-- Notifications -->
			@include('layouts/notifications')
			<!-- ./ notifications -->

			<!-- Content -->
			@yield('content')
			<!-- ./ content -->
		</div>

		<!-- ./ container -->

		<!-- Javascripts
		================================================== -->
		<?php echo Assets::js();?>


		<script type="text/javascript">
			var root = '{{url("/")}}';
		</script>
		<!-- Content -->
		@yield('javascript')
		<!-- ./ content -->
	</body>
</html>
