<?php

namespace App\Filament\Widgets;

use App\Models\HolidayPackages;
use App\Models\Transactions;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Number;

class StatsOverview extends BaseWidget
{
    private function getPercentage(int $from, int $to): float
    {
        
        if ($from === 0 && $to === 0) {
            return 0;
        }

        return ($to - $from) / (($to + $from) / 2) * 100;
    }

    protected function getStats(): array
    {
        
        $newListing = HolidayPackages::whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year)
                        ->count() ?? 0;

        $transaction = Transactions::whereStatus('approved')
                        ->whereMonth('created_at', Carbon::now()->month)
                        ->whereYear('created_at', Carbon::now()->year);

        $prevTransaction = Transactions::whereStatus('approved')
                            ->whereMonth('created_at', Carbon::now()->subMonth()->month)
                            ->whereYear('created_at', Carbon::now()->subMonth()->year);

        // If no transactions exist, default to 0
        $transactionCount = $transaction->count() ?? 0;
        $prevTransactionCount = $prevTransaction->count() ?? 0;

        // Calculate percentages (default to 0 if there are no transactions)
        $transactionPercentage = $this->getPercentage($prevTransactionCount, $transactionCount) ?? 0;
        $revenuePercentage = $this->getPercentage($prevTransaction->sum('total_price') ?? 0, $transaction->sum('total_price') ?? 0) ?? 0;

        // Generate the stats
        return [
            Stat::make('New Listing of the month', $newListing),
            Stat::make('Transaction of the month', $transactionCount)
                ->description($transactionPercentage > 0 ? "{$transactionPercentage}% increased" : "{$transactionPercentage}% decreased")
                ->descriptionIcon($transactionPercentage > 0 ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down")
                ->color($transactionPercentage > 0 ? "success" : "danger"),
            Stat::make('Revenue of the month', Number::currency($transaction->sum('total_price') ?? 0, 'USD'))
                ->description($revenuePercentage > 0 ? "{$revenuePercentage}% increased" : "{$revenuePercentage}% decreased")
                ->descriptionIcon($revenuePercentage > 0 ? "heroicon-m-arrow-trending-up" : "heroicon-m-arrow-trending-down")
                ->color($revenuePercentage > 0 ? "success" : "danger"),
        ];
    }
}

