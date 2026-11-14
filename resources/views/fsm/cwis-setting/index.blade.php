@extends('layouts.dashboard')
@push('style')
    <style type="text/css">
        .dataTables_filter {
            display: none;
        }
        #editForm [hidden] {
            display: none !important;
        }
    </style>
@endpush
@section('title', $page_title)
@section('content')
    @php($editingSettings = $errors->any() && auth()->user()->can('Edit CWIS'))
    @include('layouts.components.error-list')
    @include('layouts.components.success-alert')
    @include('layouts.components.error-alert')
    <div class="card">

        {!! Form::open([
                'method' => 'POST',
                'action' => ['Fsm\CwisSettingController@store'],
                'class' => 'form-horizontal',
                'id' => 'editForm',
            ]) !!}
        <div class="card-body">
            <div class="form-group row">
                <div class="col-sm-3" style="color:grey">
                    <small id="cwis-settings-updated"><i class="fa-regular fa-clock"></i> {{__('Last Updated')}}: {{ $updated ?? '' }}</small>
                </div>
            </div>
            
            <div class="form-group row">
                {!! Form::label('active_country', __('Country'), ['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::select('active_country', array_map('__', $countries), old('active_country', $data['active_country'] ?? 'ALL'), ['class' => 'form-control', 'required' => true, 'disabled' => !$editingSettings]) !!}
                </div>
            </div>
            @foreach ($parameters as $key => $parameter)
                <div class="form-group row">
                    {!! Form::label(
                        $key,
                        __($parameter['label']),
                        ['class' => 'col-sm-3 control-label' ],
                    ) !!}
                    <div class="col-sm-3">
                        {!! Form::number($key, old($key, $data[$key] ?? null), [
                            'class' => 'form-control',
                            'required' => true,
                            'min' => $parameter['min'],
                            'max' => $parameter['max'],
                            'step' => 'any',
                            'readonly' => !$editingSettings,
                        ]) !!}
                    </div>
                </div>
            @endforeach
            @foreach($decisions as $key => $decision)
                <div class="form-group row">
                    <label for="{{ $key }}" class="col-sm-3 control-label">{{ __($decision['label']) }}</label>
                    <div class="col-sm-3"><select id="{{ $key }}" name="{{ $key }}" class="form-control" @if(!$editingSettings) disabled @endif>
                        @foreach($decision['options'] as $value => $label)<option value="{{ $value }}" @if(old($key,$data[$key] ?? ($decision['default'] ?? 'pending')) === $value) selected @endif>{{ __($label) }}</option>@endforeach
                    </select></div>
                </div>
            @endforeach
        </div><!-- /.box-body -->
        <div class="card-footer">
             @can('Edit CWIS')
            <button type="button" id="editButton" class="btn btn-info" @if($editingSettings) hidden @endif>{{__('Edit')}}</button>
            <button type="submit" id="saveButton" class="btn btn-info" @if(!$editingSettings) hidden @endif>{{__('Save')}}</button>
            @endcan
        </div><!-- /.box-footer -->
    {!! Form::close() !!}
    </div>
@stop
@push('scripts')
<script>
    (function () {
        function initializeSettings() {
            var form = document.getElementById('editForm');
            if (!form) return;
            var editButton = form.querySelector('#editButton');
            var saveButton = form.querySelector('#saveButton');
            var editing = @json($editingSettings);

            function setEditing(value) {
                editing = value;
                form.querySelectorAll('input:not([type="hidden"])').forEach(function (input) {
                    input.readOnly = !value;
                });
                form.querySelectorAll('select').forEach(function (select) {
                    select.disabled = !value;
                });
                if (editButton) editButton.hidden = value;
                if (saveButton) saveButton.hidden = !value;
            }

            setEditing(editing);
            if (editButton) editButton.addEventListener('click', function () {
                setEditing(true);
                var updated = form.querySelector('#cwis-settings-updated');
                if (updated) updated.hidden = true;
            });
            form.addEventListener('submit', function (event) {
                if (!editing) event.preventDefault();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeSettings);
        } else {
            initializeSettings();
        }
    })();
</script>

@endpush
