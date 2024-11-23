<div class="single-valued-num-chart" style="text-align: center;">
    @isset($upper_title)
        <span class="single-valued-num-chart-text">{{$upper_title}}</span><br>
    @endisset
    @isset($value)
        <span class="single-valued-num-chart-number">{{$value}}</span><br>
    @endisset
    @isset($lower_title)
        <span class="single-valued-num-chart-text">{{$lower_title}}</span><br>
    @endisset
    @isset($description)
        <span>{{$description}}</span>
    @endisset
</div>
