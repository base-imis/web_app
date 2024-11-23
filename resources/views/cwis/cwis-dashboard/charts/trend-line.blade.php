
{{--<div style="width:90%;height: 100%;">
    <canvas id="{{$trend_line_chart_id}}" class="chartjs-render-monitor"></canvas>
</div>--}}
{{--<script>
    $(document).ready(function() {
        var years= [];
        var dataset = [];
        var colors = ['rgba(255, 99, 132, 0.2)','rgba(255, 159, 64, 0.2)','rgba(255, 205, 86, 0.2)'];
        var borderColors = ['rgb(255, 99, 132)','rgb(255, 159, 64)','rgb(255, 205, 86)']
        @foreach($charts[0]->years as $year)
                years.push({{$year}});
        @endforeach
        @foreach($charts as $key=>$chart)
        dataset.push(
            {
                label: "{{trim(str_replace('\n', '', (str_replace('\r', '', $chart->assmntmtrc_dtpnt))))}}",
                backgroundColor: colors[{{$key}}],
                borderColor: borderColors[{{$key}}],
                fill: false,
                data: @json($chart->yearly_data),
            }
        )
        @endforeach
        var trendChart = new Chart('{{$trend_line_chart_id}}',
        {
            type: 'line',
                data: {
            labels: years,
                datasets: dataset
        },
            options: {
                responsive: true,
                    maintainAspectRatio: false,
                    // title: {
                    //     display: true,
                    //     text: 'Chart.js Line Chart - Logarithmic'
                    // },
                    scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Years'
                        },

                    }],
                        yAxes: [{
                        display: true,
                        //type: 'logarithmic',
                        scaleLabel: {
                            display: true,
                            labelString: 'Percentage'
                        },
                        ticks: {
                            min: 0,
                            max: 100,

                            // forces step size to be 5 units
                            stepSize: 20
                        }
                    }]
                }
            }
        });
    });
</script>--}}


<div id="{{$trend_line_chart_id}}" style="width: 100%"></div>
<script>
    $(document).ready(function (){
        var years= [];
        var dataset = [];
        @foreach($charts[0]->years as $year)
        years.push({{$year}});
        @endforeach
        @foreach($charts as $key=>$chart)
            @if(strlen($chart->label)>0)
                dataset.push(
                    {
                        name: "{{trim(str_replace('\n', '', (str_replace('\r', '', $chart->label))))}}",
                        data: @json($chart->yearly_data),
                    }
                );
            @elseif($chart->heading)
                dataset.push(
                    {
                        name: "{{trim(str_replace('\n', '', (str_replace('\r', '', $chart->heading))))}}",
                        data: @json($chart->yearly_data),
                    }
                );
            @else
                dataset.push(
                    {
                        name: "{{trim(str_replace('\n', '', (str_replace('\r', '', $chart->assmntmtrc_dtpnt))))}}",
                        data: @json($chart->yearly_data),
                    }
                );
            @endif
        @endforeach
        var optionsLine = {
            chart: {
                id: "{{$trend_line_chart_id}}",
                width: '100%',
                height: '100%',
                type: 'line',
                zoom: {
                    enabled: false
                },
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            //colors: ["#3F51B5", '#2196F3'],
            series: dataset,
            markers: {
                size: 6,
                strokeWidth: 0,
                hover: {
                    size: 9
                }
            },
            grid: {
                show: true,
                padding: {
                    bottom: 0
                }
            },
            labels: years,
            xaxis: {
                tooltip: {
                    enabled: false
                }
            },
            legend: {
                position: 'top',
                showForSingleSeries: true,
                showForNullSeries: true,
                horizontalAlign: 'right',
                offsetY: 25
            }
        }

        var chartLine = new ApexCharts(document.querySelector('#{{$trend_line_chart_id}}'), optionsLine);
        chartLine.render();
    });

</script>
