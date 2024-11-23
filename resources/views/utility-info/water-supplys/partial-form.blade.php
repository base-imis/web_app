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
    <div class="form-group row">
		{!! Form::label('diameter','Diameter (mm)',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('diameter',null,['class' => 'form-control', 'placeholder' => 'Diameter (mm)']) !!}
		</div>
	</div>
	<div class="form-group row">
		{!! Form::label('length','Length (m)',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('length',null,['class' => 'form-control', 'placeholder' => 'Length (m)']) !!}
		</div>
	</div>
        <div class="form-group row">
		{!! Form::label('project_name','Project Name',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('project_name',null,['class' => 'form-control', 'placeholder' => 'Project Name']) !!}
		</div>
	</div>
        <div class="form-group row ">
		{!! Form::label('type','Type',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('type', ['Main' => 'Main', 'Secondary' => 'Secondary'], null, ['class' => 'form-control', 'placeholder' => 'Type']);!!}
		</div>
	</div>
        <div class="form-group row ">
		{!! Form::label('material_type','Material Type',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('material_type', ['HDPE' => 'HDPE', 'GI' => 'GI'], null, ['class' => 'form-control', 'placeholder' => 'Material Type']);!!}
		</div>
	</div>
</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\WaterSupplysController@index') }}" class="btn btn-info">Back to List</a>
	{!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
