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
        @can('Add Sanitation System Type')
        <a href="{{ action('BuildingInfo\SanitationSystemTypeController@create') }}" class="btn btn-info">Create new
            Sanitation System Technology</a>
        @endcan
        @can('Export Sanitation System Types')
        <a href="#" id="export" class="btn btn-info">Export Sanitation System Type</a>
        @endcan
        <a href="#" class="btn btn-info float-right" data-toggle="collapse" data-target="#collapseFilter"
            aria-expanded="false" aria-controls="collapseFilter">Show Filter</a>
    </div><!-- /.card-header -->
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="accordionFilter">
                    <div class="accordion-item">
                        <div id="collapseFilter" class="collapse" aria-labelledby="filter"
                            data-parent="#accordionFilter">
                            <div class="accordion-body">
                                <form class="form-horizontal" id="filter-form">
                                    <div class="form-group row">
                                        <label for="type" class="col-md-2 control-label">Type Name</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="type" placeholder="Type Name" />
                                        </div>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-info">Filter</button>
                                        <button type="reset" id="reset-filter" class="btn btn-info reset">Reset</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
    <table id="data-table" class="table table-striped table-bordered nowrap" style="width:auto" >
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
            </table>
      
    </div><!-- /.box-body -->
</div><!-- /.box -->
@stop

@push('scripts')
<script>
$(function() {
    var dataTable = $('#data-table').DataTable({
        bFilter: false,
        processing: true,
        serverSide: true,
        scrollX: true,
        ajax: {
            url: '{!! url("building-info/sanitation-system-types/data") !!}',
            data: function(d) {
                d.type = $('#type').val();
            }
        },
        columns: [{
                data: 'id',
                name: 'id'
            },
            {
                data: 'type',
                name: 'type'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
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
                        'Sanitation System Type will be deleted.',
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
    resetDataTable(dataTable);

    $('#filter-form').on('submit', function(e) {
        e.preventDefault();
        dataTable.draw();
    });



    $("#export").on("click", function(e) {
        e.preventDefault();
        var type = $('#type').val();
        var searchData = $('input[type=search]').val();
        window.location.href =
            "{!! url('building-info/sanitation-system-types/export?searchData=') !!}" + searchData +
            "&type=" + type;

    });

});
</script>
@endpush