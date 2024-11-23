@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
<div class="card card-info">
	<div class="card-header bg-white">
		
	<div class="form-inline">
		<a href="{{ action('Cwis\CwisJmpController@index') }}" class="btn btn-info float-left">Back to List</a>
		<div class="form-group float-right text-right ml-auto">
			<label for="year_new">Year</label>
			<select class="form-control" id="year_new" name="year_new">
				@foreach($newsurveyear as $key=>$newyear) 
					<option value= "{!! $newyear->newyear !!}"> {!! $newyear->newyear !!} </option>
				@endforeach
			</select>
		</div>
	</div>

	</div><!-- /.card-header -->
	@include('errors.list')
	{!! Form::open(['action'=> 'Cwis\CwisJmpController@createStore', 'method' => 'POST', 'class' => 'form-horizontal']) !!}
		@include('cwis/cwis-df-jmp._createForm', ['submitButtomText' => 'Save'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop