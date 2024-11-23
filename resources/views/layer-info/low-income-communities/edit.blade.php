@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($lic, ['method' => 'PATCH', 'action' => ['LayerInfo\LowIncomeCommunityController@update', $lic->id], 'class' => 'form-horizontal']) !!}
		@include('layer-info/low-income-communities.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</><!-- /.card -->
@stop