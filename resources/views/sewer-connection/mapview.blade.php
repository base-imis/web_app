@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/2.1.2/sweetalert.min.js"></script>
<style>
.map {
    width: 100%;
    height: 400px;
}
#kml-map {
    border: 1px solid #000000;
}
.layer-switcher {
    top: 0 !important;
}
a.skiplink {
    position: absolute;
    clip: rect(1px, 1px, 1px, 1px);
    padding: 0;
    border: 0;
    height: 1px;
    width: 1px;
    overflow: hidden;
}
a.skiplink:focus {
    clip: auto;
    height: auto;
    width: auto;
    background-color: #fff;
    padding: 0.3em;
}
#kml-map:focus {
    outline: #4A74A8 solid 0.15em;
}
</style>
<link rel="stylesheet" href="https://openlayers.org/en/v4.6.5/css/ol.css" type="text/css">
<link rel="stylesheet" href="https://unpkg.com/ol-layerswitcher@3.8.3/dist/ol-layerswitcher.css" />
<link rel="stylesheet" href="{{ asset('css/app.css') }}">

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel">Sewer Connection</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            </div>
            <div class="modal-body">
                <div id="kml-map" class="map"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://openlayers.org/en/v4.6.5/build/ol.js"></script>
<script src="https://unpkg.com/ol-layerswitcher@3.8.3"></script>
<script src="{{ asset('js/app.js') }}"></script>
<script>
var mapInitialized = false;
var map;

$(document).ready(function() {
    $('#exampleModal').on('shown.bs.modal', function(e) {
        var button = $(e.relatedTarget);
        var kml = button.data('id');
        var kml2 = button.data('sewer-code');
        var url = '/sewerconnection/sewerconnection/datageom/' + kml;
        var url2 = '/sewerconnection/sewerconnection/geomsewer/' + kml2;

        var request1 = $.ajax({ url: url, method: 'GET' });
        var request2 = $.ajax({ url: url2, method: 'GET' });

        $.when(request1, request2).done(function(response1, response2) {
            handleMapResponse(response1[0].wkt_geom, response2[0].wkt_geom, kml, kml2);
        }).fail(function() {
            showErrorModal("An error occurred while fetching data");
        });

        if (!mapInitialized) {
            initializeMap();
            mapInitialized = true;
        }
    });
});

function handleMapResponse(geomValue, sewerGeomValue, kml, kml2) {
    var format = new ol.format.WKT();

    if (geomValue) {
        var feature = format.readFeature(geomValue, {
            dataProjection: 'EPSG:4326',
            featureProjection: 'EPSG:3857'
        });

        var buildingLayer = createVectorLayer(feature, '#0000FF', 'BIN: ' + kml);
        addExtraLayer('building_layer', 'Building Layer', buildingLayer);
        fitMapView(feature.getGeometry().getExtent());
    } else {
        showErrorModal("Building not found");
    }

    if (sewerGeomValue) {
        var sewerFeature = format.readFeature(sewerGeomValue, {
            dataProjection: 'EPSG:4326',
            featureProjection: 'EPSG:3857'
        });

        var sewerLayer = createVectorLayer(sewerFeature, '#00FF00', 'Sewer Layer');
        addExtraLayer('sewer_layer', 'Sewer Layer', sewerLayer);
        fitMapView(sewerFeature.getGeometry().getExtent());
    } else {
        showErrorModal("Sewer not found");
    }
}

function initializeMap() {
    map = new ol.Map({
        target: 'kml-map',
        layers: [new ol.layer.Tile({ source: new ol.source.OSM() })],
        view: new ol.View({ center: ol.proj.fromLonLat([78.0, 23.0]), zoom: 5 })
    });
}

function createVectorLayer(feature, color, name) {
    return new ol.layer.Vector({
        source: new ol.source.Vector({ features: [feature] }),
        style: new ol.style.Style({
            stroke: new ol.style.Stroke({ color: color, width: 3 })
        }),
        title: name
    });
}

function addExtraLayer(layerId, layerName, layer) {
    if (map.getLayers().getArray().some(l => l.get('title') === layerName)) {
        map.removeLayer(map.getLayers().getArray().find(l => l.get('title') === layerName));
    }
    map.addLayer(layer);
}

function fitMapView(extent) {
    map.getView().fit(extent, { padding: [50, 50, 50, 50] });
}

function showErrorModal(message) {
    Swal.fire({
        title: 'Error!',
        text: message,
        icon: 'error',
        confirmButtonText: 'Ok'
    });
}
</script>
@endpush
