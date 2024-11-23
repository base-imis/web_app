@extends('layouts.dashboard')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($info, ['method' => 'PATCH', 'action' => ['Fsm\TreatmentPlantEffectivenessController@update', $info->id], 'class' => 'form-horizontal']) !!}
		@include('fsm.treatment-plant-effectiveness.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop