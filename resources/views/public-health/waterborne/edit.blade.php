@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($Waterborne, ['method' => 'PATCH', 'action' => ['PublicHealth\YearlyWaterborneController@update', $Waterborne], 'class' => 'form-horizontal']) !!}
		@include('public-health/waterborne.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</><!-- /.card -->
@stop
