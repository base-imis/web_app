<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Holding Taxes Status (Dummy)</h3>

    <div class="box-tools float-right">
      <button id="exportholdingTaxStatusChart" type="button" class="btn btn-box-tool"><i class="fa-solid fa-image"> </i></button>
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
    </div>
  </div>
  <div class="box-body">
    <canvas id="holdingTaxStatusChart" style="height:250px"></canvas>
  </div>
  <!-- /.box-body -->
</div>
<!-- /.box -->

@push('scripts')
<script>
var ctx = document.getElementById("holdingTaxStatusChart");
var myChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: [<?php echo implode(',', $holdingTaxStatusChart['labels']); ?>],
    datasets: [
        {
            label: "Holding Taxes Status",
            backgroundColor: [<?php echo implode(',', $holdingTaxStatusChart['colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $holdingTaxStatusChart['colors']); ?>],
            borderWidth: 1,
            data: [<?php echo implode(',', $holdingTaxStatusChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    }
  }
});
document.getElementById('exportholdingTaxStatusChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#holdingTaxStatusChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);
   
    a.download = 'Holding Taxes Status (Dummy).png';

    // Trigger the download
    a.click();
      }
</script>
@endpush