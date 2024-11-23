<div @if(sizeof($group["charts"]) == 2) class="chart-div col-lg-12 col-md-12 col-sm-12 " @endif
@if(sizeof($group["charts"]) == 3) class="chart-div col-lg-12 col-md-12 col-sm-12" @endif
     @if(sizeof($group["charts"]) < 2) class="chart-div col-lg-6 col-md-6 col-sm-12 " @endif
     @if(sizeof($group["charts"]) == 8) class=" col-lg-12 col-md-12 col-sm-12 "  @endif>
    <div class="card card-default" style="background-color: #EFF2F4 !important">
        <div class="card-header with-border">
            {{--                <h3 class="card-title">{{$chart_title}}</h3>--}}
            <div class="card-tools pull-right">
                @foreach($group["data"][0]->labelsArr as $label)
                    <span class="label {{$label}}-label category">{{$label}}</span>
                @endforeach

                {{-- <button class="btn btn-card-tool" title="Download as SVG" id="{{$group_id}}_download_btn" onclick="downloadAsSvg(document.getElementsByClassName('{{$group_id}}-card-body')[0])"><i class="fa fa-download"></i></button> --}}
                {{-- <button class="btn btn-card-tool" title="Trends" id="{{$group_id}}_trend_btn"
                        data-target="#{{$group_id}}_trend_modal"><i
                            class="fa fa-arrow-trend-up"></i></button> --}}

            </div><!-- /.card-tools -->


        </div><!-- /.card-header -->
        <div class="box-body box-container {{$group_id}}-box-body">
            <div class="row" style="width: 100%;display: flex;align-items: center;flex-wrap: wrap;justify-content:center;">
                @if(strtolower($group["group_type"])=="percent")
                    @for($i=0;$i<sizeof($group["charts"]);$i++)

                        <div @if(sizeof($group["charts"])==3)class="col-lg-4 col-md-6 col-sm-12  {{$group["data"][0]->labels}}"
                             @endif
                             @if(sizeof($group["charts"])==4)class="col-lg-4 col-md-6 col-sm-12  {{$group["data"][0]->labels}}"
                             @endif
                             @if(sizeof($group["charts"])==5)class="col-lg-4 col-md-6 col-sm-12  {{$group["data"][0]->labels}}"
                             @endif
                             @if(sizeof($group["charts"])==2)class="col-lg-6 col-md-6 col-sm-12  {{$group["data"][0]->labels}}"
                             @endif
                             @if(sizeof($group["charts"])==1)class="col-lg-12 col-md-12 col-sm-12  {{$group["data"][0]->labels}}"

                             @endif

                             data-category="{{$group["data"][$i]->labels}}" data-section = "">

                            @include("cwis.cwis-dashboard.charts.percent-chart",
                            [
                                "chart"=>$group["charts"][$i],
                                "description"=>$group["data"][$i]->heading,
                                "primary_color" => $primary_colors[$i],
                            ]
                            )
                        </div>
                    @endfor
                @endif
                @if(strtolower($group["group_type"])=="bar")
                    <div class="col-lg-12 col-md-12 col-sm-12 grid-item grid-isotope {{$group["data"][0]->labels}}"
                         data-category="{{$group["data"][0]->labels}}" style="width: 100%">
                        @include("cwis.cwis-dashboard.charts.bar-chart",[
                            "chart" => $group["charts"][0]
                        ])
                    </div>
                @endif
                @if(strtolower($group["group_type"])=="text")
                    <div class="col-lg-12 col-md-12 col-sm-12 grid-item grid-isotope {{$group["data"][0]->labels}}"
                         data-category="{{$group["data"][0]->labels}}" style="width: 100%">
                        @include("cwis.cwis-dashboard.charts.text-chart",
                                            [
                                                "chart_title"=>$group["data"][0]->parameter_title,
                                                    "description"=>$group["data"][0]->assmntmtrc_dtpnt,
                                                    "value"=>$group["data"][0]->data_value=="NA"?"N/A":$group["data"][0]->data_value,
                                                    "upper_title"=>"EVERY",
                                                    "lower_title"=>"YEARS",
                                                    "labels"=>$group["data"][0]->labelsArr])
                    </div>
                @endif
                @if(strtolower($group["group_type"])=="ratio")
                    <div class="col-lg-12 col-md-12 col-sm-12 grid-item grid-isotope {{$group["data"][0]->labels}}"
                         data-category="{{$group["data"][0]->labels}}" style="width: 100%">
                        @include("cwis.cwis-dashboard.charts.ratio-chart",
                                            [
                                                "chart_title"=>$group["data"][0]->parameter_title,
                                                    "description"=>$group["data"][0]->assmntmtrc_dtpnt,
                                                    "value"=>$group["data"][0]->data_value,
                                                    "upper_title"=>"",
                                                    "lower_title"=>"",
                                                    "labels"=>$group["data"][0]->labelsArr])
                    </div>
                @endif
            </div>
        </div><!-- /.card-body -->
        @include('cwis.cwis-dashboard.modals.modal',["modal_id"=>$group_id,"charts"=>$group["data"]])
        {{--<div class="overlay">
            <i class="fa fa-circle-exclamation fa-beat-fade"></i>
        </div>--}}
    </div><!-- /.card -->
</div>

<script>

    function downloadAsSvg(element){
        domtoimage.toSvg(element)
            .then(function (dataUrl) {
                var link = document.createElement('a');
                link.setAttribute("href", dataUrl);
                link.setAttribute("download", 'chart');
                link.click();
            });
    }
</script>
