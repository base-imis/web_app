@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('errors.list')
<div class="card card-info">
    <form id="indicator-data-form" class="form-horizontal" method="POST" action="{{ action('Cwis\CwisGeneratorDataController@store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card-header bg-white">
            <div class="form-inline">
                <a href="{{ action('Cwis\CwisGeneratorDataController@index', ['year' => $year]) }}" id="indicator-back" class="btn btn-info">{{ __('Back to List') }}</a>
                <div class="form-group ml-auto">
                    <label for="reporting-year" class="mr-2">{{ __('Reporting Year') }} <span class="text-danger">*</span></label>
                    <select id="reporting-year" name="year" class="form-control" required>
                        @foreach($years as $availableYear)
                            <option value="{{ $availableYear }}" {{ $availableYear == $year ? 'selected' : '' }}>{{ $availableYear }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="m-3">
            <p id="indicator-save-preview" class="alert alert-info" role="status" hidden></p>
            @foreach($sections as $section)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0"><span class="btn btn-link">{{ $section['title'] }}</span></h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered mb-0" width="100%">
                                <thead><tr><th width="55%">{{ __('Indicators') }}</th><th width="12%">{{ __('Outcome') }}</th><th width="33%">{{ __('Value') }}</th></tr></thead>
                                <tbody>
                                    @foreach($section['fields'] as [$id, $name, $code, $label, $hint, $step])
                                        <tr>
                                            <td>
                                                <label for="{{ $id }}" class="font-weight-normal mb-1">{{ __($label) }} <span class="text-danger">*</span></label>
                                                <small class="d-block text-muted">{{ $code }} · {{ __($hint) }}</small>
                                            </td>
                                            <td>{{ __('Equity') }}</td>
                                            <td>
                                                <input type="number" id="{{ $id }}" name="inputs[{{ $name }}]" class="form-control" min="0" step="{{ $step }}" placeholder="{{ __('Enter value') }}" required>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><span class="btn btn-link">{{ __('Sanitation Worker Policies and Coverage (EQ-6.1 - EQ-6.5)') }}</span></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" width="100%">
                            <thead><tr><th width="55%">{{ __('Indicators') }}</th><th width="12%">{{ __('Outcome') }}</th><th width="33%">{{ __('Value') }}</th></tr></thead>
                            <tbody>
                                @foreach($policies as $index => [$code, $label, $parent, $evidence])
                                    @php($policyKey = str_replace(['-', '.'], '_', $code))
                                    <tr data-policy-row data-parent="{{ $parent }}">
                                        <td>
                                            <label for="policy-{{ $index }}" class="font-weight-normal mb-1">{{ __($label) }}</label>
                                            <small class="d-block text-muted">{{ $code }}</small>
                                        </td>
                                        <td>{{ __('Equity') }}</td>
                                        <td>
                                            <select id="policy-{{ $index }}" name="policies[{{ $policyKey }}]" class="form-control" data-policy-code="{{ $code }}" aria-describedby="policy-hint-{{ $index }}">
                                                <option value="">{{ __('Unknown') }}</option>
                                                <option value="Yes">{{ __('Yes') }}</option>
                                                <option value="No">{{ __('No') }}</option>
                                            </select>
                                            <small id="policy-hint-{{ $index }}" class="d-block text-muted mt-1" data-policy-hint></small>
                                            <div data-evidence-fields hidden>
                                                <label for="evidence-{{ $index }}" class="font-weight-normal mt-2 mb-1">{{ __('Evidence') }}</label>
                                                <input type="file" id="evidence-{{ $index }}" name="evidence[{{ $policyKey }}]" class="form-control-file" aria-describedby="evidence-hint-{{ $index }}" disabled>
                                                <small id="evidence-hint-{{ $index }}" class="d-block text-muted mt-1">{{ __($evidence) }}. {{ __('The document must support the selected answer.') }}</small>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer">
            <button type="submit" name="save_mode" value="draft" formnovalidate class="btn btn-info mr-1">{{ __('Save Draft') }}</button>
            <button type="submit" name="save_mode" value="submitted" class="btn btn-info">{{ __('Submit for Approval') }}</button>
        </div>
    </form>
</div>
@stop
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var form = document.getElementById('indicator-data-form');
        var input = function (id) { return document.getElementById(id); };
        var number = function (id) { return input(id).value === '' ? NaN : Number(input(id).value); };
        function calculate() {
            var women = number('w4'), total = number('t4');
            var leaders = number('w4a'), positions = number('t4a');
            input('w4').setCustomValidity(women > total ? @json(__('Women employees cannot exceed total employees.')) : '');
            input('w4a').setCustomValidity(leaders > positions || leaders > women ? @json(__('Women leaders cannot exceed total leaders or women employees.')) : '');
            input('t4a').setCustomValidity(positions > total ? @json(__('Leadership positions cannot exceed total employees.')) : '');
        }
        function syncPolicies() {
            var selects = Array.from(form.querySelectorAll('[data-policy-code]'));
            form.querySelectorAll('[data-policy-row]').forEach(function (row) {
                var parent = row.dataset.parent;
                var select = row.querySelector('select');
                var evidence = row.querySelector('[data-evidence-fields]');
                var file = row.querySelector('input[type=file]');
                var disabled = false;
                if (parent) {
                    var parentSelect = selects.find(function (candidate) { return candidate.dataset.policyCode === parent; });
                    disabled = parentSelect.value !== 'Yes';
                    select.disabled = disabled;
                    if (disabled) select.value = '';
                    row.querySelector('[data-policy-hint]').textContent = parentSelect.value === 'No'
                        ? @json(__('Not applicable'))
                        : disabled ? @json(__('Select Yes for the parent indicator first:')) + ' ' + parent
                        : @json(__('Applies when')) + ' ' + parent + ' = Yes';
                }
                var showEvidence = !disabled && select.value === 'Yes';
                evidence.hidden = !showEvidence;
                file.disabled = !showEvidence;
                if (!showEvidence) file.value = '';
            });
        }
        form.querySelectorAll('input[type=number]').forEach(function (element) { element.addEventListener('input', calculate); });
        form.querySelectorAll('[data-policy-code]').forEach(function (element) { element.addEventListener('change', syncPolicies); });
        input('reporting-year').addEventListener('change', function () {
            input('indicator-back').href = @json(action('Cwis\CwisGeneratorDataController@index')) + '?year=' + encodeURIComponent(this.value);
        });
        form.addEventListener('submit', function (event) {
            var isDraft = event.submitter && event.submitter.value === 'draft';
            if (!isDraft) {
                calculate();
                if (!form.reportValidity()) {
                    event.preventDefault();
                    return;
                }
            }
        });
        calculate();
        syncPolicies();
    });
</script>
@endpush
