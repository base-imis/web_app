<!-- Last Modified Date: 07-05-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($waterSamples, ['method' => 'PATCH', 'action' => ['PublicHealth\WaterSamplesController@update', $waterSamples->id], 'class' => 'form-horizontal']) !!}
		@include('public-health/water-samples.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop
@push('scripts')
    <script>
$(document).ready(function() {
    $('#sample_date').daterangepicker({
        maxDate: moment(),
        singleDatePicker: true,
        autoUpdateInput: false,
        showDropdowns:true,
        autoApply:true,
        drops:"auto",
        locale: {
                format: 'YYYY-MM-DD'
            }
    });
    $('#sample_date').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    $('#sample_date').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    $('#sample_date').focus(function() {
        $(this).blur();
    });
});
</script>
@endpush