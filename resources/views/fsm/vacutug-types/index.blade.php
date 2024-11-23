<!-- Last Modified Date: 18-04-2024
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
        @can('Add Vacutug Type')
        <a href="{{ action('Fsm\VacutugTypeController@create') }}" class="btn btn-info">Add Desludging Vehicle</a>
        @endcan
        @can('Export Vacutug Type')
        <a href="{{ action('Fsm\VacutugTypeController@export') }}" id="export" class="btn btn-info">Export to
            CSV</a>
        @endcan
        <a href="#" class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse"
            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Show Filter
        </a>
    </div><!-- /.box-header -->
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                            data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <form class="form-horizontal" id="filter-form">
                                    <div class="form-group row">
                                        <label for="license_plate_number" class="col-md-2 col-form-label ">Vehicle License Plate Number</label>
                                        <div class="col-md-2">

                                            <input type="text" class="form-control" id="license_plate_number" placeholder="Vehicle License Plate Number"/>

                                        </div>
                                        <label for="capacity" class="col-md-2 col-form-label">Capacity (m³)</label>
                                        <div class="col-md-2">

                                            <input type="number" class="form-control" id="capacity" placeholder="Capacity (m³)"/>

                                        </div>
                                        <label for="width" class="col-md-2 col-form-label ">Width (m)</label>
                                        <div class="col-md-2">

                                            <input type="number" class="form-control" id="width"  placeholder="Width (m)"/>

                                        </div>
                                    </div>

                                    <div class="form-group row">
                                    <label for="width" class="col-md-2 col-form-label ">Status</label>
                                    <div class="col-md-2">
                                    <select class="form-control chosen-select" id="status" name="status">
                                        <option value="">Status</option>

                                        <option value="true">Operational</option>
                                        <option value="false">Not Operational</option>

                                    </select>
                                    </div>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-info">Filter</button>
                                        <button type="reset" id="reset-filter" class="btn btn-info">Reset</button>
                                    </div>
                                </form>
                            </div>
                            <!--- accordion body!-->
                        </div>
                        <!--- collapseOne!-->
                    </div>
                    <!--- accordion item!-->
                </div>
                <!--- accordion !-->
            </div>
            <!---col!-->
        </div>
        <!--- row !-->
    </div>
    <!--- card body !-->
    <div class="card-body">
    <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
            <thead>
                <tr>
                    
                    <th>Service Provider</th>
                    <th>Vehicle License Plate Number</th>
                    <th>Capacity (m³)</th>
                    <th>Width (m)</th>
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
            url: '{!! url("fsm/desludging-vehicles/data") !!}',
            data: function(d) {
                d.license_plate_number = $('#license_plate_number').val();
                d.capacity = $('#capacity').val();
                d.width = $('#width').val();
                d.status = $('#status').val();
            }
        },
        columns: [
            {
                data: 'service_provider_id',
                name: 'service_provider_id'
            },
            {
                data: 'license_plate_number',
                name: 'license_plate_number'
            },
            {
                data: 'capacity',
                name: 'capacity'
            },
            {
                data: 'width',
                name: 'width'
            },
            
            {
                data: 'status',
                name: 'status'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],
        order: [
            [0, "desc"]
        ]
    }).on('draw', function() {
        $('.delete').on('click', function(e) {
            var form = $(this).closest("form");
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
                        'Desludging Vehicle will be deleted.',
                        'success'
                    ).then((willDelete) => {
                        if (willDelete) {
                            form.submit();
                        }
                    })
                }
            })

        });
    });
    var license_plate_number = '',
        capacity = '',
        width = '',
        status = '';

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        license_plate_number = $('#license_plate_number').val();
        capacity = $('#capacity').val();
        width = $('#width').val();
        status = $('#status').val();
        dataTable.draw();
    });

    //$('#data-table_filter input[type=search]').attr('readonly', 'readonly');

    resetDataTable(dataTable);

    $("#export").on("click", function(e) {
        e.preventDefault();
        var searchData = $('input[type=search]').val();
        license_plate_number = $('#license_plate_number').val();
        capacity = $('#capacity').val();
        width = $('#width').val();
        window.location.href = "{!! url('fsm/desludging-vehicles/export?searchData=') !!}" + searchData +
            "&license_plate_number=" + license_plate_number +
            "&capacity=" + capacity +
            "&width=" + width +
            "&status=" + status;
    });

});
</script>
@endpush
