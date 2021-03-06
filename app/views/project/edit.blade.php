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
		<h1>Edit Project</h1>
	</div>
	<div class="col-md-12">
      <form id="projectForm" method="post" 
			    data-bv-message="This value is not valid"
			    data-bv-feedbackicons-valid="glyphicon glyphicon-ok"
			    data-bv-feedbackicons-invalid="glyphicon glyphicon-remove"
			    data-bv-feedbackicons-validating="glyphicon glyphicon-refresh">
			<div class="col-md-6">
				<div class="well" style="background: #d9edf7;">
					<h2 style="margin: 0px; padding: 0px;">Project</h2>
					<div class="form-group">
						<label for="description">Description</label>
						<input type="text" class="form-control" name="description" value="{{$project->description}}" id="description" placeholder="Enter Description"  
						data-bv-message="The description is not valid"
		                required
		                data-bv-notempty-message="A description is required and cannot be empty">
					</div>
					<div class="form-group">
						<label for="docket">Docket</label>
						<input type="text" class="form-control" name="docket" value="{{$project->docket}}" id="docket" placeholder="Enter Docket Number" 
						data-bv-message="The docket number is not valid"
		                required
		                data-bv-notempty-message="A docket number is required and cannot be empty">
					</div>
					<div class="form-group">
						<label for="customer">Customer</label>
						<select class="form-control" id="customer" placeholder="Select Customer">
						@foreach($customers as $customer)
						@if ($customer->id == $project->customer()->first()->id)
						<option value="{{$customer->id}}" selected>{{$customer->name}}</option>
						@else
						<option value="{{$customer->id}}">{{$customer->name}}</option>
						@endif
						@endforeach
						</select>
					</div>
					<div class="form-group">
						<label for="sheets"># Sheets</label>
						<input type="number" class="form-control" name="sheets" value="{{$project->sheets}}" id="sheets" placeholder="Enter Number of Sheets" required>
					</div>
					<div class="form-group">
						<label for="stock">Stock</label>
						<textarea class="form-control" rows="2" id="stock">{{$project->stock}}</textarea>
					</div>
					<div class="form-group">
						<label for="notes">Notes</label>
						<textarea class="form-control" rows="2" id="notes">{{$project->notes}}</textarea>
					</div>
					<div class="form-group">
						<label for="customer">Rep</label>
						<select class="form-control" id="rep" placeholder="Select Representative">
						@foreach($users as $user)
						@if ($user->id == $project->user()->first()->id)
						<option value="{{$user->id}}" selected>{{$user->first_name}} {{$user->last_name}}</option>
						@else
						<option value="{{$user->id}}">{{$user->first_name}} {{$user->last_name}}</option>
						@endif
						@endforeach
						</select>
					</div>
				    <div class="form-group form-inline">
				        <label for="duedate">Due Date</label>
			            <input type="text" id="duedate" data-customClass="form-control" data-smartDays="true" data-format="DD-MM-YYYY" data-template="D MMM YYYY" name="due_date" value="{{$project->due_date}}">
				    </div>
				</div>
			</div>
			<div class="col-md-6">
				<div class="well" style="background: #d9edf7;">
					<div class="row">
						<div class="col-md-6">
							<h2 style="margin: 0px; padding: 0px;">Tasks</h2>
						</div>
						<div class="col-md-6 text-right">
							<button type="button" class="btn btn-success btn-sm" id="addTask"><span class="glyphicon glyphicon-plus" style="margin: 0px;"></span></button>
						</div>
					</div>
					<div class="row" style="margin-top: 15px;">
						<div class="col-md-12">
							<div class="tasks">
							@foreach($project->tasks()->get() as $task)
								<div class="panel panel-warning" data-task="{{$task->id}}">
									<div class="panel-heading">
										<h4 class="panel-title pull-left">
											<a data-toggle="collapse" href="#collapseTask{{$task->id}}">
											@if ($task->start_date != null && $task->end_date != null)
											<img src="{{asset('/img/ON_schedule.png')}}" width="18" />
											@endif
											{{Process::find(ProcessEquipment::find($task->equipment_id)->process_id)->name}} ({{ProcessEquipment::find($task->equipment_id)->name}}) - {{$task->duration}} hours - {{ucfirst($task->status)}}<br />
											</a>
										</h4>
										<div class="col-md-2 pull-right text-right" style="padding: 0px;">
											<button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myModal">
												<span class="glyphicon glyphicon-trash" style="margin: 0px;"></span>
											</button>
										</div>
										@if ($task->start_date != null && $task->end_date != null)
										<div class="row">
											<div class="col-md-7">
												<span style="padding-left: 23px;font-size: 11px;">{{date('l, F d, Y', substr($task->start_date, 0, 10))}}</span>
											</div>
										</div>
										@endif
										<div class="clearfix"></div>
									</div>
									<div id="collapseTask{{$task->id}}" class="panel-collapse collapse">
										<div class="panel-body">
											<div class="form-group">
												<label for="process">Process</label>
												<select class="form-control process" id="process" placeholder="Select Process">
												@foreach($processes as $process)
													@if ($process->id == ProcessEquipment::find($task->equipment_id)->process_id)
													<option value="{{$process->id}}" selected>{{$process->name}}</option>
													@else
													<option value="{{$process->id}}">{{$process->name}}</option>
													@endif
												@endforeach
												</select>
											</div> 
											<div class="form-group">
												<label for="equipment">Equipment</label>
												<select class="form-control equipment" id="equipment" placeholder="Select Equipment">
													@foreach(Process::find(ProcessEquipment::find($task->equipment_id)->process_id)->equipment()->get() as $equipment)
														@if ($equipment->id == $task->equipment_id)
														<option value="{{$equipment->id}}" selected>{{$equipment->name}}</option>
														@else
														<option value="{{$equipment->id}}">{{$equipment->name}}</option>
														@endif
													@endforeach 
												</select>
											</div>
											<div class="form-group">
												<label for="duration">Duration</label>
												<input type="number" class="form-control duration" name="duration[]" value="{{$task->duration}}" id="duration" placeholder="Enter Duration" required>
											</div> 
											<div class="form-group">
												<label for="notes">Notes</label>
												<textarea class="form-control notes" rows="2" id="notes">{{$task->notes}}</textarea>
											</div>
											<div class="form-group">
												<label for="status">Status</label>
												<select class="form-control status" id="status" placeholder="Select Status">
													<option value="pending" {{($task->status == "pending") ? "selected" : ""}}>Pending</option>
													<option value="approved" {{($task->status == "approved") ? "selected" : ""}}>Approved</option>
													<option value="in-progress" {{($task->status == "in-progress") ? "selected" : ""}}>In Progress</option>
													<option value="complete" {{($task->status == "complete") ? "selected" : ""}}>Complete</option>
												</select>
											</div>
										</div>
									</div>
								</div>
							@endforeach
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-md-12 text-right">
				<button type="button" class="btn btn-danger pull-left" data-toggle="modal" data-target="#projectModal"><span class="glyphicon glyphicon-trash"></span> Delete Project</button>
				<button type="submit" class="btn btn-primary"><span class="glyphicon glyphicon-save"></span> Save Project</button>
			</div>
		</form>
	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Delete Task</h4>
      </div>
      <div class="modal-body">
        Are you sure you want to remove this task permanently?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary delete">Delete</button>
      </div>
    </div>
  </div>
