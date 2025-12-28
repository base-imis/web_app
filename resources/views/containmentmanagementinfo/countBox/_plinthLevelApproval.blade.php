@php
  $Building_data = $Building_data ?? ($buildingData ?? []);
  $plinth = (int) ($Building_data['TotalPlinthLevelApproval'] ?? 0);
@endphp

<div class="info-box">
  <span class="info-box-icon bg-info"><i class="fa-solid fa-building-circle-exclamation"></i></span>

  <div class="info-box-content">
    <span class="info-box-text"><h3>{{ $plinth }}</h3></span>
    <span class="info-box-number">Temporary Approval</span>
  </div>
</div>
