@include ('layouts.dashboard.card', [
    'number' => number_format($sludgeCollectionEmptyingServices, 0),
    'heading' => 'Volume of Sludge (m³) Emptied',
    'image' => asset('img/svg/imis-icons/desludgingVehicle.svg'),
])
