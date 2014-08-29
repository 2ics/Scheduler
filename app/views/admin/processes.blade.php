@extends('layouts.default')

{{-- Web site Title --}}
@section('title')
@parent
{{trans('pages.helloworld')}}
@stop

{{-- Content --}}
@section('content')
<div class="row">
	<div class="col-md-12">
		<h1>Edit Processes</h1>
	</div>
	<div class="col-md-12">
      <div class="well">
      	<div class="row">
      		<div class="col-md-4">
      			<ul class="list-group processes">
      				@foreach(Process::orderBy('order', 'asc')->get() as $process)
					<li class="list-group-item hasItems process sorted-processes" style="padding: 5px 15px;" data-id="{{$process->id}}"><span style="font-size: 14px;"><b>{{$process->name}}</b></span>
						<div class="btn-group pull-right" style="padding-right: 6px;">
						  <button type="button" class="btn btn-default btn-sm edit"><span class="glyphicon glyphicon-pencil"></span></button>
						  <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash"></span></button>
						</div>
						<div class="clearfix"></div>
						<ul class="list-group equipments" style="margin-bottom: 0px;">
							@foreach ($process->equipment()->orderBy('order', 'asc')->get() as $equipment)
						    <li class="list-group-item equipment" data-id="{{$equipment->id}}" style="padding: 5px 5px 5px 15px;">
						    	<span style="font-size: 14px;">{{$equipment->name}}</span>
								<div class="btn-group pull-right">
								  <button type="button" class="btn btn-default btn-sm edit"><span class="glyphicon glyphicon-pencil"></span></button>
								  <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash"></span></button>
								</div>
								<div class="clearfix"></div>
						    </li>
						    @endforeach
						</ul>
					</li>
					@endforeach
                </ul>
			</div>
      		<div class="col-md-4">
      			<h4>Add Process</h4>
      			<div class="input-group">
      				<input type="text" class="form-control" id="create_process" placeholder="Process Name" />
					<span class="input-group-btn">
					  <button type="button" class="btn btn-success btn-add-process">
					      <span class="glyphicon glyphicon-plus"></span>
					  </button>
					</span>
          		</div>
      			<ul class="list-group processes new-processes">
                </ul>
			</div>
      		<div class="col-md-4">
      			<h4>Add Equipment</h4>
      			<div class="input-group">
      				<input type="text" class="form-control" id="create_equipment" placeholder="Equipment Name" />
					<span class="input-group-btn">
					  <button type="button" class="btn btn-success btn-add-equipment">
					      <span class="glyphicon glyphicon-plus"></span>
					  </button>
					</span>
				</div>
				<ul class="list-group equipments new-equipment" style="margin-bottom: 0px;">
				</ul>
			</div>
		</div>
		<div class="col-md-12 text-right">
			<button type="button" class="btn btn-primary btn-lg submit">
			  Save
			</button>
		</div>
		<div class="clearfix"></div>
      </div>
	</div>
</div>
<!-- Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Delete Process/Equipment</h4>
      </div>
      <div class="modal-body">
      	Are you sure you want to delete this? Continuing will remove all tasks associated with this process/equipment.
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger btn-delete">Delete</button>
      </div>
    </div>
  </div>
</div>
@stop

{{-- Javascript --}}
@section('javascript')
<script>
addSortable();
addEventHandles();
function addSortable(){
	$( ".processes" ).sortable({
	    connectWith: '.processes',
	    beforeStop: function(ev, ui) {
	        if ($(ui.item).hasClass('hasItems') && $(ui.placeholder).parent()[0] != this) {
	        }
	    }
	});
	$('.equipments').sortable({
	    connectWith: '.equipments'
	});
}

function addEventHandles(){
	$(".edit").click(function(){
		$($(this).parent().parent().find('span')[0]).html('<div class="input-group"> <input type="text" class="form-control" placeholder="Process Name" value="'+$($(this).parent().parent().find('span')[0]).text()+'" /> <span class="input-group-btn"> <button type="button" class="btn btn-success btn-change-name"> <span class="glyphicon glyphicon-ok"></span> </button> </span> </div>');
		$(this).parent().parent().find('.btn-group').hide();
		$(".btn-change-name").click(function(){
			var div = $(this).parent().parent().parent().parent();
			div.find('.input-group').hide();
			div.find('.btn-group').show();
			div.prepend('<span style="font-size: 14px;"><b>'+$(this).parent().parent().find('input').val()+'</b></span>');
		});
	});
}
$('#deleteModal').on('shown.bs.modal', function (e) {
	var self = $(this);
	$(this).find('.btn-delete').click(function(){
		$(e.relatedTarget).parent().parent().remove();
		self.modal('hide');
	});
});
$('#deleteModal').on('hidden.bs.modal', function (e) {
	$(this).find('.btn-delete').unbind('click');
});

$(".btn-add-process").click(function(){
	if ($("#create_process").val() != ""){
		$(".new-processes").append('<li class="list-group-item hasItems process" style="padding: 5px 15px;"><span style="font-size: 14px;"><b>'+$("#create_process").val()+'</b></span><div class="btn-group pull-right" style="padding-right: 6px;"> <button type="button" class="btn btn-default btn-sm edit"><span class="glyphicon glyphicon-pencil"></span></button> <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash"></span></button> </div> <div class="clearfix"></div><ul class="list-group equipments" style="margin-bottom: 0px;min-height: 15px;"></ul></li>');
		$("#create_process").val('');
		addSortable();
		addEventHandles();
	}
});
$(".btn-add-equipment").click(function(){
	if ($("#create_equipment").val() != ""){
		$(".new-equipment").append('<li class="list-group-item equipment" style="padding: 5px 5px 5px 15px;"><span style="font-size: 14px;">'+$("#create_equipment").val()+'</span> <div class="btn-group pull-right"> <button type="button" class="btn btn-default btn-sm edit"><span class="glyphicon glyphicon-pencil"></span></button> <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal"><span class="glyphicon glyphicon-trash"></span></button> </div> <div class="clearfix"></div></li>');
		$("#create_equipment").val('');
		addSortable();
		addEventHandles();
	}
});

$(".submit").click(function(){
	var data = {};
	var process_count = 0;
	var equipment_count = 0;
	$(".sorted-processes").find(".process").each(function(){
		var process = $($(this).find('span')[0]).text();
		equipment_count = 0;
		data[process] = {id: $(this).data('id'), order: process_count, name: process, equipment: {}};
		process_count++;
		$(this).find('.equipment').each(function(){
			var equipment = $($(this).find('span')[0]).text();
			data[process]['equipment'][equipment] = {id: $(this).data('id'), order: equipment_count, name: equipment};
			equipment_count++;
		});
	});
	$.ajax({
	    url: "{{action('AdminController@saveProcesses')}}",
	    type: 'POST',
	    data: data,
	    success: function(data) {
	    	window.location.href = "{{action('AdminController@processes')}}";
	    }
	});
});	
</script>
@stop
