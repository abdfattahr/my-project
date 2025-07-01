<x-filament-panels::page class="fi-dashboard-page">
    <div class="text-center">
        @if ($this->getDescription())
            <div class="text-gray-600 text-right mt-2 mb-4">{!! $this->getDescription() !!}</div>
        @endif
    </div>

    @if (method_exists($this, 'filtersForm'))
        {{ $this->filtersForm }}
    @endif

    <x-filament-widgets::widgets
        :columns="$this->getColumns()"
        :data="
            [
                ...(property_exists($this, 'filters') ? ['filters' => $this->filters] : []),
                ...$this->getWidgetData(),
            ]
        "
        :widgets="$this->getVisibleWidgets()"
    />
</x-filament-panels::page>
