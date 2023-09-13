@foreach($routes as $locale => $route)
    <link rel="alternate" href="{{ $route }}" hreflang="{{ $locale }}">
@endforeach

@if ($isHome)
    <link href="{{ url('/') }}" rel="alternate" hreflang="x-default">
@endif
