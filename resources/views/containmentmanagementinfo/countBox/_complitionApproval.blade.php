@php
  $Building_data = $Building_data ?? ($buildingData ?? []);
  $completion = (int) ($Building_data['TotalCompletionApproval'] ?? 0);
@endphp

<div class="info-box">
  <span class="info-box-icon bg-info"><i class="fa-solid fa-building-wheat"></i></span>

  <div class="info-box-content">
    <span class="info-box-text"><h3>{{ $completion }}</h3></span>
    <span class="info-box-number">Completion Approval</span>
  </div>
</div>
