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
		<table id="table_id" class="display">
		    <thead>
		        <tr>
                <th>ID</th>
		            <th>Docket</th>
		            <th>Customer</th>
		            <th>Job Description</th>
		            <th>Press</th>
		            <th># Sheets</th>
		            <th>Due Date</th>
		            <th>Rep</th>
		            <th>Notes</th>
		            <th>Duration</th>
		            <th>Colour</th>
		            <th>Status</th>
		            <th>Stock</th>
		        </tr>
		    </thead>
		</table>
		<!-- Button trigger modal -->
		<!--  data-toggle="modal" data-target="#myModal" -->
		<button class="btn btn-primary btn-lg" id='add_task' data-toggle="modal" data-target="#myModal">
		  Add Task
		</button>

  </div>
</div>

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
        <h4 class="modal-title" id="myModalLabel">Modal title</h4>
      </div>
      <form id="attributeForm" method="post" class="form-horizontal"
			    data-bv-message="This value is not valid"
			    data-bv-feedbackicons-valid="glyphicon glyphicon-ok"
			    data-bv-feedbackicons-invalid="glyphicon glyphicon-remove"
			    data-bv-feedbackicons-validating="glyphicon glyphicon-refresh">
      	<div class="modal-body">
				    <div class="form-group">
				        <label class="col-lg-3 control-label">Docket</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="docket" placeholder="Docket"
				                data-bv-notempty="true"
				                data-bv-notempty-message="A docket number is required and cannot be empty" />
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Customer</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="customer" placeholder="Customer Name"
				                data-bv-message="The customer name is not valid"

				                data-bv-notempty="true"
				                data-bv-notempty-message="The customer name is required and cannot be empty"

				                data-bv-regexp="true"
				                data-bv-regexp-regexp="^[a-zA-Z0-9_\.]+$"
				                data-bv-regexp-message="The customer name can only consist of alphabetical, number, dot and underscore"

				                data-bv-stringlength="true"
				                data-bv-stringlength-min="3"
				                data-bv-stringlength-max="30"
				                data-bv-stringlength-message="The customer name must be more than 3 and less than 30 characters long" />
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Description</label>
				        <div class="col-lg-5">
				            <textarea class="form-control" name="description"></textarea>
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Press</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="press" placeholder="Press"
				                data-bv-notempty="true"
				                data-bv-notempty-message="A press is required and cannot be empty" />
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label"># Sheets</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="sheets" placeholder="100"
				                data-bv-notempty="true"
				                data-bv-notempty-message="The number of sheets is required and cannot be empty" />
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Due Date</label>
				        <div class="col-lg-5">
				            <input type="text" id="date" data-customClass="form-control" data-smartDays="true" data-format="DD-MM-YYYY" data-template="D MMM YYYY" name="due_date" value="09-01-2013">
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Rep</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="rep" placeholder="Rep"
				                data-bv-message="The representative name is not valid"

				                data-bv-notempty="true"
				                data-bv-notempty-message="The representative name is required and cannot be empty"

				                data-bv-regexp="true"
				                data-bv-regexp-regexp="^[a-zA-Z0-9_\.]+$"
				                data-bv-regexp-message="The representative name can only consist of alphabetical, number, dot and underscore"

				                data-bv-stringlength="true"
				                data-bv-stringlength-min="3"
				                data-bv-stringlength-max="30"
				                data-bv-stringlength-message="The representative name must be more than 3 and less than 30 characters long" />
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Notes</label>
				        <div class="col-lg-5">
				            <textarea class="form-control" name="notes"></textarea>
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Duration</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="duration" placeholder="100"
				                data-bv-notempty="true"
				                data-bv-notempty-message="The duration is required and cannot be empty" />
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Colour</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="colour" placeholder="100"
				                data-bv-notempty="true"
				                data-bv-notempty-message="A colour is required and cannot be empty" />
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Status</label>
				        <div class="col-lg-5">
				            <select name='status' class='form-control selectpicker'>
				            	<option value='In Progress'>In Progress</option>
				            </select>
				        </div>
				    </div>

				    <div class="form-group">
				        <label class="col-lg-3 control-label">Stock</label>
				        <div class="col-lg-5">
				            <input type="text" class="form-control" name="stock" placeholder="100"
				                data-bv-notempty="true"
				                data-bv-notempty-message="A stock is required and cannot be empty" />
				        </div>
				    </div>
	      </div>
	      <div class="modal-footer">
	        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	        <button type="submit" id="task_submit" class="btn btn-primary">Save changes</button>
	      </div>
			</form>
    </div>
  </div>
