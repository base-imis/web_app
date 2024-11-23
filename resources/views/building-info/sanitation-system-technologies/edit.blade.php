@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($sanitationSystemTechnology, ['method' => 'PATCH', 'action' => ['BuildingInfo\SanitationSystemTechnologyController@update', $sanitationSystemTechnology->id], 'class' => 'form-horizontal']) !!}
		@include('building-info/sanitation-system-technologies.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.box -->
@stop