</div>
<!-- Modal -->
<div class="modal fade" id="projectModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Delete Project</h4>
      </div>
      <div class="modal-body">
        Are you sure you want to remove this project permanently?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary delete">Delete</button>
      </div>
    </div>
  </div>
</div>
@stop

{{-- Javascript --}}
@section('javascript')
<script>
var num_tasks = 1;
var equipment = {};
$(document).ready(function(){
	$("#projectForm").bootstrapValidator({
		feedbackIcons: {
            valid: 'glyphicon glyphicon-ok',
            invalid: 'glyphicon glyphicon-remove',
            validating: 'glyphicon glyphicon-refresh'
        }
    });
	$("#projectForm").unbind('click', "form#projectForm.bv-form");
    $("#projectForm").submit(function(e){
    	e.preventDefault();
    	if($("#projectForm").data('bootstrapValidator').isValid()){
			var formData = {};
	    	formData.project = {};
	    	formData['project_id'] = "{{$project->id}}";
	    	formData['project']['description'] = $("#description").val();
	    	formData['project']['docket'] = $("#docket").val();
	    	formData['project']['sheets'] = $("#sheets").val();
	    	formData['project']['stock'] = $("#stock").val();
	    	formData['project']['customer_id'] = $("#customer").val();
	    	formData['project']['notes'] = $("#notes").val();
	    	formData['project']['user_id'] = $("#rep").val();
	    	formData['project']['due_date'] = $("#duedate").val();
	    	formData.tasks = {};
	    	$.each($(".tasks").find(".panel"), function(index, element){
	    		formData['tasks'][index] = {};
	    		if ($(element).data('task') != undefined){
		    		formData['tasks'][index]['id'] = $(element).data('task');
		    	}
	    		formData['tasks'][index]['equipment_id'] = $(element).find(".equipment").val();
	    		formData['tasks'][index]['duration'] = $(element).find(".duration").val();
	    		formData['tasks'][index]['status'] = $(element).find(".status").val();
	    		formData['tasks'][index]['notes'] = $(element).find(".notes").val();
	    	});
      		$.ajax({
		        url: root+'/project/save',
		        type: 'POST',
		        data: formData,
		        success: function(data) {
		        	window.location.href = "{{action('ProjectController@editor')}}";
		        }
		    });
    	}
    });
	$("#customer, #rep").chosen();
	$('#duedate').combodate({
		smartDays: true,
		customClass: "form-control"
	});
	$.ajax({
	  type: "GET",
	  async: false,
	  url: root+"/project/getEquipment",
      success: function(data) {
      	equipment = data;
      	updateEquipmentHandler();
      }
	});
});

