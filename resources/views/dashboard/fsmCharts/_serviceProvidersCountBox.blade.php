@include ('layouts.dashboard.card', [
    'number' => number_format($serviceProviderCount),
    'heading' => 'Service Providers',
    'image' => asset('img/svg/imis-icons/serviceProvider.svg'),
])

