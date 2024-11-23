<!-- Last Modified Date: 07-05-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->

@extends('layouts.dashboard')
@section('title', 'Water Samples Details')
@section('content')
<div class="card card-info">
    
    <div class="card-header bg-transparent ">
        <a href="{{ action('PublicHealth\WaterSamplesController@index') }}" class="btn btn-info">Back to List</a>
    </div>
	
	<div class="form-horizontal">
		<div class="card-body">
			<div class="form-group row">
				{!! Form::label('sample_date','Sample Date',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSamples->sample_date,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('sample_location','Sample Location',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
					{!! Form::label(null,$waterSamples->sample_location,['class' => 'form-control']) !!}
				</div>
			</div>
			<div class="form-group row">
				{!! Form::label('water_coliform_test_result','Water Coliform Test Result',['class' => 'col-sm-3 control-label']) !!}
				<div class="col-sm-3">
				{!! Form::label(null,
					($waterSamples->water_coliform_test_result == 'positive') ? 'Positive' :
					(($waterSamples->water_coliform_test_result == 'negative') ? 'Negative' : ''),
					['class' => 'form-control']
				) !!}
				</div>
			</div>
			
		</div><!-- /.box-body -->
	</div>
<div class="card-footer">
    
</div>
</div><!-- /.box -->
@stop

