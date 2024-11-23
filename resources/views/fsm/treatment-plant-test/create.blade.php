<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'fsm/treatment-plant-test', 'class' => 'form-horizontal']) !!}
		@include('fsm/treatment-plant-test.partial-form', ['submitButtomText' => 'Save'])
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
@push('scripts')
    <script>
$(document).ready(function() {
    $('#date').daterangepicker({
        maxDate: moment(),
        singleDatePicker: true,
        autoUpdateInput: false,
        showDropdowns:true,
        autoApply:true,
        drops:"auto"
    });
    $('#date').on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('MM/DD/YYYY'));
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