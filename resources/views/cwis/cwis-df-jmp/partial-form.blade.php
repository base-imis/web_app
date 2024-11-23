<div class="panel-group m-3" id="accordion" role="tablist" aria-multiselectable="true">
    @foreach ($subCategory_titles as $key=>$subCategory_title)
            <h3 class="panell-title" text-align="center">
                            <centre><a>{!! $subCategory_title !!}</a></centre>
            </h3>
        <div class="panel panel-default">
        @for($i=0; $i<$param_listcount ; $i++)
            @foreach ($param_titles[$i] as $param_title)
                <div class="panel-heading" role="tab" id="headingOne">
                    <h4 class="panel-title">
                        <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseparam{{$i}}" aria-expanded="true" aria-controls="collapseparam{{$i}}">
                        <span>{!! $param_title !!}</span></h6>
                        </a>
                    </h4>
                </div>
                <div id="collapseparam{{$i}}" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingOne">
                    <div class="panel-body">
                        <table class="table table-bordered" width='100%'>
                            <tr>
                                <th colspan=4 width='50%' style="margin-left:auto; margin-right:auto;">Parameter: {!! $param_title !!}</th>
                            </tr>
                            <tr class="" width='100%'>
                                <th width='65%'>Assessment Metric or Data Point	Unit</th>
                                <th width='15%'>Unit</th>
                                <th width='20%'>Data</th>
                            </tr>
                            @foreach ($param_details[$i] as $param_detail)
                            <tr class="" width='100%'>
                                <td width='65%'>{{ $param_detail->assmntmtrc_dtpnt }}</td>
                                <td width='15%'>{{ $param_detail->unit }}</td>
                                <td width='20%'><input class="form-control" placeholder="{{ $param_detail->data_type_phldr }}" name="data_value[]" type="{{ $param_detail->data_type }}" value="{{ $param_detail->data_value }}" {{ $param_detail->data_type_req }}>
                                <!-- <td width='20%'>{!! Form::text('data_value[]',$param_detail->data_value,['class' => 'form-control', 'placeholder' => 'Enter data here ..']) !!}</td> -->
                            </tr>
                            @endforeach
                            </table>
                    </div>
                </div>
            @endforeach
            <br/>
        @endfor
        </div>
    @endforeach
  <div class="footer">
        {!! Form::submit('Save', ['class' => 'btn btn-info']) !!}
    </div>
</div>





