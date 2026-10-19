@props(['title', 'description' => ''])

<div class="flex flex-wrap items-center justify-between gap-4">
    <div class="min-w-0">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
        @if ($description)<p class="mt-1 max-w-2xl text-sm leading-6 text-gray-500 dark:text-gray-400">{{ $description }}</p>@endif
    </div>
    @if ($slot->isNotEmpty())<div class="flex flex-wrap items-center gap-3">{{ $slot }}</div>@endif
</div>
