<!-- Last Modified Date: 09-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@push('style')
<style type="text/css">
.dataTables_filter {
  display: none;
}
</style>
@endpush
@section('title', $page_title)
@section('content')
	<div class="card">
        <div class="card-header">
          @can('Add Employee Info')
          <a href="{{ action('Fsm\EmployeeInfoController@create') }}" class="btn btn-info">Add Employee Information</a>
          @endcan
          @can('Export Employee Infos')
          <a href="#" id="export" class="btn btn-info">Export to CSV</a>
          @endcan
          <a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Show Filter
            </a>
        </div><!-- /.card-header -->
        <div class="card-body">
    <div class="row">
      <div class="col-12">
        <div class="accordion" id="accordionExample">
          <div class="accordion-item">
            <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample">
              <div class="accordion-body">
                <form class="form-horizontal" id="filter-form">
                    <div class="form-group row">
                        <label for="id" class="col-md-2 col-form-label ">Employee ID</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="id" placeholder="Employee ID"/>
                            </div>
                        <label for="employee_name" class="col-md-2 col-form-label text-right">Employee Name</label>
                            <div class="col-md-2">
                                <input type="text" class="form-control" id="employee_name" placeholder="Employee Name"/>
                            </div>
                        <label for="employee_type" class="col-md-2 col-form-label text-right">Designation</label>
                            <div class="col-md-2">
                                <select class="form-control" id="employee_type">
                                    <option value="">Designation</option>
                                    <option value="Management">Management</option>
                                    <option value="Driver">Driver</option>
                                    <option value="Cleaner/Emptier">Cleaner/Emptier</option>
                                </select>
                            </div>
                    </div>
                    <div class="form-group row">
                    <label for="status" class="col-md-2 col-form-label ">Status</label>
                    <div class="col-md-2">
                      <select class="form-control chosen-select" id="status" name="status">
                                          <option value="">Status</option>

                                          <option value="true">Active</option>
                                          <option value="false">Inactive</option>

                      </select>
                    </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info ">Filter</button>
                        <button type="reset" class="btn btn-info reset ">Reset</button>
                    </div>
                </form>
              </div>  <!--- accordion body!-->
            </div>    <!--- collapseOne!-->
          </div>      <!--- accordion item!-->
        </div>        <!--- accordion !-->
      </div>            <!---col!-->
    </div>            <!--- row !-->
  </div>              <!--- card body !-->

  <div class="card-body">
    <div style="overflow: auto; width: 100%;">
    <table id="data-table" class="table table-bordered table-striped" width="100%">
            <thead>
                <tr>
                <th>Employee ID</th>
                <th>Employee Name</th>
                <th>Address</th>
                <th>Designation</th>
                <th>Monthly Remuneration</th>
                <th>Status</th>
                <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
    </div><!-- /.card-body -->
    </div><!-- /.card -->
@stop

@push('scripts')
<script>
$(function() {
    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollCollapse: true,
        ajax: {
          url: '{!! url("fsm/employee-infos/data") !!}',
            data: function(d) {
                d.id = $('#id').val();
                d.employee_name = $('#employee_name').val();
                d.employee_type = $('#employee_type').val();
                d.status = $('#status').val();
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'name', name: 'name' },
            { data: 'address', name: 'address' },
            { data: 'employee_type', name: 'employee_type' },
            { data: 'wage', name: 'wage' },
            { data: 'status', name: 'status' },
            { data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        order: [ [0, 'desc'] ]
    }).on( 'draw', function () {

        $('.delete').on('click', function(e) {

         var form =  $(this).closest("form");
         event.preventDefault();
         Swal.fire({
           title: 'Are you sure?',
           text: "You won't be able to revert this!",
           icon: 'warning',
           showCancelButton: true,
           confirmButtonColor: '#3085d6',
           cancelButtonColor: '#d33',
           confirmButtonText: 'Yes, delete it!'
         }).then((result) => {
           if (result.isConfirmed) {
             Swal.fire(
               'Deleted!',
               'Employee Information will be deleted.',
               'success'
             ).then((willDelete) => {
                    if (willDelete) {
                      form.submit();
                    }
                  })
           }
         })
     });
    } );
    var id = '',
    employee_name = '',
    employee_type = '',
    status = '';
    $('#filter-form').on('submit', function(e){
        e.preventDefault();
        dataTable.draw();
    });
    $(".reset").on("click",function(e){
        $('#id').val('');
        $('#employee_name').val('');
        $('#employee_type').val('');
        $('#status').val('');
        $('#data-table').dataTable().fnDraw();
    });
    $("#export").on("click",function(e){
        e.preventDefault();
        var id = $('#id').val();
        var employee_name = $('#employee_name').val();
        var employee_type = $('#employee_type').val();
        var status = $('#status').val();
        var searchData=$('input[type=search]').val();
        window.location.href="{!! url('fsm/employee-infos/export?searchData=') !!}"+searchData+
        "&id=" + id +
        "&employee_name=" + employee_name +
        "&employee_type=" + employee_type +
        "&status=" + status;
    })

});



</script>
<!-- toggle filter show hide -->

@endpush
