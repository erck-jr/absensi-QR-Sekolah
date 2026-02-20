@props(['title', 'icon' => 'list', 'color' => 'purple'])

@php
    $colorClasses = [
        'purple' => 'bg-purple-600',
        'blue' => 'bg-blue-500',
        'green' => 'bg-green-500',
        'orange' => 'bg-orange-500',
        'red' => 'bg-red-500',
        'gray' => 'bg-gray-700',
    ];
    $headerColor = $colorClasses[$color] ?? 'bg-purple-600';
@endphp

<div class="bg-white rounded-lg shadow-md relative mt-4">
    <!-- Header -->
    <div class="mx-4 -mt-6 p-4 {{ $headerColor }} rounded-lg shadow-lg flex items-center justify-between text-white">
        <div class="flex items-center">
            <div class="p-1 rounded mr-3">
                <span class="material-icons">{{ $icon }}</span>
            </div>
            <div>
                <h4 class="font-bold text-lg leading-tight">{{ $title }}</h4>
                @if(isset($subtitle))
                    <p class="text-sm opacity-80">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
        
        @if(isset($actions))
            <div class="flex items-center space-x-2">
                {{ $actions }}
            </div>
        @endif
    </div>

    <!-- Body -->
    <div class="p-6 pt-8">
        {{ $slot }}
    </div>
</div>
