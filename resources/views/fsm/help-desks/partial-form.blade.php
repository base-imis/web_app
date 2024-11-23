<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('name','Help Desk Name',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('name',null,['class' => 'form-control', 'placeholder' => 'Help Desk Name']) !!}
        </div>
    </div>
    
    <div class="form-group row required">
        {!! Form::label('description',null,['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('description',null,['class' => 'form-control', 'placeholder' => 'Description']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('contact_number','Contact Number',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('contact_number',null,['class' => 'form-control', 'placeholder' => 'Contact Number']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('email','Email Address',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('email',null,['class' => 'form-control', 'placeholder' => 'Email Address']) !!}
        </div>
    </div>
    @if(Auth::user()->service_provider_id)
        <div class="form-group row required" id="service_provider">
        {!! Form::label('service_provider_id','Service Provider',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::label(null,$serviceProviders[Auth::user()->service_provider_id],['class' => 'form-control']) !!}
            {!! Form::text('service_provider_id', Auth::user()->service_provider_id, ['hidden' => 'true']) !!}
        </div>
    </div>

    {{-- @else
    <div class="form-group row" id="service_provider">
        {!! Form::label('service_provider_id','Service Provider',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('service_provider_id', $serviceProviders, null, ['class' => 'form-control', 'placeholder' => 'Service Provider']) !!}
        </div>
    </div> --}}
    @endif
    @if(!$helpDesk)
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
    <a href="{{ action('Fsm\HelpDeskController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->