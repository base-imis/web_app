<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Holding Taxes</h3>

    <div class="box-tools float-right">
      <button id="exportholdingTaxesChart" type="button" class="btn btn-box-tool"><i class="fa-solid fa-image"> </i></button>
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
    </div>
  </div>
  <div class="box-body">
    <canvas id="holdingTaxesChart" style="height:250px"></canvas>
  </div>
  <!-- /.box-body -->
</div>
<!-- /.box -->

@push('scripts')
<script>
var ctx = document.getElementById("holdingTaxesChart");
var myChart = new Chart(ctx, {
  type: 'line',
  data: {
    labels: ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"],
    datasets: [
        {
            label: "Number of buidlings whose holding taxes were paid by month",
            fill: false,
            lineTension: 0.1,
            backgroundColor: "rgba(200, 99, 132, 0.2)",
            borderColor: "rgba(200,99,132,1)",
            data: [2200, 1590, 1800, 2210, 2060, 2150, 1810, 2330, 2040, 2300, 1700, 2170],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    }
  }
});
document.getElementById('exportholdingTaxesChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#holdingTaxesChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);
   
    a.download = 'Holding taxes.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush