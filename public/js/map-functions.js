function initializeMapLinks({workspace, geoserverUrl, authKey} = {}) {
    var geoserverWms = geoserverUrl + 'wms';
    var geoserverWfs = geoserverUrl + 'wfs';
    var geoserverLegend = geoserverWms + "?REQUEST=GetLegendGraphic&VERSION=1.0.0&FORMAT=image/png&WIDTH=20&HEIGHT=20&BBOX=89.1281,23.502, 89.2068,23.5892&LAYER=";
    var buildingsLayer = new ol.layer.Image({
        visible: false,
        title: "Buildings",
        source: new ol.source.ImageWMS({
            url: geoserverWms,
            params: {
                'LAYERS': workspace + ':' + 'buildings_layer',
                'TILED': true,
            },
            serverType: 'geoserver',
            transition: 0,
        })
    });
    var containmentsLayer = new ol.layer.Image({
        visible: false,
        title: "Containments",
        source: new ol.source.ImageWMS({
            url: geoserverWms,
            params: {
                'LAYERS': workspace + ':' + 'containments_layer',
                'TILED': true,
            },
            serverType: 'geoserver',
            transition: 0,
        })
    });
    var wardsLayer = new ol.layer.Tile({
        visible: true,
        title: "Wards",
        source: new ol.source.TileWMS({
            url: geoserverWms,
            params: {
                'LAYERS': workspace + ':' + 'wards_layer',
                'TILED': true,
                'STYLES': 'wards_layer_none'

            },
            serverType: 'geoserver',
            transition: 0,
        })
    });
    var roadLineLayer = new ol.layer.Image({
        visible: false,
        title: "Road Line",
        source: new ol.source.ImageWMS({
            url: geoserverWms,
            params: {
                'LAYERS': workspace + ':' + 'roadlines_layer',
                'TILED': true,
            },
            serverType: 'geoserver',
            transition: 0,
        })
    });
    var placesLayer = new ol.layer.Image({
        visible: false,
        title: "Places",
        source: new ol.source.ImageWMS({
            url: geoserverWms,
            params: {
                'LAYERS': workspace + ':' + 'places_layer',
                'TILED': true,

            },
            serverType: 'geoserver',
            transition: 0,
        })
    });
    var googleLayerHybrid = new ol.layer.Tile({
        visible: false,
        title: "Google Satellite & Roads",
        type: "base",
        source: new ol.source.TileImage({url: 'http://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}'}),
    });
    var googleLayerRoadmap = new ol.layer.Tile({
        title: "Google Road Map",
        type: "base",
        source: new ol.source.TileImage({url: 'http://mt1.google.com/vt/lyrs=m&x={x}&y={y}&z={z}'}),
    });
    var layerSwitcher = new LayerSwitcher({
        startActive: false,
        reverse: true,
        groupSelectStyle: 'group'
    });
    var eLayer = {};

    return {
        'geoserverWms': geoserverWms,
        'geoserverWfs': geoserverWfs,
        'geoserverLegend': geoserverLegend,
        'buildingsLayer': buildingsLayer,
        'containmentsLayer': containmentsLayer,
        'wardsLayer': wardsLayer,
        'roadLineLayer': roadLineLayer,
        'placesLayer': placesLayer,
        'googleLayerHybrid': googleLayerHybrid,
        'googleLayerRoadmap': googleLayerRoadmap,
        'layerSwitcher': layerSwitcher,
        'eLayer': eLayer,
    }
}

function setInitialZoom(map) {
    map.getView().setCenter(ol.proj.transform([85.38334613018505, 27.634613503939818], 'EPSG:4326', 'EPSG:3857'));
    map.getView().setZoom(12);
}

// Add extra overlay to Extra Overlays Object
function addExtraLayer(map, key, name, layer, eLayer) {
    // adding as property of Extra Overlays Object
    eLayer[key] = {name: name, layer: layer};
    // Adding layer to OpenLayers Map
    map.addLayer(layer);
}

