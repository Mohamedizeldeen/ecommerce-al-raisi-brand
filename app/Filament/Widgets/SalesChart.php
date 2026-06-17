<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\Order;
use Filament\Widgets\ChartWidget;

class SalesChart extends ChartWidget
{
    protected ?string $heading = 'Revenue — last 14 days';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $labels = [];
        $values = [];

        foreach (range(13, 0) as $offset) {
            $day = now()->subDays($offset)->startOfDay();
            $labels[] = $day->format('M j');

            $baisa = (int) Order::where('payment_status', PaymentStatus::Paid)
                ->whereBetween('paid_at', [$day, (clone $day)->endOfDay()])
                ->sum('total_baisa');

            $values[] = round($baisa / 1000, 3); // OMR
        }

        return [
            'datasets' => [[
                'label' => 'Revenue (OMR)',
                'data' => $values,
                'backgroundColor' => 'rgba(138, 109, 75, 0.5)',
                'borderColor' => '#8a6d4b',
                'borderWidth' => 1,
            ]],
            'labels' => $labels,
        ];
    }
}
