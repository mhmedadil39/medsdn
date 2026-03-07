<x-admin::layouts>
    <x-slot:title>
        Settings
    </x-slot>

    <div class="flex items-center justify-between gap-4 max-sm:flex-wrap">
        <p class="text-xl font-bold text-gray-800 dark:text-white">
            Settings
        </p>
    </div>

    <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        @foreach ($sections as $section)
            <a
                href="{{ route($section['route']) }}"
                class="rounded box-shadow bg-white p-5 transition-all hover:translate-y-[-1px] hover:shadow-lg dark:bg-gray-900"
            >
                <p class="text-base font-semibold text-gray-800 dark:text-white">
                    {{ $section['title'] }}
                </p>

                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                    Open {{ strtolower($section['title']) }} settings.
                </p>
            </a>
        @endforeach
    </div>
</x-admin::layouts>
