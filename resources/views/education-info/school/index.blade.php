@extends('layouts.dashboard')
@section('title', 'Schools')

@section('content')
    <!-- Modal -->
    <div class="modal fade" id="educationModal" tabindex="-1" role="dialog" aria-labelledby="educationModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="educationModalLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Content loaded by Ajax -->
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            @can('Add Education')
                <a href="{{ route('education.school.create') }}" class="btn btn-info">Add School</a>
            @endcan
            @can('Export Education')
                <a href="{{-- {{ route('education.export') }} --}}" class="btn btn-info">Export CSV</a>
            @endcan
            @can('Export Education')
                <a href="#" id="export-kml" class="btn btn-info">Export KML</a>
            @endcan

            <a href="#" class="btn btn-info float-right" data-toggle="collapse"
                data-target="#filterCollapse" aria-expanded="true" aria-controls="filterCollapse">
                Show Filter
            </a>
        </div>
        <div class="card-body">
            <div class="collapse" id="filterCollapse">
                <form id="filter-form" class="mb-4">
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Institution Name</label>
                            <input type="text" class="form-control" id="institution_name" placeholder="Name">
                        </div>
                        <div class="form-group col-md-3">
                            <label>Type</label>
                            <select class="form-control" id="institution_type">
                                <option value="">Select Type</option>
                                @foreach ($institution_types as $key => $type)
                                    <option value="{{ $key }}">{{ $type }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Ward</label>
                            <select class="form-control" id="ward_number">
                                <option value="">Select Ward</option>
                                @foreach ($wards as $key => $ward)
                                    <option value="{{ $key }}">{{ $ward }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group col-md-3">
                            <label>Ownership</label>
                            <select class="form-control" id="ownership">
                                <option value="">Public / Private</option>
                                <option value="Public">Public</option>
                                <option value="Private">Private</option>
                            </select>
                        </div>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="btn btn-info">Filter</button>
                        <button type="reset" id="reset-filter" class="btn btn-info">Reset</button>
                    </div>
                </form>
            </div>

            <div style="overflow:auto; width:100%;">
                <table id="education-table" class="table table-bordered table-striped" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Ward</th>
                            <th>Type</th>
                            <th>Ownership</th>
                            <th>Contact Person</th>
                            <th>Phone</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
@stop

@push('scripts')
<script>
    $(function() {
        var table = $('#education-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('education.school.data') }}',
                type: 'GET',              // be explicit
                dataType: 'json',         // expect JSON
                data: function (d) {
                    d.institution_name = $('#institution_name').val();
                    d.institution_type = $('#institution_type').val();
                    d.ward_number      = $('#ward_number').val();
                    d.ownership        = $('#ownership').val();
                },
                error: function (xhr, error, thrown) {
                    console.error('DataTables Ajax error:', xhr.status, error, thrown);
                    console.error(xhr.responseText); // this will show your Laravel error/HTML
                    alert('Failed to load education data. Check console for details.');
                }
            },
            columns: [
                { data: 'custom_school_id',      name: 'custom_school_id' },
                { data: 'name',                  name: 'name' },
                { data: 'ward_no',               name: 'ward_no' },
                { data: 'type',                  name: 'type' },                 // Type
                { data: 'ownership',             name: 'ownership' },            // Ownership
                { data: 'contact_person_name',   name: 'contact_person_name' },
                { data: 'contact_person_number', name: 'contact_person_number' },
                { data: 'action',                name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'asc']]
        });

        $('#filter-form').on('submit', function(e) {
            e.preventDefault();
            table.draw();
        });

        $('#reset-filter').on('click', function() {
            $('#filter-form')[0].reset();
            table.draw();
        });

        $(document).on('click', '.show-institution', function() {
            var id = $(this).data('id');
            $.ajax({
                url: '{{ url("education") }}/' + id,
                type: 'GET',
                success: function(data) {
                    $('#educationModal .modal-title').text(data.name);
                    $('#educationModal .modal-body').html(data.detailsHtml);
                    // $('#educationModal').modal('show');
                },
                error: function(xhr) {
                    console.error('Show institution error:', xhr.responseText);
                }
            });
        });
    });
</script>
@endpush

