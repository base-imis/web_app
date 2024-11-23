@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::open(['url' => 'fsm/hotspots', 'class' => 'form-horizontal']) !!}
		@include('fsm/hotspots.partial-form', ['submitButtomText' => 'Save'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop