<!-- Last Modified Date: 18-04-2024
Developed By: Innovative Solution Pvt. Ltd. (ISPL)   -->
<div class="card-body">
@if(Auth::user()->hasRole('Super Admin') || Auth::user()->hasRole('Municipality - IT Admin') && !Auth::user()->hasRole('Municipality - Executive') || Auth::user()->hasRole('Municipality - Sanitation Department'))
    <div class="form-group row required">
        {!! Form::label('service_provider_id','Service Provider',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('service_provider_id', $serviceProviders, null, ['class' => 'form-control chosen-select', 'placeholder' => 'Service Provider']) !!}
        </div>
    </div>
    @else
        <div class="form-group row required">
            {!! Form::label('service_provider_id','Service Provider',['class' => 'col-sm-3 control-label']) !!}
            <div class="col-sm-3">
                {!! Form::label($serviceProviders[Auth::user()->service_provider_id], $serviceProviders[Auth::user()->service_provider_id], ['class' => 'form-control']) !!}
                {!! Form::text('service_provider_id', Auth::user()->service_provider_id, ['hidden' => 'true']) !!}
            </div>
        </div>
    @endif
    <div class="form-group row required">
        {!! Form::label('license_plate_number','Vehicle License Plate Number ',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('license_plate_number',null,['class' => 'form-control', 'placeholder' => 'Vehicle License Plate Number ']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('capacity','Capacity (m³)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('capacity',null,['class' => 'form-control', 'placeholder' => 'Capacity (m³)']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('width','Width (m)',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('width',null,['class' => 'form-control', 'placeholder' => 'Width (m)']) !!}
        </div>
    </div>
   <div class="form-group row required">
        {!! Form::label('comply_with_maintainance_standards','Comply with Maintenance Standards',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('comply_with_maintainance_standards', $complyMaintainStandard, null, ['class' => 'form-control chosen-select', 'placeholder' => 'Comply with Maintenance Standards']) !!}
        </div>
    </div>
    <div class="form-group row required">
        {!! Form::label('status','Status',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::select('status', $status, null, ['class' => 'form-control chosen-select', 'placeholder' => 'Status']) !!}
        </div>
    </div>
    <div class="form-group row">
        {!! Form::label('description','Description',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::textarea('description',null,['class' => 'form-control', 'placeholder' => 'Description']) !!}
        </div>
    </div>
</div><!-- /.card-body -->
<div class="card-footer">
    <a href="{{ action('Fsm\VacutugTypeController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.card-footer -->
