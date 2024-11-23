@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($sanitationSystemType, ['method' => 'PATCH', 'action' => ['BuildingInfo\SanitationSystemTypeController@update', $sanitationSystemType->id], 'class' => 'form-horizontal']) !!}
		@include('building-info/sanitation-system-types.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.box -->
@stop