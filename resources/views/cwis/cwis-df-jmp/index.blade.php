@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-white">
			
			<form method="GET" action="{{url('cwis/cwis-df-jmp/export-jmp-csv')}}"  class="form-inline ml-auto">
			@can('Export CWIS JMP to Excel')
			<button class="btn btn-info">Export to Excel</button>
			@endcan	
				<span><a href="{{ action('Cwis\CwisJmpController@createIndex') }}" class="btn btn-info ml-2">Add survey</a></span>
						<div class="form-group float-right text-right ml-auto">
							<label for="year_select">Year</label>
							<select class="form-control" id="year_select" name="year_select">
							
								@foreach($pickyear as $key=>$unique) 
									<option value= "{!! $unique !!}"> {!! $unique !!} </option>
								@endforeach
							</select>
						</div>
					</form>
		
	</div><!-- /.card-header -->
		
	@include('errors.list')
	{!! Form::open(['url' => 'cwis-df-jmp', 'class' => 'form-horizontal']) !!}
		@include('cwis/cwis-df-jmp.partial-form', ['submitButtomText' => 'Save'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop

@push('scripts')
<script>
    $('[name="year_select"]').change(function(e) {
        // e.preventDefault();
        
		var year = $('#year_select').val();
		localStorage.setItem('year', year);
		const url = '<?php echo url('');?>'+`/cwis/cwis-df-jmp/${year}`;	
		window.location.replace(url);
		
       
    })
</script>
<script>
	$(document).ready(function() {
		year = localStorage.getItem('year');
		$("#year_select").val(year);
	})
</script>
@endpush
