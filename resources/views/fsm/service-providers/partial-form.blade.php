<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
	<div class="form-group row required">
		{!! Form::label('company_name','Company Name',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('company_name',null,['class' => 'form-control', 'placeholder' => 'Company Name']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('email','Email',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('email',null,['class' => 'form-control', 'placeholder' => 'Email']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('ward','Ward Number',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::select('ward', $wards, null, ['class' => 'form-control', 'placeholder' => 'Ward Number']) !!}
		</div>
	</div>
	<div class="form-group row required">
		{!! Form::label('company_location','Address',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('company_location',null,['class' => 'form-control', 'placeholder' => 'Address']) !!}
		</div>
	</div>

	<div class="form-group row required">
		{!! Form::label('contact_person','Contact Person Name',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('contact_person',null,['class' => 'form-control', 'placeholder' => 'Contact Person Name']) !!}
		</div>
	</div>
	<div class="form-group row required">
        {!! Form::label('contact_gender','Contact Person Gender',array('class'=>'col-sm-3 control-label')) !!}
        <div class="col-sm-3">
        {!! Form::select('contact_gender',array("Male"=>"Male","Female"=>"Female","Others"=>"Others"), null,['class' => 'form-control', 'placeholder' => 'Contact Person Gender']) !!}
        </div>
    </div>
	<div class="form-group row required">
		{!! Form::label('contact_number','Contact Person Number',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::text('contact_number',null,['class' => 'form-control', 'placeholder' => 'Contact Person Number']) !!}
		</div>
	</div>
	<div class="form-group row required">
        {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', $serviceProviderStatus, null, ['class' => 'form-control chosen-select', 'placeholder' => 'Status']) !!}
        </div>
    </div>

	@if(!$serviceProvider)
	<div class="form-group row">
		{!! Form::label('create_user','Create User?',['class' => 'col-sm-3 control-label']) !!}
		<div class="col-sm-3">
			{!! Form::checkbox('create_user',null,['class' => 'form-control create_user','id'=>'create_user', 'placeholder' => 'Contact Number']) !!}
		</div>
	</div>
	<div id="user-password">
		<div class="form-group row">
			<label for="password" class="col-sm-3 col-form-label text-md-end">{{ __('Password') }}</label>

			<div class="col-sm-3">
				<input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password"  autocomplete="new-password" placeholder="Password">

				@error('password')
					<span class="invalid-feedback" role="alert">
						<strong>{{ $message }}</strong>
					</span>
				@enderror
			</div>
		</div>

		<div class="form-group row">
			<label for="password-confirm" class="col-sm-3 col-form-label text-md-end">{{ __('Confirm Password') }}</label>

			<div class="col-sm-3">
				<input id="password-confirm" type="password" class="form-control" name="password_confirmation"  autocomplete="new-password" placeholder="Confirm Password">
			</div>
		</div>
	</div>
	@endif
</div><!-- /.box-body -->
<div class="card-footer">
	<a href="{{ action('Fsm\ServiceProviderController@index') }}" class="btn btn-info">Back to List</a>
	{!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->

