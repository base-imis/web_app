@php
  $totalRequestCount = $totalRequestCount ?? 0;
@endphp

<div class="info-box">
  <div class="info-box-icon bg-info">
    <img src="{{ asset('/img/icons/septic-tank-light.png') }}" aria-hidden="true" style="width: 50px; height: auto;">
  </div>

  <div class="info-box-content">
    <span class="info-box-text"><h3>{{ (int)$totalRequestCount }}</h3></span>
    <span class="info-box-number">Septic Tank Inspection Pending Requests</span>
  </div>
</div>
