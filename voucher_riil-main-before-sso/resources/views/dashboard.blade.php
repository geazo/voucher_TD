<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Welcome') }}
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="p-3 text-white bg-green-500 rounded">
            {{ session('success') }}
        </div>
    @endif

</x-app-layout>
