<div class="single-valued-num-chart">
    @isset($upper_title)
        <span class="single-valued-num-chart-text">{{$upper_title}}</span>
    @endisset
    @isset($value)
        <span class="single-valued-num-chart-number">{{$value}}</span>
    @endisset
    @isset($lower_title)
        <span class="single-valued-num-chart-text">{{$lower_title}}</span>
    @endisset
    @isset($description)
        <span>{{$description}}</span>
    @endisset
</div>
