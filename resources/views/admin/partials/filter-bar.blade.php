@php
    $searchPlaceholder = $searchPlaceholder ?? null;
    $filters = $filters ?? [];

    $fieldNames = collect($filters)->pluck('name')->when($searchPlaceholder, fn($names) => $names->push('q'))->all();

    $isFiltered = collect($fieldNames)->contains(fn($name) => request()->filled($name));
    $fieldErrors = collect($fieldNames)->flatMap(fn($name) => $errors->get($name));
@endphp

@if ($fieldErrors->isNotEmpty())
    <div class="alert alert-danger">
        @foreach ($fieldErrors as $message)
            <div>{{ $message }}</div>
        @endforeach
    </div>
@endif

<form method="GET" action="{{ $action }}" class="row g-2 mb-4">
    @if ($searchPlaceholder)
        <div class="col-md-4">
            <input type="search" name="q" value="{{ request('q') }}" maxlength="100" class="form-control"
                placeholder="{{ $searchPlaceholder }}">
        </div>
    @endif

    @foreach ($filters as $filter)
        @php
            $options = array_is_list($filter['options'])
                ? collect($filter['options'])
                    ->mapWithKeys(fn($option) => [$option => ucfirst(str_replace('_', ' ', $option))])
                    ->all()
                : $filter['options'];
        @endphp
        <div class="col-md-3">
            <select name="{{ $filter['name'] }}" class="form-select" aria-label="{{ $filter['label'] }}">
                <option value="">All {{ $filter['label'] }}</option>
                @foreach ($options as $value => $label)
                    <option value="{{ $value }}" @selected((string) request($filter['name']) === (string) $value)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    @endforeach

    <div class="col-md-auto">
        <button type="submit" class="btn btn-dark">Apply</button>
    </div>

    @if ($isFiltered)
        <div class="col-md-auto">
            <a href="{{ $action }}" class="btn btn-outline-secondary">Reset</a>
        </div>
    @endif
</form>