</div>
@stop

{{-- Javascript --}}
@section('javascript')
<script>
var t = null;
$(document).ready( function () {
  $.fn.editable.defaults.mode = 'popup';
	$('#myModal').on('shown.bs.modal', function (e) {
		t.ajax.reload();
		$('#date').combodate({
			smartDays: true,
			customClass: "form-control"
		});    
		$('#attributeForm').bootstrapValidator({
        submitHandler: function(form) {
            $.ajax({
		        url: root+'/api/tasks/add',
		        type: 'POST',
		        dataType: 'text',
		        data: $("#attributeForm").serializeArray(),
		        success: function(data) {
		        	$('#myModal').modal('hide');
		        	t.ajax.reload();
		        }
		    });
        }
     });
		$('.selectpicker').selectpicker();
	});
    t = $('#table_id').DataTable( {
      "dom": 'TCR<"clear">lfrtip',
	  "pageLength": 10,
      "ajax": { 
      	'url': root+"/api/tasks/get"
    	},
      "columns": [
          { "data": "id"},
          { "data": "docket", },
          { "data": "customer", },
          { "data": "description", },
          { "data": "press", },
          { "data": "sheets", },
          { "data": "due_date", },
          { "data": "rep", },
          { "data": "notes", },
          { "data": "duration", },
          { "data": "colour", },
          { "data": "status", },
          { "data": "stock", }
      ],
      "tableTools": {
          "sSwfPath": "{{ asset('packages/datatables/extensions/TableTools/swf/copy_csv_xls_pdf.swf') }}",
          "sRowSelect": "multi",
          "aButtons": [
              {
                  "sExtends":    "collection",
                  "bSelectedOnly": true,
                  "sButtonText": "Export",
                  "aButtons": [ 
                      {
                          "sExtends": "csv",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      },
                      {
                          "sExtends": "xls",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      },
                      {
                          "sExtends": "pdf",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      },
                      {   
                          "sExtends": "print",
                          "sButtonText": "Printer Friendly",
                          "bSelectedOnly": true, 
                          "mColumns": "visible"
                      }
                  ]
              },
              'select_all', 'select_none'
          ]
      },
      "drawCallback": function () {
        activate_editables();
      },
      "oColReorder": {
  		"reorderCallback": function () {
        	activate_editables();
        }
      }
  } );
} );

function activate_editables() {
	$('.docket').editable({
		title: 'Enter Docket Number',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'docket', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.customer').editable({
		title: 'Enter Customer Name',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'customer', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.description').editable({
		title: 'Enter Description',
		'type':'textarea',
		'placement': 'right',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'description', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.press').editable({
		title: 'Enter Press',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'press', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.sheets').editable({
		title: 'Enter Sheet Count',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'sheets', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.due_date').editable({
		title: 'Enter Due Date',
		format: 'YYYY-MM-DD',
		viewformat: 'DD/MM/YYYY',
		template: 'D / MMMM / YYYY',
		combodate: {
		  minYear: 2000,
		  maxYear: 2015,
		  minuteStep: 1
		},
	    'type':'combodate',
	    'placement': 'right',
	    success: function(response, newValue) {
	    	console.log(newValue.unix());
	        $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'due_date', value: newValue.unix()},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
	    }
 	});
	$('.notes').editable({title: 'Enter Note', 'type':'textarea', 'placement': 'right',

        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'notes', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.sheets').editable({title: 'Enter Duration',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'sheets', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.status').editable({title: 'Enter Status',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'status', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.rep').editable({title: 'Enter Representative',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'rep', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.colour').editable({title: 'Enter Colour',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'colour', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.duration').editable({title: 'Enter Duration',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'duration', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
	$('.stock').editable({title: 'Enter Stock', 'type':'textarea', 'placement': 'left',
        success: function(response, newValue) {
            $.ajax({
		        url: root+'/api/tasks/editcolumn',
		        type: 'POST',
		        dataType: 'text',
		        data: {id: $($.parseHTML(t.row($(this).closest('tr').index()).data().id)).text(), field: 'stock', value: newValue},
		        success: function() {
		        	t.ajax.reload();
		        }
		    });
        }
    });
}

</script>
@stop