function displayPointByCoordinates(map, lat, long, eLayer) {
    if (eLayer.selectedPointCoordinate) {
        eLayer.selectedPointCoordinate.layer.getSource().clear();
    } else {
        var layer = new ol.layer.Vector({
            source: new ol.source.Vector()
        });

        addExtraLayer(map, 'selectedPointCoordinate', 'Selected Point Coordinate', layer, eLayer);
    }

    var feature = new ol.Feature({
        geometry: new ol.geom.Point(ol.proj.transform([parseFloat(long), parseFloat(lat)], 'EPSG:4326', 'EPSG:3857'))
    });

    var style = new ol.style.Style({
        image: new ol.style.Icon({
            anchor: [0.5, 1],
            src: 'http://maps.google.com/mapfiles/ms/icons/red.png'
        })
    });

    feature.setStyle(style);

    eLayer.selectedPointCoordinate.layer.getSource().addFeature(feature);

    map.getView();
}


function lineGeomDrawer({workspace, geoserverUrl, authKey, mapID}) {
    initializeMapLinks({
        workspace: workspace,
        geoserverUrl: geoserverUrl,
        authKey: authKey
    });

}

function pointGeomDrawer({workspace, geoserverUrl, authKey, mapID, geom} = {}) {
    const {
        geoserverWms,
        geoserverWfs,
        geoserverLegend,
        buildingsLayer,
        containmentsLayer,
        wardsLayer,
        roadLineLayer,
        placesLayer,
        googleLayerHybrid,
        googleLayerRoadmap,
        layerSwitcher,
        eLayer
    } = initializeMapLinks({
        workspace: workspace,
        geoserverUrl: geoserverUrl,
        authKey: authKey
    });

    var map = new ol.Map({
        interactions: ol.interaction.defaults({
            altShiftDragRotate: false,
            dragPan: false,
            rotate: false,
            // mouseWheelZoom: false,
            doubleClickZoom: false
        }).extend([new ol.interaction.DragPan({kinetic: null})]),
        target: mapID,
        controls: ol.control.defaults({attribution: false}),
        layers: [
            new ol.layer.Group({
                title: 'Base maps',
                layers: [
                    googleLayerHybrid, googleLayerRoadmap
                ]
            }),
            new ol.layer.Group({
                title: 'Layers',
                fold: 'open',
                layers: [
                    wardsLayer, buildingsLayer, roadLineLayer
                ]
            })
        ],
        view: new ol.View({
            center: ol.proj.transform([85.37004580498977, 27.643296216592432], 'EPSG:4326', 'EPSG:3857'),
            minZoom: 12.5,
            maxZoom: 19,
            extent: ol.proj.transformExtent([85.32348539192756, 27.58711426558866, 85.44082675863419, 27.684646263435823], 'EPSG:4326', 'EPSG:3857')
        })
    });
    map.addControl(layerSwitcher);
    map.on('singleclick', function (evt) {
        if(eLayer.geomToView){
            map.removeLayer(eLayer.geomToView.layer);
        }
        var coordinate = ol.proj.transform(evt.coordinate, 'EPSG:3857', 'EPSG:4326');
        displayPointByCoordinates(map, coordinate[1], coordinate[0], eLayer);
        $('#latitude').val(coordinate[1]);
        $('#longitude').val(coordinate[0]);
    })
    setInitialZoom(map);
    addGeomToMap({
        geom: geom,
        map:map,
        eLayer:eLayer
    })
}

