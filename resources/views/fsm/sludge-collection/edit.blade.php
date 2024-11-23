<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	<div class="card-header with-border">
		<h3 class="card-title">Application ID: {{ $sludgeCollection->application_id }}</h3>
	</div><!-- /.card-header -->
	{!! Form::model($sludgeCollection, ['method' => 'PATCH', 'action' => ['Fsm\SludgeCollectionController@update', $sludgeCollection->id], 'class' => 'form-horizontal']) !!}
		@include('fsm/sludge-collection.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}

</div><!-- /.card -->
@stop

@push('scripts')


@push('scripts')
    <script>
   
$(document).ready(function() {
    $('#sludge_collection_date').daterangepicker({
                singleDatePicker: true,
                autoUpdateInput: false,
                showDropdowns:true,
                autoApply:true,
                drops:"auto"
            });
            $('#sludge_collection_date').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('MM/DD/YYYY'));
            });

            $('#sludge_collection_date').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
            });
           
   
    })
    </script>
@endpush