<!-- Last Modified Date: 11-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->
<div class="card-body">
<div class="form-group row ">
		{!! Form::label('code','Code',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('code',null,['class' => 'form-control', 'disabled' => 'true', 'placeholder' => 'Code']) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('road_code','Road Code',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('road_code',null,['class' => 'form-control', 'disabled' => 'true', 'placeholder' => 'Road Code']) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('treatment_plant_id','Treatment Plant',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('treatment_plant_id',$treatdrp,null,['class' => 'form-control', 'placeholder' => 'Treatment Plant']) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('length','Length (m)',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('length',null,['class' => 'form-control', 'placeholder' => 'Length (in meter)']) !!}
		</div>
	</div>
	<div class="form-group row">
		{!! Form::label('location','Location',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('location',null,['class' => 'form-control', 'placeholder' => 'Location']) !!}
		</div>
	</div>
    <div class="form-group row ">
		{!! Form::label('diameter','Diameter (mm)',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('diameter',null,['class' => 'form-control', 'placeholder' => 'Diameter (mm)']) !!}
		</div>
	</div>
</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\SewerLineController@index') }}" class="btn btn-info">Back to List</a>
	{!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
