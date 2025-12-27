@php
  $Building_data = $Building_data ?? ($buildingData ?? []);
  $result = $result ?? (
      (int)($Building_data['TotalPlinthLevelApproval'] ?? 0)
    + (int)($Building_data['TotalAbovePlinthLevelApproval'] ?? 0)
    + (int)($Building_data['TotalCompletionApproval'] ?? 0)
  );
@endphp

<div class="info-box">
  <span class="info-box-icon bg-info"><i class="fa-solid fa-building-circle-check"></i></span>
  <div class="info-box-content">
    <span class="info-box-text"><h3>{{ (int)$result }}</h3></span>
    <span class="info-box-number">Building Permit Application</span>
    <p style="font-size:12px">Total approvals (Temporary + Above Plinth + Completion)</p>
  </div>
</div>