function polyGeomDrawer({workspace, geoserverUrl, authKey, mapID, geom} = {}) {
    const {
        geoserverWms,
        geoserverWfs,
        geoserverLegend,
        buildingsLayer,
        containmentsLayer,
        wardsLayer,
        roadLineLayer,
        placesLayer,
        googleLayerHybrid,
        googleLayerRoadmap,
        layerSwitcher,
        eLayer
    } = initializeMapLinks({
        workspace: workspace,
        geoserverUrl: geoserverUrl,
        authKey: authKey
    });

    var map = new ol.Map({
        interactions: ol.interaction.defaults({
            altShiftDragRotate: false,
            dragPan: false,
            rotate: false,
            doubleClickZoom: false
        }).extend([new ol.interaction.DragPan({kinetic: null})]),
        target: mapID,
        controls: ol.control.defaults({attribution: false}),
        layers: [
            new ol.layer.Group({
                title: 'Base maps',
                layers: [
                    googleLayerHybrid, googleLayerRoadmap
                ]
            }),
            new ol.layer.Group({
                title: 'Layers',
                fold: 'open',
                layers: [
                    wardsLayer, buildingsLayer, roadLineLayer
                ]
            })
        ],
        view: new ol.View({
            center: ol.proj.transform([85.37004580498977, 27.643296216592432], 'EPSG:4326', 'EPSG:3857'),
            minZoom: 12.5,
            maxZoom: 19,
            extent: ol.proj.transformExtent([85.32348539192756, 27.58711426558866, 85.44082675863419, 27.684646263435823], 'EPSG:4326', 'EPSG:3857')
        })
    });
    map.addControl(layerSwitcher);
    if(!eLayer.geomToView) {
        var reportPolygonBufferLayer = new ol.layer.Vector({

            source: new ol.source.Vector(),
            style: new ol.style.Style({
                stroke: new ol.style.Stroke({
                    color: '#0000FF',
                    width: 3
                }),
            })
        });


        addExtraLayer(map,'geomToView', 'Report Polygon Buffer', reportPolygonBufferLayer,eLayer);
    }
    draw = new ol.interaction.Draw({
        source: eLayer.geomToView.layer.getSource(),
        type: 'Polygon'
    });
    map.addInteraction(draw);
    draw.on('drawstart', function(evt){
        eLayer.geomToView.layer.getSource().clear();
    });
    draw.on('drawend', function(evt){
        var format = new ol.format.WKT();
        var geom = format.writeGeometry(evt.feature.getGeometry().clone().transform('EPSG:3857', 'EPSG:4326'));
        $('#geom').val(geom);
    });
    setInitialZoom(map);
    addGeomToMap({
        geom: geom,
        map:map,
        eLayer:eLayer
    })
}

function geomViewer({workspace, geoserverUrl, authKey, mapID, geom} = {}) {
    const {
        geoserverWms,
        geoserverWfs,
        geoserverLegend,
        buildingsLayer,
        containmentsLayer,
        wardsLayer,
        roadLineLayer,
        placesLayer,
        googleLayerHybrid,
        googleLayerRoadmap,
        layerSwitcher,
        eLayer
    } = initializeMapLinks({
        workspace: workspace,
        geoserverUrl: geoserverUrl,
        authKey: authKey
    });

    var map = new ol.Map({
        interactions: ol.interaction.defaults({
            altShiftDragRotate: false,
            dragPan: false,
            rotate: false,
            doubleClickZoom: false
        }).extend([new ol.interaction.DragPan({kinetic: null})]),
        target: mapID,
        controls: ol.control.defaults({attribution: false}),
        layers: [
            new ol.layer.Group({
                title: 'Base maps',
                layers: [
                    googleLayerHybrid, googleLayerRoadmap
                ]
            }),
            new ol.layer.Group({
                title: 'Layers',
                fold: 'open',
                layers: [
                    wardsLayer, buildingsLayer, roadLineLayer
                ]
            })
        ],
        view: new ol.View({
            center: ol.proj.transform([85.37004580498977, 27.643296216592432], 'EPSG:4326', 'EPSG:3857'),
            minZoom: 12.5,
            maxZoom: 19,
            extent: ol.proj.transformExtent([85.32348539192756, 27.58711426558866, 85.44082675863419, 27.684646263435823], 'EPSG:4326', 'EPSG:3857')
        })
    });
    map.addControl(layerSwitcher);
    addGeomToMap({
        geom:geom,
        map:map,
        eLayer:eLayer
    })

}

function addGeomToMap({geom, map, eLayer}) {
    if (geom!==''){
        if (!eLayer.geomToView) {
            var geomLayer = new ol.layer.Vector({
                source: new ol.source.Vector(),
            });
            addExtraLayer(map,'geomToView', 'Geom', geomLayer,eLayer);
        }
        var format = new ol.format.WKT();
        var feature = format.readFeature(geom, {
            dataProjection: 'EPSG:4326',
            featureProjection: 'EPSG:3857'
        });
        switch (feature.getGeometry().getType()) {
            case 'Point':
                feature.setStyle(
                    new ol.style.Style({
                        image: new ol.style.Icon({
                            anchor: [0.5, 1],
                            src: 'http://maps.google.com/mapfiles/ms/icons/red.png'
                        })
                    })
                );
                break;
        }
        eLayer.geomToView.layer.getSource().addFeature(feature);
        map.getView().fit(geomLayer.getSource().getExtent(), map.getSize());
    } else {
        setInitialZoom(map);
    }
}
