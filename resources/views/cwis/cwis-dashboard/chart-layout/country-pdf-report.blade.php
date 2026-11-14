<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ __('CWIS Report') }} {{ $reportingYear }}</title>
    <style>
        body { font-family: Arial, sans-serif; color: #263238; font-size: 12px; }
        h1 { font-size: 26px; margin: 0 0 10px; }
        h2 { font-size: 22px; margin: 20px 0 12px; }
        .next-outcome { page-break-before: always; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        tr { page-break-inside: avoid; }
        td { width: 33.33%; padding: 16px 12px; border: 1px solid #D9D9D9; background: #F4F9F7; text-align: center; vertical-align: middle; word-wrap: break-word; }
        td.empty { border: 0; background: transparent; }
        .value { font-size: 28px; font-weight: bold; margin-bottom: 10px; }
        .unit { font-size: 11px; margin-bottom: 8px; }
        .label { font-size: 12px; line-height: 1.5; margin: 0; }
        .doughnut { width: 150px; height: 150px; position: relative; margin: 0 auto 12px; }
        .doughnut img { width: 150px; height: 150px; }
        .doughnut .value { position: absolute; top: 62px; left: 0; width: 150px; font-size: 20px; margin: 0; }
    </style>
</head>
<body>
@php
    $groups = [
        'equity' => ['Equity', '#6CB4EE'],
        'safety' => ['Safety', '#29ab87'],
        'sustainability' => ['Sustainability', '#B7791F'],
        'responsibility' => ['Responsibility', '#A43F69'],
        'accountability' => ['Accountability', config('cwis_accountability.color')],
        'rpm' => ['Resource Planning and Management', config('cwis_rpm.color')],
    ];
    $safetyDefinitions = config('cwis_safety_d22.indicators', []);
    $firstGroup = true;
@endphp
<h1>{{ __('CWIS Monitoring and Evaluation') }}</h1>
<p>{{ __('Country') }}: {{ $countryLabel }} &nbsp; {{ __('Year') }}: {{ $reportingYear }}</p>
@foreach($groups as $outcome => [$heading, $color])
    @php
        $indicators = $catalogue->where('outcome', $outcome)->values();
    @endphp
    @if($indicators->isEmpty()) @continue @endif
    <section class="{{ $firstGroup ? '' : 'next-outcome' }}">
        <h2>{{ __($heading) }}</h2>
        @if(!$firstGroup)<p>{{ __('Country') }}: {{ $countryLabel }} &nbsp; {{ __('Year') }}: {{ $reportingYear }}</p>@endif
        @php
            $firstGroup = false;
        @endphp
        <table>
        @foreach($indicators->chunk(3) as $row)
            <tr>
            @foreach($row as $indicator)
                @php
                    $code = $indicator->indicator_code;
                    $value = $values[$code] ?? null;
                    $numeric = is_numeric($value) && is_finite((float) $value);
                    $count = in_array($code, ['SF-0a', 'SF-0b', 'SF-0c'], true);
                    $unit = '';
                    if ($outcome === 'safety' && !$count) {
                        $unit = $safetyDefinitions[$code]['unit'] ?? ($code === 'SF-3e' ? 'm' : '%');
                    } elseif (in_array($code, ['EQ-4', 'EQ-4a', 'SS-1a', 'SS-1b'], true)) {
                        $unit = '%';
                    }
                    if ($count) {
                        $display = \App\Support\CwisValueFormatter::formatCount($value);
                    } elseif ($numeric) {
                        $display = \App\Support\CwisValueFormatter::format($value);
                    } elseif (in_array($value, ['NA', 'Not applicable'], true)) {
                        $display = __('Not applicable');
                    } else {
                        $display = __(in_array($value, ['Yes', 'No'], true) ? $value : 'No data');
                    }
                    $doughnut = $outcome === 'safety' && $numeric && !$count && $unit === '%';
                    if ($doughnut) {
                        // Clamp only the arc; keep the actual calculated value in the label.
                        $circumference = 2 * M_PI * 60;
                        $arc = max(0, min(100, (float) $value)) / 100 * $circumference;
                        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150" viewBox="0 0 150 150">'
                            . '<circle cx="75" cy="75" r="60" fill="none" stroke="#E1E8E5" stroke-width="16"/>'
                            . '<circle cx="75" cy="75" r="60" fill="none" stroke="' . $color . '" stroke-width="16"'
                            . ' stroke-dasharray="' . $arc . ' ' . $circumference . '" transform="rotate(-90 75 75)"/></svg>';
                        $chartImage = 'data:image/svg+xml;base64,' . base64_encode($svg);
                    }
                @endphp
                <td data-indicator-code="{{ $code }}">
                    @if($doughnut)
                    <div class="doughnut" data-chart="safety-percentage">
                        <img src="{{ $chartImage }}" alt="{{ __($indicator->label) }}">
                        <div class="value" style="color: {{ $color }};">{{ $display }}%</div>
                    </div>
                    @else
                    <div class="value" style="color: {{ $color }};">{{ $display }}@if($numeric && !$count && $unit === '%')%@endif</div>
                    @endif
                    @if($numeric && !$count && $unit !== '' && $unit !== '%')<div class="unit">{{ __($unit) }}</div>@endif
                    <p class="label">{{ __($indicator->label) }}</p>
                </td>
            @endforeach
            @for($empty = $row->count(); $empty < 3; $empty++)<td class="empty"></td>@endfor
            </tr>
        @endforeach
        </table>
    </section>
@endforeach
@if($firstGroup)<p>{{ __('No indicators are configured for this country.') }}</p>@endif
</body>
</html>
