@extends('layouts.dashboard')
@section('title', $page_title)
@push('style')
    <style type="text/css">
        .dataTables_filter {
            display: none;
        }
    </style>
@endpush
@section('content')
    <div class="card">
        <div class="card-header">
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

                                            <label for="bin" class="control-label col-md-2">House Number </label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="bin"
                                                    placeholder="House Number" />
                                            </div>

                                            <label for="date_from" class="control-label col-md-2">Date From</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="date_from"
                                                    placeholder="Select Date From" />
                                            </div>
                                            <label for="date_to" class="control-label col-md-2">Date To</label>
                                            <div class="col-md-2">
                                                <input type="text" class="form-control" id="date_to"
                                                    placeholder="Select Date To" />
                                            </div>


                                        </div>
                                        <div class="card-footer text-right">
                                            <button type="submit" class="btn btn-info" style="font-family: 'Open Sans', sans-serif;">Filter</button>
                                            <button type="reset" class="btn btn-info reset" style="font-family: 'Open Sans', sans-serif;">Reset</button>
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
            <div style="overflow: auto; width: 100%;" >
                <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%" style="font-family: 'Open Sans', sans-serif;">
                    <thead>
                        <tr>
                            <th>House Number</th>
                            <th>Tax Code</th>
                            <th>Survey Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div><!-- /.box-body -->
    </div><!-- /.box -->
    @include('building-info.building-surveys.kmlPreviewModal')
@stop

@push('scripts')
    <script>
        $(function() {
            var dataTable = $('#data-table').DataTable({
                bFilter: false,
                processing: true,
                serverSide: true,
                scrollCollapse: true,
                "bStateSave": true,
                "stateDuration": 1800, // In seconds; keep state for half an hour

                ajax: {
                    url: '{!! url('building-info/building-surveys/data') !!}',
                    data: function(d) {
                        d.bin = $('#bin').val();
                        d.tax_code = $('#tax_code').val();
                        d.collected_date = $('#collected_date').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'bin',
                        name: 'bin'
                    },
                    {
                        data: 'tax_code',
                        name: 'tax_code',

                    },
                    {
                        data: 'collected_date',
                        name: 'collected_date',

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
                            Swal.fire(
                                'Deleted!',
                                'Building Survey Record been deleted.',
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

            var bin = '',
                tax_code = '';
            collected_date = '';


            $('#filter-form').on('submit', function(e) {

                var date_from = $('#date_from').val();
                var date_to = $('#date_to').val();

                if ((date_from !== '') && (date_to === '')) {

                    Swal.fire({
                        title: 'Date To is required',
                        text: "Please Select Date To ",
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'close'
                    })

                    return false;
                }
                if ((date_from === '') && (date_to !== '')) {

                    Swal.fire({
                        title: 'Date From is required',
                        text: "Please Select Date From ",
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'close'
                    })

                    return false;
                }
                e.preventDefault();
                dataTable.draw();
                bin = $('#bin').val();
                tax_code = $('#tax_code').val();
                date_from = $('#date_from').val();
                date_to = $('#date_to').val();
            });

            $(".reset").on("click", function(e) {
                $('#bin').val('');
                $('#date_from').val('');
                $('#date_to').val('');
                $('#data-table').dataTable().fnDraw();
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
            $('.date, #date_from, #date_to').focus(function() {
                $(this).blur();
            });

            $('#headingOne').click(function() {

                if ($(this).text() == 'Hide Filter') {
                    $('#mydiv').slideDown("slow");
                } else if ($(this).text() == 'Show Filter') {
                    $('#mydiv').slideUp("slow");
                }
            });
        });
    </script>
@endpush
