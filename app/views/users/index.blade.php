@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.listwith')}} {{trans('pages.users')}}
@stop

{{-- Content --}}
@section('content')
<h4>{{trans('pages.currentusers')}}:</h4>
<div class="row">
  <div class="col-md-10 col-md-offset-1">
	<div class="table-responsive">
		<table class="table table-striped table-hover">
			<thead>
				<th>{{trans('pages.user')}}</th>
				<th>{{trans('pages.status')}}</th>
				<th>{{trans('pages.options')}}</th>
			</thead>
			<tbody>
				@foreach ($users as $user)
					<tr>
						<td><a href="{{ action('UserController@show', array($user->id)) }}">{{ $user->email }}</a></td>
						<td>@if ($user->status=='Active')
							{{trans('pages.active')}}
						 @else
						 	{{trans('pages.notactive')}}
						 @endif
						 </td>
							
						<td>
							<button class="btn btn-default" type="button" onClick="location.href='{{ action('UserController@edit', array($user->id)) }}'">{{trans('pages.actionedit')}}</button> 
							
							<button class="btn btn-default action_confirm" href="{{ action('UserController@destroy', array($user->id)) }}" data-token="{{ Session::getToken() }}" data-method="delete">{{trans('pages.actiondelete')}}</button></td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</div>
  </div>
</div>
@stop
