<?php

namespace Khoirulaksara\Awrel\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected bool $deferLoading = false;

    public function deferLoading(bool $condition = true): static
    {
        $this->deferLoading = $condition;

        return $this;
    }

    protected function getViewData(): array
    {
        return array_merge(parent::getViewData(), [
            'deferLoading' => $this->deferLoading,
        ]);
    }
}
