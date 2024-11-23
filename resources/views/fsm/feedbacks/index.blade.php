<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)


@section('content')

<div class="card border-0">
    <div class="card-header">
        <a class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse"
            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
            Show Filter
        </a>
        @can('Export Feedbacks')
        <a href="{{ action('Fsm\FeedbackController@export') }}" id="export" class="btn btn-info">Export to CSV</a>
        @endcan
    </div><!-- /.card-header -->
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
                                        <label for="application_id" class="control-label col-md-2">Application
                                            ID</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="application_id" placeholder="Application ID"/>
                                        </div>
                                        <label for="ward_select" class="control-label col-md-2">Ward</label>
                                        <div class="col-md-2">
                                            <select class="form-control" id="ward_select">
                                                <option value="">All Wards</option>
                                                @foreach($wards as $key=>$value)
                                                <option value="{{$key}}">{{$value}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
        
                                    <div class="form-group row">
                                        <label for="date_from" class="control-label col-md-2">Date From</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="date_from" placeholder="Date From"/>
                                        </div>
                                        <label for="date_to" class="control-label col-md-2">Date To</label>
                                        <div class="col-md-2">
                                            <input type="text" class="form-control" id="date_to" placeholder="Date To"/>
                                        </div>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-info ">Filter</button>
                                        <button id="reset-filter" type="reset" class="btn btn-info reset">Reset</button>
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
        </div>
        <!--- row !-->
    </div>
    <!--- card body !-->
    <div class="card-body">
    <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
            <thead>
                <tr>
                    <th>Application ID</th>
                    <th>Ward</th>
                    <th>Feedback Date</th>
                    <th>Actions</th>

                </tr>
            </thead>
        </table>
</div>
    </div><!-- /.card-body -->
</div> <!-- /.card -->

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
            url: '{!! url("fsm/feedback/getData") !!}',
            data: function(d) {
                d.application_id = $('#application_id').val();
                d.ward = $('#ward_select').val();
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
            }
        },
        columns: [{
                data: 'application_id',
                name: 'application_id'
            },
            {
                data: 'ward',
                name: 'ward'
            },
            {
                data: 'created_at',
                name: 'created_at'
            },
            {
                data: 'action',
                name: 'action',
                orderable: false,
                searchable: false
            }
        ],
         order: [
            [0, 'desc']
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
                    form.submit();
                    Swal.fire(
                        'Deleted!',
                        'Your file has been deleted.',
                        'success'
                    )
                }
            })
        });
    });
    $('#filter-form').on('submit', function(e) {
        var date_from = $('#date_from').val();
        var date_to = $('#date_to').val();
        if ((date_from !== '') && (date_to === '')) {

            Swal.fire({
            title: 'Date To is required',
            text: "Please select Date To!",
            icon: 'warning',
            showCancelButton: false,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Close'
        })

            return false;
        }
        if ((date_from === '') && (date_to !== '')) {

                    Swal.fire({
                    title: 'Date From is required',
                    text: "Please select Date From!",
                    icon: 'warning',
                    showCancelButton: false,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'close'
                })

                    return false;
                }
    });
    filterDataTable(dataTable);
    resetDataTable(dataTable);
    $("#export").on("click", function(e) {
        e.preventDefault();
        application_id = $('#application_id').val();
        ward = $('#ward_select').val();
        date_from = $('#date_from').val();
        date_to = $('#date_to').val();
        var searchData = $('input[type=search]').val();
        window.location.href = "{!! url('fsm/feedback/export?searchData=') !!}" + searchData +
            "&application_id=" + application_id + "&ward=" + ward + "&date_from=" + date_from + "&date_to=" + date_to;
    })



    $('#date_from, #date_to').daterangepicker({
        singleDatePicker: true,
        autoUpdateInput: false,
        showDropdowns: true,
        autoApply: true,
        drops: "auto"
    });

    $('#date_from, #date_to').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY'));
    });

    $('#date_from, #date_to').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });


    $('#date_from, #date_to').focus(function() {
        $(this).blur();
    });

});
</script>


@endpush