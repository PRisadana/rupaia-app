@php
    $tabs = [
        'showcase' => ['Showcases', 'showcase.index'],
        'content' => ['Contents', 'content.index'],
        'folder' => ['Folders', 'folder.index'],
        'report' => ['Reported Items', 'seller.report.index'],
        'sales' => ['Sales', 'seller.sales.index'],
        'rating' => ['Ratings', 'seller.rating.index'],
    ];
@endphp

<ul class="nav nav-underline justify-content-center mb-4">
    @foreach ($tabs as $key => [$label, $route])
        <li class="nav-item">
            <a class="nav-link {{ $active === $key ? 'active' : 'text-secondary' }}" href="{{ route($route) }}">
                {{ $label }}
            </a>
        </li>
    @endforeach
</ul>
