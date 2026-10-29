@php
    $logoUrl = asset('images/logo.png');
@endphp

<img
    alt="{{ config('app.name') }} logo"
    src="{{ $logoUrl }}"
    loading="lazy"
    style=""
    class="h-full w-full rounded-full object-cover shadow-sm"
/>
