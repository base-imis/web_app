<div class="card-body">
	<div class="form-group row required">
		{!! Form::label('name','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('name',null,['class' => 'form-control', 'placeholder' => 'Date Added']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('week','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('week',null,['class' => 'form-control', 'placeholder' => 'Week']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('registration_number','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('registration_number',null,['class' => 'form-control', 'placeholder' => 'Registration Number']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('opd_eme_ipd','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('opd_eme_ipd',null,['class' => 'form-control', 'placeholder' => 'OPD/EME/IPD']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('patient_name','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('patient_name',null,['class' => 'form-control', 'placeholder' => 'Patient Name']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('age','Age',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('age',null,['class' => 'form-control', 'placeholder' => 'Age']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('sex','Sex',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('sex',null,['class' => 'form-control', 'placeholder' => 'Sex']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('contact_number','Contact Number',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('contact_number',null,['class' => 'form-control', 'placeholder' => 'Contact Number']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('ward','Ward',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('ward',null,['class' => 'form-control', 'placeholder' => 'Ward']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('name','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('name',null,['class' => 'form-control', 'placeholder' => 'Date Added']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('name','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('name',null,['class' => 'form-control', 'placeholder' => 'Date Added']) !!}
		</div>
	</div>
    <div class="form-group row required">
		{!! Form::label('name','Date Added',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('name',null,['class' => 'form-control', 'placeholder' => 'Date Added']) !!}
		</div>
	</div>
    
        
</div><!-- /.card-body -->
<div class="card-footer">
	<a href="{{ action('UtilityInfo\RoadlineController@index') }}" class="btn btn-info">Back to List</a>
	{!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->