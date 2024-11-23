<div class="box box-primary">
  <div class="box-header with-border">
    <h3 class="box-title">Containement Types By Wards (Builtup Only)</h3>

    <div class="box-tools float-right">
    <button id="exportcontainmentTypesByBuiltupPerwardChart" type="button" class="btn btn-box-tool"><i class="fa-solid fa-image"> </i></button>
      <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
    </div>
  </div>
  <div class="box-body " class="collapse show">
    <canvas id="containmentTypesByBuiltupPerwardChart" style="height:250px"></canvas>
  </div>
  <!-- /.box-body -->
</div>
<!-- /.box -->

@push('scripts')
<script>
var ctx = document.getElementById("containmentTypesByBuiltupPerwardChart");
var myChart = new Chart(ctx, {
  type: 'bar',
  data: {
    labels: [<?php echo implode(',', $containmentTypesByBuiltupPerwardChart['labels']); ?>],
    datasets: [
        @foreach($containmentTypesByBuiltupPerwardChart['datasets'] as $dataset)
        {
            label: <?php echo $dataset['label']; ?>,
            backgroundColor: <?php echo $dataset['color']; ?>,
            data: [<?php echo implode(',', $dataset['data']); ?>],
        },
        @endforeach
    ]
},
  options: {
    animation:{
      animateScale:true
    },
    scales: {
      xAxes: [{
        stacked: true,
        ticks: {
                beginAtZero: true
            }
      }],
      yAxes: [{
        stacked: true,
        ticks: {
                beginAtZero: true
            }
      }]
    },
    tooltips: {
        mode: 'index'
    }
  }
});
document.getElementById('exportcontainmentTypesByBuiltupPerwardChart').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#containmentTypesByBuiltupPerwardChart');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);
   
    a.download = 'Containement Types by Wards (Builtup only).png';

    // Trigger the download
    a.click();
      }
</script>
@endpush