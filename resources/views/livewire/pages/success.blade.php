<x-app-layout>
    {{-- Передаємо змінну $order всередину нашого Volt-компонента --}}
    @livewire('pages.success-component', ['order' => $order])
</x-app-layout>