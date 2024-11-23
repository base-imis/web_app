<div class="card-body">
    <div class="form-group row required">
        {!! Form::label('type','Name',['class' => 'col-sm-3 control-label']) !!}
        <div class="col-sm-3">
            {!! Form::text('type',null,['class' => 'form-control', 'placeholder' => 'Name']) !!}
        </div>
    </div>
    
</div><!-- /.box-body -->
<div class="card-footer">
    <a href="{{ action('BuildingInfo\SanitationSystemTypeController@index') }}" class="btn btn-info">Back to List</a>
    {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
</div><!-- /.box-footer -->