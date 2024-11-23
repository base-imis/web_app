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
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card">

    <div class="card-body">
    {!! Form::model(['method' => 'PATCH', 'action' => ['Fsm\TreatmentplantPerformanceTestController@update'], 'class' => 'form-horizontal' , 'id' => 'editForm']) !!}
    <div class="form-group row ">
        {!! Form::label('tss_standard','TSS Standard (mg/l)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
        {!! Form::number('tss_standard', isset($data[0]) ? $data[0]->tss_standard : 0, ['class' => 'form-control', 'placeholder' => 'TSS Standard']) !!}

        </div>
    </div>

    <div class="form-group row ">
        {!! Form::label('ecoli_standard','ECOLI Standard (CFU/100 mL)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('ecoli_standard',isset($data[0]) ? $data[0]->ecoli_standard : 0,['class' => 'form-control', 'placeholder' => 'ECOLI Standard']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('ph_min','pH Minimum',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('ph_min',isset($data[0]) ? $data[0]->ph_min : 0,['class' => 'form-control', 'placeholder' => 'pH Minimum']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('ph_max','pH Maximum',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('ph_max', isset($data[0]) ? $data[0]->ph_max : 0,['class' => 'form-control', 'placeholder' => 'pH Maximum']) !!}
        </div>
    </div>
    <div class="form-group row ">
        {!! Form::label('bod_standard','BOD Standard (mg/l)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::number('bod_standard',isset($data[0]) ? $data[0]->bod_standard : 0,['class' => 'form-control', 'placeholder' => 'BOD Standard']) !!}
        </div>
    </div>

</div><!-- /.box-body -->
<div class="card-footer">
        <span id="editButton" class="btn btn-info">Edit</span>
        <button type="submit" id="saveButton" class="btn btn-info" style="display: none;">Save</button>
    </div><!-- /.box-footer -->
  </div>
  {!! Form::close() !!}
</div>


</div><!-- /.box -->
@stop
@push('scripts')
<script>
 $(document).ready(function () {
    // Function to toggle readonly attribute
    function toggleReadOnly(readonly) {
        $('input').prop('readonly', readonly);
    }

    // Initially set form fields as read-only
    toggleReadOnly(true);

    // Edit button click event
    $('#editButton').click(function () {
        $('input').removeAttr('readonly');
        $('#editButton').hide();
        $('#saveButton').show();
    });

    // Check for errors and update buttons accordingly
    var hasErrors = $('.alert-danger').length > 0;

    if (hasErrors) {
        $('input').removeAttr('readonly');
        $('#editButton').hide();
        $('#saveButton').show();
    } else {
        $('#saveButton').hide();
        $('#editButton').show();
    }
});


</script>

@endpush
