@php
  $Building_data = $Building_data ?? ($buildingData ?? []);
  $above = (int) ($Building_data['TotalAbovePlinthLevelApproval'] ?? 0);
@endphp

<div class="info-box">
  <span class="info-box-icon bg-info"><i class="fa-regular fa-building"></i></span>
  <div class="info-box-content">
    <span class="info-box-text"><h3>{{ $above }}</h3></span>
    <span class="info-box-number">Above Plinth Level Approval</span>
  </div>
</div>
