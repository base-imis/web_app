<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($treatmentPlantTest, ['method' => 'PATCH', 'action' => ['Fsm\TreatmentPlantTestController@update', $treatmentPlantTest->id], 'class' => 'form-horizontal']) !!}
		@include('fsm/treatment-plant-test.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@push('scripts')
    <script>
$(document).ready(function() {
    $('#date').daterangepicker({
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
    $('#date').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('YYYY-MM-DD'));
    });
    $('#date').on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
    });
    $('#date').focus(function() {
        $(this).blur();
    });
});
</script>
@endpush
@stop
