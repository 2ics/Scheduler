<?php

if (isset($_SERVER['HTTP_HOST'])){
	if ($_SERVER['HTTP_HOST'] == "localhost" || $_SERVER['HTTP_HOST'] == "localhost:8888"){
		$_SERVER['HTTP_HOST'] = "".$_SERVER['HTTP_HOST']."/scheduler/public";
	}
}
if (isset($_SERVER['HTTP_HOST'])){
	$url = $_SERVER['HTTP_HOST'];
}else{
	$url = "http://localhost:8888/scheduler/public";
}
return array(

	/*
	|--------------------------------------------------------------------------
	| Local assets directories
	|--------------------------------------------------------------------------
	|
	| Override defaul prefix folder for local assets. They are relative to your
	| public folder. Don't use trailing slash!.
	|
	| Default for CSS: 'css'
	| Default for JS: 'js'
	*/
	'base_url' => $url,
	'css_dir' => '/assets',
	'public_dir' => $url,
	'js_dir' => '/assets',

	/*
	|--------------------------------------------------------------------------
	| Assets collections
	|--------------------------------------------------------------------------
	|
	| Collections allow you to have named groups of assets (CSS or JavaScript files).
	|
	| If an asset has been loaded already it won't be added again. Collections may be
	| nested but please be careful to avoid recursive loops.
	|
	| To avoid conflicts with the autodetection of asset types make sure your
	| collections names don't end with ".js" or ".css".
	|
	|
	| Example:
	|	'collections' => array(
	|
	|		// jQuery (CDN)
	|		'jquery-cdn' => [
	|			'//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js
	|		'],
	|
	|		// Twitter Bootstrap (CDN)
	|		'bootstrap-cdn' => [
	|			'jquery-cdn',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap.min.css',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css',
	|			'//netdna.bootstrapcdn.com/bootstrap/3.0.0/js/bootstrap.min.js'
	|		],
	|
	|		//Zurb Foundation (CDN)
	|		'foundation-cdn' => [
	|			'//cdn.jsdelivr.net/foundation/5.3.0/js/vendor/modernizr.js',
	|			'jquery2-cdn',
	|			'//cdn.jsdelivr.net/foundation/5.3.0/js/foundation.min.js',
	|			'app.js',
	|			'//cdn.jsdelivr.net/foundation/5.3.0/css/normalize.css',
	|			'//cdn.jsdelivr.net/foundation/5.3.0/css/foundation.min.css',
	|		],
	|	),
	*/

	'collections' => array(
		'planner'   => [
		                'stylesheets/main/bootstrap.min.css',
		                'stylesheets/main/justified-nav.css',
		                'stylesheets/main/breadcrumbs.css',
		                'stylesheets/main/main.css',
		                'stylesheets/main/basic.css',
		                'stylesheets/main/bootstrapValidator.min.css',
		                'stylesheets/main/chosen.min.css',
		                'stylesheets/main/ui-lightness/jquery-ui-1.10.4.min.css',
		                'stylesheets/main/bootstrap-editable.css',
		                'stylesheets/main/bootstrap-select.css',
		                'stylesheets/main/jquery.colour.css',
		                'javascripts/main/jquery-2.0.2.min.js',
		                'javascripts/main/jquery-ui-1.10.4.min.js',
		                'javascripts/main/bootstrap.min.js',
		                'javascripts/main/restfulizer.js',
		                'javascripts/main/dropzone.js',
		                'javascripts/main/jquery.actual.min.js',
		                'javascripts/main/bootstrapValidator.js',
		                'javascripts/main/chosen.jquery.min.js',
		                'javascripts/main/knockout-min.js',
		                'javascripts/main/jquery.cascadingdropdown.js',
                        'javascripts/main/jquery.blockUI.js',
                        'javascripts/main/jquery-ui-timepicker-addon.js',
		                'javascripts/main/bootstrap-editable.js',
		                'javascripts/main/bootstrap-select.js',
		                'javascripts/main/jquery.colour.js',
		                'packages/datatables/media/css/dataTables.bootstrap.css',
		                'packages/datatables/media/css/jquery.dataTables.min.css',
		                'packages/datatables/extensions/AutoFill/css/dataTables.autoFill.min.css',
		                'packages/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
		                'packages/datatables/extensions/ColVis/css/dataTables.colVis.min.css',
		                'packages/datatables/extensions/ColVis/css/dataTables.colvis.jqueryui.css',
		                'packages/datatables/extensions/FixedColumns/css/dataTables.fixedColumns.min.css',
		                'packages/datatables/extensions/FixedHeader/css/dataTables.fixedHeader.min.css',
		                'packages/datatables/extensions/KeyTable/css/dataTables.keyTable.min.css',
		                'packages/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
		                'packages/datatables/extensions/TableTools/css/dataTables.tableTools.min.css',
		                'packages/datatables/media/js/jquery.dataTables.min.js',
		                'packages/datatables/media/js/moment.js',
		                'packages/datatables/media/js/dataTables.bootstrap.js',
		                'packages/datatables/extensions/AutoFill/js/dataTables.autoFill.min.js',
		                'packages/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
		                'packages/datatables/extensions/ColVis/js/dataTables.colVis.min.js',
		                'packages/datatables/extensions/FixedColumns/js/dataTables.fixedColumns.min.js',
		                'packages/datatables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js',
		                'packages/datatables/extensions/KeyTable/js/dataTables.keyTable.min.js',
		                'packages/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
		                'packages/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
					],
		'scheduler'   => [
		                'stylesheets/main/bootstrap.min.css',
		                'stylesheets/main/justified-nav.css',
		                'stylesheets/main/breadcrumbs.css',
		                'stylesheets/main/basic.css',
		                'stylesheets/main/main.css',
		                'stylesheets/main/bootstrapValidator.min.css',
		                'stylesheets/main/chosen.min.css',
		                'stylesheets/main/ui-lightness/jquery-ui-1.10.4.min.css',
		                'stylesheets/main/bootstrap-editable.css',
		                'stylesheets/main/bootstrap-select.css',
		                'stylesheets/main/jquery.colour.css',
		                'packages/calendar/calendar.css',
		                'packages/calendar/skins/default.css',
		                'packages/calendar/skins/gcalendar.css',
		                'packages/font-awesome-4.1.0 2/css/font-awesome.min.css',
						'packages/calendar/libs/jquery-1.4.4.min.js',
		                'javascripts/main/jquery.actual.min.js',
		                'javascripts/main/bootstrap.min.js',
		                'packages/calendar/libs/jquery-ui-1.8.11.custom.min.js',
		                'packages/calendar/libs/jquery-ui-i18n.js',
		                'packages/calendar/libs/date.js',
		                'packages/calendar/jquery.calendar.js',
		                'javascripts/main/restfulizer.js',
		                'javascripts/main/dropzone.js',
		                'javascripts/main/bootstrapValidator.js',
		                'javascripts/main/chosen.jquery.min.js',
		                'javascripts/main/knockout-min.js',
		                'javascripts/main/jquery.cascadingdropdown.js',
                        'javascripts/main/jquery.blockUI.js',
                        'javascripts/main/jquery-ui-timepicker-addon.js',
		                'javascripts/main/bootstrap-editable.js',
		                'javascripts/main/bootstrap-select.js',
		                'javascripts/main/jquery.colour.js',
		                'packages/datatables/media/css/dataTables.bootstrap.css',
		                'packages/datatables/media/css/jquery.dataTables.min.css',
		                'packages/datatables/extensions/AutoFill/css/dataTables.autoFill.min.css',
		                'packages/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css',
		                'packages/datatables/extensions/ColVis/css/dataTables.colVis.min.css',
		                'packages/datatables/extensions/ColVis/css/dataTables.colvis.jqueryui.css',
		                'packages/datatables/extensions/FixedColumns/css/dataTables.fixedColumns.min.css',
		                'packages/datatables/extensions/FixedHeader/css/dataTables.fixedHeader.min.css',
		                'packages/datatables/extensions/KeyTable/css/dataTables.keyTable.min.css',
		                'packages/datatables/extensions/Scroller/css/dataTables.scroller.min.css',
		                'packages/datatables/extensions/TableTools/css/dataTables.tableTools.min.css',
		                'packages/datatables/media/js/jquery.dataTables.min.js',
		                'packages/datatables/media/js/moment.js',
		                'packages/datatables/media/js/dataTables.bootstrap.js',
		                'packages/datatables/extensions/AutoFill/js/dataTables.autoFill.min.js',
		                'packages/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js',
		                'packages/datatables/extensions/ColVis/js/dataTables.colVis.min.js',
		                'packages/datatables/extensions/FixedColumns/js/dataTables.fixedColumns.min.js',
		                'packages/datatables/extensions/FixedHeader/js/dataTables.fixedHeader.min.js',
		                'packages/datatables/extensions/KeyTable/js/dataTables.keyTable.min.js',
		                'packages/datatables/extensions/Scroller/js/dataTables.scroller.min.js',
		                'packages/datatables/extensions/TableTools/js/dataTables.tableTools.min.js',
					],
	),

	/*
	|--------------------------------------------------------------------------
	| Preload assets
	|--------------------------------------------------------------------------
	|
	| Here you may set which assets (CSS files, JavaScript files or collections)
	| should be loaded by default even if you don't explicitly add them.
	|
	*/

	'autoload' => array(),

	/*
	|--------------------------------------------------------------------------
	| Assets pipeline
	|--------------------------------------------------------------------------
	|
	| When enabled, all your assets will be concatenated and minified to a sigle
	| file, improving load speed and reducing the number of requests that the
	| browser makes to render a web page.
	|
	| It's a good practice to enable it only on production environment.
	|
	| Use an integer value greather than 1 to append a timestamp to the URL.
	|
	| Default: false
	*/

	'pipeline' => false,

	/*
	|--------------------------------------------------------------------------
	| Pipelined assets directories
	|--------------------------------------------------------------------------
	|
	| Override defaul folder for storing pipelined assets. Relative to your
	| assets folder. Don't use trailing slash!.
	|
	| Default: 'min'
	*/

	'pipeline_dir' => 'min',

);
