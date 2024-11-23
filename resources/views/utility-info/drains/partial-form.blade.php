<!-- Last Modified Date: 11-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)  (© ISPL, 2024) -->

<div class="card-body">
<div class="form-group row ">
		{!! Form::label('code','Code',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('code',null,['class' => 'form-control', 'disabled' => 'true', 'placeholder' => 'code']) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('road_code','Road Code',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('road_code',null,['class' => 'form-control', 'disabled' => 'true', 'placeholder' => 'Road Code']) !!}
		</div>
	</div>
	<div class="form-group row">
		{!! Form::label('cover_type','Cover Type',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('cover_type', $cover_type, null, ['class' => 'form-control', 'placeholder' => 'Cover Type']);!!}
		</div>
	</div>
	<div class="form-group row">
		{!! Form::label('surface_type','Surface Type',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('surface_type', $surface_type, null, ['class' => 'form-control', 'placeholder' => 'Surface Type']);!!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('size','Width (mm)',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('size',null,['class' => 'form-control', 'placeholder' => 'width(mm)']) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('length','Length (m)',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('length',null,['class' => 'form-control', 'placeholder' => 'Length (m)']) !!}
		</div>
	</div>
	<div class="form-group row ">
		{!! Form::label('treatment_plant_id','Treatment Plant',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('treatment_plant_id', $treatmentPlants, null, ['class' => 'form-control', 'placeholder' => 'Treatment Plant']);!!}
		</div>
	</div>
	
	
</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\DrainController@index') }}" class="btn btn-info">Back to List</a>
	{!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
