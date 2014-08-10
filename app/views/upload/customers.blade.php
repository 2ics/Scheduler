@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
Import Companies
@stop


{{-- Content --}}
@section('content')
<div class="well clearfix">
	<div class="row">
	  	<div class="col-md-12">
        	<div class="panel panel-primary">
                <div class="panel-heading">
                    <h3 class="panel-title">Upload Companies</h3>
                </div>
                <div class="panel-body">
                	{{ Form::open(array('url' => action('UploadController@uploadCustomers'), 'id' => 'demo-upload', 'files' => true, 'class' => 'dropzone')) }}
                	{{ Form::close() }}
               		<div id="workspace"></div>
               	</div>
            </div>
        </div>
	</div>
</div>
@stop
