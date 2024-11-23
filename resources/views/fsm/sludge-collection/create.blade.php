<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'fsm/sludge-collection', 'class' => 'form-horizontal']) !!}
		@include('fsm/sludge-collection.partial-form', ['submitButtomText' => 'Save'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop
@push('scripts')
<script type="text/javascript">
  $(document).ready(function(){

	$('#sludge_collection_date').daterangepicker({
                minDate: moment(),
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
  
  });
  $('.date').focus(function(){
         $(this).blur();
     });
</script>
@endpush