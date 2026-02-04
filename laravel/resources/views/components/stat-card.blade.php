@props(['label', 'value', 'icon' => null])

<div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 flex items-start justify-between">
    <div>
        <p class="text-sm font-medium text-gray-500 mb-1">{{ $label }}</p>
        <h3 class="text-2xl font-bold text-gray-900">{{ $value }}</h3>
    </div>
    @if($icon)
        <div class="p-2 bg-emerald-50 rounded-lg text-emerald-600">
            {!! $icon !!}
        </div>
    @endif
</div>
