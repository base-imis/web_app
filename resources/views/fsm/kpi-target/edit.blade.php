@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($kpi, ['method' => 'PATCH', 'action' => ['Fsm\KpiTargetController@update', $kpi], 'class' => 'form-horizontal']) !!}
		@include('fsm/kpi-target.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop