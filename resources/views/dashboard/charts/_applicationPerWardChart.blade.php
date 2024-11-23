<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Applications by Ward</h3>

    <div class="box-tools float-right">
      <button id="exportapplicationsPerWardChart" type="button" class="btn btn-box-tool"><i class="fa-solid fa-image"> </i></button>
      <button type="button" class="btn btn-box-tool"data-toggle="collapse" data-target="applicationsPerWardChart1"><i class="fa fa-minus"></i></button>
    </div>
  </div>
  <div class="box-body collapse show" id="applicationsPerWardChart1">
    <canvas id="applicationsPerWardChart" style="height:250px"></canvas>
  </div>
  <!-- /.box-body -->
</div>
<!-- /.box -->

@push('scripts')
<script>
var ctx = document.getElementById("applicationsPerWardChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $applicationsPerWardChart['labels']); ?>],
    datasets: [
        {
            label: "No. of applications",
            backgroundColor: "rgba(90, 155, 212,0.2)",
            borderColor: "rgba(90, 155, 212,1)",
            borderWidth: 1,
            hoverBackgroundColor: "rgba(90, 155, 212,0.4)",
            hoverBorderColor: "rgba(90, 155, 212,1)",
            data: [<?php echo implode(',', $applicationsPerWardChart['values']); ?>],
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    },
    scales: {
        yAxes: [{
            ticks: {
                beginAtZero: true
            }
        }]
    }
  }
});
document.getElementById('exportapplicationsPerWardChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#applicationsPerWardChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);
   
    a.download = 'Application by Ward.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush