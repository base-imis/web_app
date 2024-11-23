<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('sub_type','Name',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('sub_type',null,['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
    </div>
    
    <div class="form-group row required" id="treatment_plant">
                {!! Form::label('sanitation_type_id','Sanitation System Type',['class' => 'col-sm-3 control-label']) !!}
                <div class="col-sm-3">
                    {!! Form::select('sanitation_type_id', $sanitationSystemTypes, null, ['class' => 'form-control ', 'placeholder' => '--- Choose Sanitation System ---']) !!}
                </div>
    </div>
</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('BuildingInfo\SanitationSystemTechnologyController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->