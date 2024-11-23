@include ('layouts.dashboard.card', [
    'number' => number_format($treatmentPlantCount),
    'heading' => 'Treatment Plants',
    'image' => asset('img/svg/imis-icons/treatment-plants.svg'),
])
