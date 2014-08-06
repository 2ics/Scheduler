@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row" id="calendar-row">
	@foreach ($processes as $process)
	  <div class="col-md-6">
		<div class="well text-center"><a href="{{action('HomeController@scheduleProcess', array('process_name' => $process->name))}}"><button type="button" class="btn btn-lg btn-default">{{$process->name}}</button></a></div>
	  </div>
  	@endforeach
</div>

@stop



@section('javascript')

@stop