<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Poverty Status In Informal Settlements</h3>

    <div class="box-tools float-right">
       <button id="exportpovertyInSettlementChart" type="button" class="btn btn-box-tool"><i class="fa-solid fa-image"> </i></button>
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
    </div>
  </div>
  <div class="box-body">
    <canvas id="povertyInSettlementChart" style="height:250px"></canvas>
  </div>
  <!-- /.box-body -->
</div>
<!-- /.box -->

@push('scripts')
<script>
var ctx = document.getElementById("povertyInSettlementChart");
var myChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: [<?php echo implode(',', $povertyInSettlementChart['labels']); ?>],
    datasets: [
        {
            label: "Households in settlements chart",
            backgroundColor: [<?php echo implode(',', $povertyInSettlementChart['colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $povertyInSettlementChart['colors']); ?>],
            borderWidth: 1,
            data: [<?php echo implode(',', $povertyInSettlementChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    }
  }
});
document.getElementById('exportpovertyInSettlementChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#povertyInSettlementChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);
   
    a.download = 'Poverty Status in Informal Settlements.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush