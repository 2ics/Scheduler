@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
Edit Profile
@stop

{{-- Content --}}
@section('content')

<h4>{{trans('pages.actionedit')}} 
@if ($user->email == Sentry::getUser()->email)
	{{trans('users.yours')}}
@else 
	{{ $user->email }} 
@endif 
{{trans('pages.profile')}}</h4>
<div class="well">
	{{ Form::open(array(
        'action' => array('UserController@update', $user->id), 
        'method' => 'put',
        'class' => 'form-horizontal', 
        'role' => 'form'
        )) }}
        
        <div class="form-group {{ ($errors->has('firstName')) ? 'has-error' : '' }}" for="firstName">
            {{ Form::label('edit_firstName', trans('users.fname'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('firstName', $user->first_name, array('class' => 'form-control', 'placeholder' => trans('users.fname'), 'id' => 'edit_firstName'))}}
            </div>
            {{ ($errors->has('firstName') ? $errors->first('firstName') : '') }}    			
    	</div>


        <div class="form-group {{ ($errors->has('lastName')) ? 'has-error' : '' }}" for="lastName">
            {{ Form::label('edit_lastName', trans('users.lname'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('lastName', $user->last_name, array('class' => 'form-control', 'placeholder' => trans('users.lname'), 'id' => 'edit_lastName'))}}
            </div>
            {{ ($errors->has('lastName') ? $errors->first('lastName') : '') }}                
        </div>

        <div class="form-group {{ ($errors->has('title')) ? 'has-error' : '' }}" for="title">
            {{ Form::label('edit_title', trans('users.title'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('title', $user->title, array('class' => 'form-control', 'placeholder' => trans('users.title'), 'id' => 'edit_title'))}}
            </div>
            {{ ($errors->has('title') ? $errors->first('title') : '') }}                
        </div>

        <div class="form-group {{ ($errors->has('phone')) ? 'has-error' : '' }}" for="phone">
            {{ Form::label('edit_phone', trans('users.phone'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('phone', $user->phone, array('class' => 'form-control', 'placeholder' => trans('users.phone'), 'id' => 'edit_phone'))}}
            </div>
            {{ ($errors->has('phone') ? $errors->first('phone') : '') }}                
        </div>

        <div class="form-group {{ ($errors->has('ext')) ? 'has-error' : '' }}" for="ext">
            {{ Form::label('edit_ext', trans('users.ext'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('ext', $user->ext, array('class' => 'form-control', 'placeholder' => trans('users.ext'), 'id' => 'edit_ext'))}}
            </div>
            {{ ($errors->has('ext') ? $errors->first('ext') : '') }}                
        </div>

        <div class="form-group {{ ($errors->has('fax')) ? 'has-error' : '' }}" for="fax">
            {{ Form::label('edit_fax', trans('users.fax'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
              {{ Form::text('fax', $user->fax, array('class' => 'form-control', 'placeholder' => trans('users.fax'), 'id' => 'edit_fax'))}}
            </div>
            {{ ($errors->has('fax') ? $errors->first('fax') : '') }}                
        </div>


        <div class="form-group" for="colour">
            {{ Form::label('colour', trans('users.colour'), array('class' => 'col-sm-2 control-label')) }}
            <div class="col-sm-10">
                <select name="colour" id="colour-picker">
                    <option value="ffffff">#ffffff</option>
                    <option value="ffccc9">#ffccc9</option>
                    <option value="ffce93">#ffce93</option>
                    <option value="fffc9e">#fffc9e</option>
                    <option value="ffffc7">#ffffc7</option>
                    <option value="9aff99">#9aff99</option>
                    <option value="96fffb">#96fffb</option>
                    <option value="cdffff">#cdffff</option>
                    <option value="cbcefb">#cbcefb</option>
                    <option value="cfcfcf">#cfcfcf</option>
                    <option value="fd6864">#fd6864</option>
                    <option value="fe996b">#fe996b</option>
                    <option value="fffe65">#fffe65</option>
                    <option value="fcff2f">#fcff2f</option>
                    <option value="67fd9a">#67fd9a</option>
                    <option value="38fff8">#38fff8</option>
                    <option value="68fdff">#68fdff</option>
                    <option value="9698ed">#9698ed</option>
                    <option value="c0c0c0">#c0c0c0</option>
                    <option value="fe0000">#fe0000</option>
                    <option value="f8a102">#f8a102</option>
                    <option value="ffcc67">#ffcc67</option>
                    <option value="f8ff00">#f8ff00</option>
                    <option value="34ff34">#34ff34</option>
                    <option value="68cbd0">#68cbd0</option>
                    <option value="34cdf9">#34cdf9</option>
                    <option value="6665cd">#6665cd</option>
                    <option value="9b9b9b">#9b9b9b</option>
                    <option value="cb0000">#cb0000</option>
                    <option value="f56b00">#f56b00</option>
                    <option value="ffcb2f">#ffcb2f</option>
                    <option value="ffc702">#ffc702</option>
                    <option value="32cb00">#32cb00</option>
                    <option value="00d2cb">#00d2cb</option>
                    <option value="3166ff">#3166ff</option>
                    <option value="6434fc">#6434fc</option>
                    <option value="656565">#656565</option>
                    <option value="9a0000">#9a0000</option>
                    <option value="ce6301">#ce6301</option>
                    <option value="cd9934">#cd9934</option>
                    <option value="999903">#999903</option>
                    <option value="009901">#009901</option>
                    <option value="329a9d">#329a9d</option>
                    <option value="3531ff">#3531ff</option>
                    <option value="6200c9">#6200c9</option>
                    <option value="343434">#343434</option>
                    <option value="680100">#680100</option>
                    <option value="963400">#963400</option>
                    <option value="986536">#986536</option>
                    <option value="646809">#646809</option>
                    <option value="036400">#036400</option>
                    <option value="34696d">#34696d</option>
                    <option value="00009b">#00009b</option>
                    <option value="303498">#303498</option>
                    <option value="000000">#000000</option>
                    <option value="330001">#330001</option>
                    <option value="643403">#643403</option>
                    <option value="663234">#663234</option>
                    <option value="343300">#343300</option>
                    <option value="013300">#013300</option>
                    <option value="003532">#003532</option>
                    <option value="010066">#010066</option>
                    <option value="340096">#340096</option>
                </select>       
            </div>       
        </div>



        @if (Sentry::check() && (Sentry::getUser()->hasAccess('Super Admin') || Sentry::getUser()->hasAccess('Admin')))
        <div class="form-group">
            {{ Form::label('edit_memberships', trans('users.group_membership'), array('class' => 'col-sm-2 control-label'))}}
            <div class="col-sm-10">
                @foreach ($allGroups as $group)
                    @if ($group->name == "Super Admin" && !Sentry::getUser()->hasAccess('Super Admin'))

                    @else
                    <label class="radio-inline">
                      <input type="radio" name="group" {{(Sentry::getUser()->groups()->first()->id == $group->id) ? "checked" : ""}} value="{{$group->id}}">{{$group->name}}
                    </label>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

        <div class="form-group">
            <div class="col-sm-offset-2 col-sm-10">
                {{ Form::hidden('id', $user->id) }}
                {{ Form::submit(trans('pages.actionedit'), array('class' => 'btn btn-primary'))}}
            </div>
      </div>
    {{ Form::close()}}
</div>

<h4>{{trans('users.change_passwort')}}</h4>
<div class="well">
    {{ Form::open(array(
        'action' => array('UserController@change', $user->id), 
        'class' => 'form-inline', 
        'role' => 'form'
        )) }}
        
        <div class="form-group {{ $errors->has('oldPassword') ? 'has-error' : '' }}">
        	{{ Form::label('oldPassword', trans('users.oldpassword_lbl'), array('class' => 'sr-only')) }}
			{{ Form::password('oldPassword', array('class' => 'form-control', 'placeholder' => trans('users.oldpassword_lbl'))) }}
    	</div>

        <div class="form-group {{ $errors->has('newPassword') ? 'has-error' : '' }}">
        	{{ Form::label('newPassword', trans('users.newpassword_lbl'), array('class' => 'sr-only')) }}
            {{ Form::password('newPassword', array('class' => 'form-control', 'placeholder' => trans('users.newpassword_lbl'))) }}
    	</div>

    	<div class="form-group {{ $errors->has('newPassword_confirmation') ? 'has-error' : '' }}">
        	{{ Form::label('newPassword_confirmation', trans('users.newcompassword_lbl'), array('class' => 'sr-only')) }}
            {{ Form::password('newPassword_confirmation', array('class' => 'form-control', 'placeholder' => trans('users.newcompassword_lbl'))) }}
    	</div>

        {{ Form::submit(trans('users.change_passwort'), array('class' => 'btn btn-primary', 'style' => 'margin-bottom: 15px'))}}
	        	
      {{ ($errors->has('oldPassword') ? '<br />' . $errors->first('oldPassword') : '') }}
      {{ ($errors->has('newPassword') ?  '<br />' . $errors->first('newPassword') : '') }}
      {{ ($errors->has('newPassword_confirmation') ? '<br />' . $errors->first('newPassword_confirmation') : '') }}

      {{ Form::close() }}
  </div>

@stop

{{-- Javascript --}}
@section('javascript')
  <script type="text/javascript">
  (function($) {
    users = Array();
    $(document).ready(function() {
        jQuery('#colour-picker').colourPicker({
            ico:   '{{asset('/img/colourwheel.gif')}}', 
            title:    false
        });
    });
  })(jQuery);
  </script>
@stop
