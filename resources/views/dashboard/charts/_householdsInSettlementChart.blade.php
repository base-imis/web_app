<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Informal Settlements By No. Of Households</h3>

    <div class="box-tools float-right">
       <button id="exporthouseholdsInSettlementChart" type="button" class="btn btn-box-tool"><i class="fa-solid fa-image"> </i></button>
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
    </div>
  </div>
  <div class="box-body">
    <canvas id="householdsInSettlementChart" style="height:250px"></canvas>
  </div>
  <!-- /.box-body -->
</div>
<!-- /.box -->

@push('scripts')
<script>
var ctx = document.getElementById("householdsInSettlementChart");
var myChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: [<?php echo implode(',', $householdsInSettlementChart['labels']); ?>],
    datasets: [
        {
            label: "Households in settlements chart",
            backgroundColor: [<?php echo implode(',', $householdsInSettlementChart['colors']); ?>],
            hoverBackgroundColor: [<?php echo implode(',', $householdsInSettlementChart['colors']); ?>],
            borderWidth: 1,
            data: [<?php echo implode(',', $householdsInSettlementChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    }
  }
});
document.getElementById('exporthouseholdsInSettlementChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#householdsInSettlementChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);
   
    a.download = 'Informal settlements by no. of households.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush