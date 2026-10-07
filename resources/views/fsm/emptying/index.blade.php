<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
{{-- Extend the main layout --}}
@extends('layouts.dashboard')
{{-- Add sections for the main layout --}}
@section('title', 'Emptying')
{{-- Add sections for the index layout --}}
@section('filter-form')
    @include('layouts.filter-form', ['formFields' => $filterFormFields])
@endsection
@section('data-table')
    <div style="overflow: auto; width: 100%;">
        <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Application ID</th>
                    <th>Sludge Volume (m³)</th>
                    <th>Emptied Date</th>
                    <th>Total Cost</th>
                    <th>Service Provider Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
        </table>
    </div>
@endsection

{{-- Include the layout inside the main content section --}}
@section('content')
    @include('layouts.index')
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            var dataTable = $('#data-table').DataTable({
                bFilter: false,
                processing: true,
                serverSide: true,
                stateSave: true,
                scrollCollapse: true,
                ajax: {
                    url: '{!! route('emptying.get-data') !!}',
                    data: function(d) {
                        d.application_id = $('#application_id').val();
                        d.emptied_date = $('#emptied_date').val();
                        d.containment_code = $('#containment_id').val() ? $('#containment_id').val() : "{{$containment_code}}";
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    },
                },
                columns: [{
                        data: 'id',
                        name: 'id'
                    },
                    {
                        data: 'application_id',
                        name: 'application_id'
                    },

                    {
                        data: 'volume_of_sludge',
                        name: 'volume_of_sludge'
                    },
                    {
                        data: 'emptied_date',
                        name: 'emptied_date',
                        render: function(data) {
                            return moment(data).format("dddd, MMMM Do YYYY");
                        }
                    },
                    {
                        data: 'total_cost',
                        name: 'total_cost'
                    },
                    {
                        data: 'service_provider_id',
                        name: 'service_provider_id'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                ],
                order: [
                    [0, 'desc']
            });

            filterDataTable(dataTable);
            resetDataTable(dataTable);

            $('#filter-form').on('submit', function(e) {
                var date_from = $('#date_from').val();
                var date_to = $('#date_to').val();
                if (date_from !== '' && date_to !== '' && (date_to <= date_from)) {
                    Swal.fire({
                        title: 'Invalid Date Range',
                        text: "Date To cannot be Before Date From!",
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Close'
                    });

                    return false;
                }
                if ((date_from !== '') && (date_to === '')) {
                    Swal.fire({
                        title: 'Date To is Required',
                        text: "Please Select Date To!",
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Close'
                    });

                    return false;
                }
                if ((date_from === '') && (date_to !== '')) {
                    Swal.fire({
                        title: 'Date From is Required',
                        text: "Please Select Date From!",
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Close'
                    });

                    return false;
                }
                e.preventDefault();
                dataTable.draw();
            });
        });
    </script>
@endpush
