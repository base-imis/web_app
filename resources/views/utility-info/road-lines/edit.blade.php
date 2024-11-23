@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($roadline, ['method' => 'PATCH', 'action' => ['UtilityInfo\RoadlineController@update', $roadline->code], 'class' => 'form-horizontal']) !!}
		@include('utility-info/road-lines.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@endsection
