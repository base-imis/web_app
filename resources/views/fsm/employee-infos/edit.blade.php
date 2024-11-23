<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')

<div class="card card-info">
	{!! Form::model($employeeInfos, ['method' => 'PATCH', 'action' => ['Fsm\EmployeeInfoController@update', $employeeInfos->id], 'class' => 'form-horizontal']) !!}
	@include('fsm/employee-infos.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div>
<!-- /.card -->
@stop
@push('scripts')
    <script>
   
$(document).ready(function() {
    $('#employment_start, #employment_end').daterangepicker({
                singleDatePicker: true,
                autoUpdateInput: false,
                showDropdowns:true,
                autoApply:true,
                drops:"auto"
            });
            $('#employment_start, #employment_end').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            });

            $('#employment_start, #employment_end').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
           
   
    })
    </script>
@endpush