

@if( $indicators['indicator'] == 'Response Time')
<div class="col-md-4 col-sm-6 col-12">
        <div class="info-box bg-light-blue-gradient">
            <span class="info-box-icon">{!! $indicators['icon'] !!}</span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold">{{ $indicators['indicator'] }}</span>
                <span class="info-box-text">Expected: {{ $indicators['target'] }} hrs</span>
                <span class="info-box-number">{{ $indicators['achievement'] }} hrs</span>
                
            </div>
        </div>
    </div>
@else
<div class="col-md-4 col-sm-6 col-12">
        <div class="info-box bg-light-blue-gradient">
            <span class="info-box-icon">{!! $indicators['icon'] !!}</span>
            <div class="info-box-content">
                <span class="info-box-text font-weight-bold">{{ $indicators['indicator'] }}</span>
                <span class="info-box-text">Expected: {{ $indicators['target'] }}%</span>
                <span class="info-box-number">{{ $indicators['achievement'] }}%</span>
                <div class="progress">
                    <div class="progress-bar" style="width:{{ $indicators['achievement'] }}%;"></div>
                </div>
            </div>
        </div>
    </div>
@endif
