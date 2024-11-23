<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
@extends('layouts.layers')
@section('title', $page_title)
@section('content')
@include('layouts.components.error-list')
@include('layouts.components.success-alert')
@include('layouts.components.error-alert')
<div class="card card-info">
	{!! Form::model($treatmentPlant, ['method' => 'PATCH', 'action' => ['Fsm\TreatmentPlantController@update', $treatmentPlant->id], 'class' => 'form-horizontal']) !!}
		@include('fsm/treatment-plants.partial-form', ['submitButtomText' => 'Update'])
	{!! Form::close() !!}
</div><!-- /.card -->
@stop