<div @if($charts_in_box == 2 or $charts_in_box ==3) class="chart-div col-lg-12 col-md-12 col-sm-12" @endif
@if($charts_in_box == 1) class="chart-div col-lg-6 col-md-6 col-sm-12" @endif>
            <div class="row" style="width: 100%">
                @if($chart_type=="percentage")
                    @for($i=0;$i<sizeof($charts);$i++)

                        <div @if(sizeof($charts)==3)class="col-lg-4 col-md-6 col-sm-12  {{$charts[$i]->labels}}"
                             @endif
                             @if(sizeof($charts)==2)class="col-lg-6 col-md-6 col-sm-12  {{$charts[$i]->labels}}"
                             @endif
                             @if(sizeof($charts)==1)class="col-lg-12 col-md-12 col-sm-12  {{$charts[$i]->labels}}"
                             @endif
                             >
                            @include("cwis.pdf.cwis-mne.charts.single-valued-percentage",[
                                                    "source"=>$sources[$i],
                                                    "description"=>$charts[$i]->assmntmtrc_dtpnt
                                                    ])
                        </div>
                    @endfor
                @endif
                @if($chart_type=="bar")
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @include("cwis.pdf.cwis-mne.charts.horizontal-bar-chart",
                                            [
                                                "source"=>$sources[0],
                                                ])
                    </div>
                @endif
                @if($chart_type=="text")
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        @include("cwis.pdf.cwis-mne.charts.single-valued-chart",
                                            [
                                                "chart_title"=>$charts[0]->parameter_title,
                                                    "description"=>$charts[0]->assmntmtrc_dtpnt,
                                                        "value"=>$charts[0]->data_value=="NA"?"N/A":$charts[0]->data_value,
                                                    "upper_title"=>"EVERY",
                                                    "lower_title"=>"YEARS",
                                                    "labels"=>$charts[0]->labelsArr])
                    </div>
                @endif
                    @if($chart_type=="ratio")
                        <div class="col-lg-12 col-md-12 col-sm-12">
                            @include("cwis.pdf.cwis-mne.charts.single-valued-chart",
                                                [
                                                    "chart_title"=>$charts[0]->parameter_title,
                                                        "description"=>$charts[0]->assmntmtrc_dtpnt,
                                                        "value"=>$charts[0]->data_value=="NA"?"N/A":$charts[0]->data_value . ":" . (100-$charts[0]->data_value),
                                                        "upper_title"=>"",
                                                        "lower_title"=>"",
                                                        "labels"=>$charts[0]->labelsArr])
                        </div>
                    @endif
            </div>
</div>