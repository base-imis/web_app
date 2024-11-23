<script src="{{ $chart->cdn() }}"></script>
<figure class="single-valued-chart-figure">
    <div class="single-valued-chart-canvas-div">
        {!! $chart->container() !!}
        {{--                        <canvas class="{{$chart_canvas_id}} .single-valued-chart-canvas chartCanvas" width="150" height="150" aria-label="Example doughnut chart showing data as a percentage" role="img"></canvas>--}}
    </div>
    <figcaption class="single-valued-chart-caption" style="color: {{$primary_color}}">
        <span style="font-size: 13px">{{$description}}</span>
    </figcaption>

</figure>

