<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatus;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BusinessStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $paid = Order::where('payment_status', PaymentStatus::Paid);

        $revenue = (int) (clone $paid)->sum('total_baisa');
        $monthRevenue = (int) (clone $paid)->where('paid_at', '>=', now()->startOfMonth())->sum('total_baisa');
        $paidCount = (clone $paid)->count();
        $pending = Order::where('payment_status', PaymentStatus::Pending)->count();
        $aov = $paidCount > 0 ? intdiv($revenue, $paidCount) : 0;
        $newCustomers = User::where('is_admin', false)->where('created_at', '>=', now()->subDays(30))->count();

        return [
            Stat::make('Revenue (paid)', format_omr($revenue))
                ->description(format_omr($monthRevenue).' this month')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Paid orders', number_format($paidCount))
                ->description($pending.' awaiting payment')
                ->descriptionIcon($pending > 0 ? 'heroicon-m-clock' : 'heroicon-m-check-circle')
                ->color($pending > 0 ? 'warning' : 'primary'),

            Stat::make('Avg. order value', format_omr($aov))
                ->description('Across all paid orders')
                ->descriptionIcon('heroicon-m-calculator')
                ->color('info'),

            Stat::make('Active products', number_format(Product::where('is_active', true)->count()))
                ->description(Product::where('is_active', true)->where('is_featured', true)->count().' featured')
                ->descriptionIcon('heroicon-m-shopping-bag')
                ->color('gray'),

            Stat::make('Customers', number_format(User::where('is_admin', false)->count()))
                ->description($newCustomers.' new in 30 days · '.NewsletterSubscriber::count().' subscribers')
                ->descriptionIcon('heroicon-m-users')
                ->color('gray'),
        ];
    }
}
