@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row">
  <div class="col-md-3">
	<div class="panel-group">
        <div class="panel panel-default">
          <div class="panel-heading">
            <h4 class="panel-title">
              <a>TASKS</a>
            </h4>
          </div>
          <div class="panel-collapse">
            <ul class="list-group">
              <li class="list-group-item"><a data-toggle="collapse" href="#collapseEligible">Eligible Tasks</a><span class="badge">4</span>
                <ul class="list-group collapse" id="collapseEligible">
                  <li class="list-group-item">Business Cards<span class="glyphicon glyphicon-search pull-right"></span></li>

                  <li class="list-group-item">Postcards</li>

                  <li class="list-group-item">Newsletters</li>

                  <li class="list-group-item">Annual Reports</li>
                </ul>
              </li>
              <li class="list-group-item"><a data-toggle="collapse" href="#collapseUnplaced" class="">Unplaced Tasks</a><span class="badge">4</span>
                <ul class="list-group collapse" id="collapseUnplaced">
                  <li class="list-group-item">Business Cards</li>

                  <li class="list-group-item">Postcards</li>

                  <li class="list-group-item">Newsletters</li>

                  <li class="list-group-item">Annual Reports</li>
                </ul>
              </li>
            </ul>
          </div>
        </div>
      </div>
  </div>
  <div class="col-md-9">
  </div>
</div>

@stop

{{-- Javascript --}}
@section('javascript')
<script>
</script>
@stop
