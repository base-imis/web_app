@include('layouts.dashboard.chart-card',[
    'card_title' => "Impact In Creating Public Awareness",
    'export_chart_btn_id' => "exportimpactCreatingPublicAwareness",
    'canvas_id' => "impactCreatingPublicAwareness"
])
@push('scripts')
<script>
var ctx = document.getElementById("impactCreatingPublicAwareness");
var myChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: @json($impactCreatingPublicAwareness['labels']),
    datasets: [
        {
            label: "Impact In Creating Public Awareness",
            backgroundColor: @json($impactCreatingPublicAwareness['colors']),
            borderColor: @json($impactCreatingPublicAwareness['borderColor']),
            hoverBackgroundColor: @json($impactCreatingPublicAwareness['hoverBackgroundColor']),
            hoverBorderColor: @json($impactCreatingPublicAwareness['hoverBorderColor']),
            borderWidth: 1,
            data: @json($impactCreatingPublicAwareness['values']),
        }
    ]
},
  options: {
    animation:{
      animateScale:true
    }
  }
});
document.getElementById('exportimpactCreatingPublicAwareness').addEventListener("click", downloadIMG);
  //donwload pdf from original canvas
  function downloadIMG() {
    var newCanvas = document.querySelector('#impactCreatingPublicAwareness');

    //create image from dummy canvas
    var newCanvasImg = newCanvas.toDataURL("image/png", 1.0);
    var a = document.createElement('a');
    a.href =newCanvas.toDataURL("image/png", 1.0);

    a.download = 'Impact in Creating Public Awareness.png';

    // Trigger the download
    a.click();
      }
</script>
@endpush
