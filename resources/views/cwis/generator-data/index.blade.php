@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('errors.list')
<div class="card">
    <div class="card-header">
        <a href="{{ action('Cwis\CwisGeneratorDataController@create', ['year' => $year]) }}" id="add-indicator-data" class="btn btn-info">{{ __('Add Indicator Data') }}</a>
        <button class="btn btn-info float-right" id="headingOne" type="button" data-toggle="collapse" data-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">{{ __('Show Filter') }}</button>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-12">
                <div class="accordion" id="accordionExample">
                    <div class="accordion-item">
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne">
                            <div class="accordion-body">
                                <form class="form-horizontal" id="filter-form">
                                    <div class="form-group row">
                                        <label for="generator-data-year" class="col-md-2 col-form-label">{{ __('Year') }}</label>
                                        <div class="col-md-2">
                                            <select name="year" id="generator-data-year" class="form-control">
                                                @foreach($years as $availableYear)
                                                    <option value="{{ $availableYear }}" {{ $availableYear == $year ? 'selected' : '' }}>{{ $availableYear }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <label for="generator-data-outcome" class="col-md-2 col-form-label">{{ __('Service Outcome') }}</label>
                                        <div class="col-md-2">
                                            <select name="service_outcome" id="generator-data-outcome" class="form-control">
                                                <option value="">{{ __('All Service Outcomes') }}</option>
                                                @foreach($serviceOutcomes as $value => $label)
                                                    <option value="{{ $value }}" {{ ($filters['service_outcome'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="card-footer text-right">
                                        <button type="submit" class="btn btn-info">{{ __('Filter') }}</button>
                                        <button id="reset-filter" type="button" class="btn btn-info">{{ __('Reset') }}</button>
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
        <p id="generator-data-action-preview" class="alert alert-info" role="status" hidden></p>
        <div style="overflow: auto; width: 100%;">
            <table id="data-table" class="table table-bordered table-striped dtr-inline" width="100%">
                <thead>
                    <tr>
                        <th>{{ __('Year') }}</th>
                        <th>{{ __('Service Outcome') }}</th>
                        <th>{{ __('Actions') }}</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>
@stop
@push('scripts')
<script>
    $(function () {
        var addUrl = @json(action('Cwis\CwisGeneratorDataController@create'));
        var dataTable = $('#data-table').DataTable({
            bFilter: false,
            processing: true,
            serverSide: true,
            scrollCollapse: true,
            ajax: {
                url: @json(action('Cwis\CwisGeneratorDataController@getData')),
                data: function (d) {
                    d.year = $('#generator-data-year').val();
                    d.service_outcome = $('#generator-data-outcome').val();
                }
            },
            columns: [
                { data: 'year', name: 'year' },
                { data: 'service_outcome', name: 'service_outcome' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            order: [[0, 'desc']]
        });
        function updateAddLink() {
            $('#add-indicator-data').attr('href', addUrl + '?year=' + encodeURIComponent($('#generator-data-year').val()));
        }
        $('#filter-form').on('submit', function (e) {
            e.preventDefault();
            updateAddLink();
            dataTable.ajax.reload();
        });
        $('#generator-data-year').on('change', updateAddLink);
        $('#reset-filter').on('click', function () {
            $('#generator-data-year').val(@json((string) now()->year));
            $('#generator-data-outcome').val('');
            updateAddLink();
            dataTable.ajax.reload();
        });
        $('#data-table').on('click', '[data-preview-action]', function () {
            var notice = document.getElementById('generator-data-action-preview');
            notice.textContent = this.getAttribute('aria-label') + ' — ' + @json(__('Preview only. No data has been changed.'));
            notice.hidden = false;
        });
    });
</script>
@endpush