$("#addTask").click(function(){
	$(".tasks").append('<div class="panel panel-warning"> <div class="panel-heading"> <h4 class="panel-title pull-left"> <a data-toggle="collapse" href="#collapseTask'+num_tasks+'"> Task </a> </h4> <div class="col-md-2 pull-right text-right" style="padding: 0px;"> <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#myModal"> <span class="glyphicon glyphicon-trash" style="margin: 0px;"></span> </button> </div> <div class="clearfix"></div> </div> <div id="collapseTask'+num_tasks+'" class="panel-collapse collapse in"> <div class="panel-body"> <div class="form-group"> <label for="process">Process</label> <select class="form-control process" id="process" placeholder="Select Process"> @foreach($processes as $process) <option value="{{$process->id}}">{{$process->name}}</option> @endforeach </select> </div> <div class="form-group"> <label for="equipment">Equipment</label> <select class="form-control equipment" id="equipment" placeholder="Select Equipment"> @if (count($processes) > 0) @foreach($processes[0]->equipment()->get() as $equipment) <option value="{{$equipment->id}}">{{$equipment->name}}</option> @endforeach @endif</select> </div> <div class="form-group"> <label for="duration">Duration</label> <input type="number" class="form-control duration" name="duration[]" id="duration" placeholder="Enter Duration" required> </div> <div class="form-group"> <label for="notes">Notes</label> <textarea class="form-control notes" rows="2" id="notes"></textarea> </div> <div class="form-group"> <label for="status">Status</label> <select class="form-control status" id="status" placeholder="Select Status"> <option value="pending">Pending</option> <option value="approved">Approved</option> <option value="in-progress">In Progress</option> <option value="complete">Complete</option> </select> </div> </div> </div> </div>');
	num_tasks++;
	updateEquipmentHandler();
	$('#projectForm').bootstrapValidator('addField', $("input[name='duration[]']"));
});

$(".equipment, .duration, .status").change(function(){
	updateTaskName($(this).closest(".panel"));
});

function updateTaskName(panel){
	$(panel).find('h4 a').text($(panel).find('.process').find(':selected').text() + " (" + $(panel).find('.equipment').find(':selected').text() + ") - " + $(panel).find('.duration').val() + " hours - " + $(panel).find('.status').find(':selected').text());
}

function updateEquipmentHandler(){
	$(".process").change(function(){
		var self = $(this);
		$(this).parent().parent().find('.equipment').html('');
		$.each(equipment[$(this).val()], function(index, element){
			if (index == 0){
				self.parent().parent().find('.equipment').append('<option value="'+element.id+'" selected>'+element.name+'</option>');
			}else{
				self.parent().parent().find('.equipment').append('<option value="'+element.id+'">'+element.name+'</option>');
			}
		});
		updateTaskName($(this).closest(".panel"));
	});
}

$('#myModal').on('shown.bs.modal', function (e) {
	var self = $(this);
	$(this).find('.delete').click(function(){
		$(e.relatedTarget).closest('.panel').remove();
		self.modal('hide');
	});
});
$('#myModal').on('hidden.bs.modal', function (e) {
	$(this).find('.delete').unbind('click');
});
</script>
@stop
