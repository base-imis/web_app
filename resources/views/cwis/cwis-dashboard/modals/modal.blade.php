<!-- Modal -->
<div class="modal modal-rel fade trend-modal" id="{{$modal_id}}_trend_modal" tabindex="-1" role="dialog" aria-labelledby="trendModalLabel" data-focus="false" data-backdrop="false">
    <div class="modal-dialog box-modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <div style="display: flex;justify-content: flex-end;">
                        <button class="btn btn-box-tool" id="{{$modal_id."_maximize"}}" title="Maximize" aria-label="Maximize"><span aria-hidden="true"><i class="fa fa-window-maximize"></i> </span></button>
                        <button type="button" class="btn close" title="Close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                </div>
            </div>
            <div class="modal-body">
                @include('cwis.cwis-dashboard.charts.trend-line',["trend_line_chart_id"=>$modal_id."_trend_canvas","charts"=>$charts])
            </div>
        </div>
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
    $(document).ready(function (){
        //show trend modal
        $("#{{$modal_id}}_trend_btn").click(function() {
            var modalId = $(this).attr('data-target');
            $.fn.modal.Constructor.prototype.enforceFocus = function() {};
            $(modalId).modal("toggle");
            $(modalId).on('show.bs.modal', function (e) {
                const waitForBackdrop = function () {
                    try { // can fail to get the config if the modal is opened for the first time
                        const config = $(this).data('bs.modal')._config || $(this).data('bs.modal').options;
                        if (config.backdrop !== false) {
                            const node = $(this).data('bs.modal')._backdrop ||
                                $(this).data("bs.modal").$backdrop;
                            if (node) {

                                $(node).addClass('modal-rel-backdrop').appendTo($(modalId).parent());
                                $('body').removeClass('modal-open');

                            } else {
                                window.requestAnimationFrame(waitForBackdrop);
                            }
                        }
                    } catch (e) {
                        window.requestAnimationFrame(waitForBackdrop);
                    }
                }.bind(this);
                waitForBackdrop();
                // $('.modal-backdrop').attr('id',$(this)[0].id+"_backdrop");
            });
            ApexCharts.exec("{{$modal_id}}_trend_canvas","updateOptions",{
                chart: {
                    width: '100%',
                    height: '100%'
                }
            });
        });

        //maximize
        $("#{{$modal_id."_maximize"}}").click(function (){
            $(this).parent().parent().parent().find(".modal-body")[0].requestFullscreen();
        });
    })

</script>