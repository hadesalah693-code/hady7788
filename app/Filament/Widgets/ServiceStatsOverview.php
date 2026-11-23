<?php

namespace App\Filament\Widgets;

use App\Enums\CommissionStatus;
use App\Enums\ServiceProviderStatus;
use App\Enums\ServiceRequestStatus;
use App\Models\ServiceCommission;
use App\Models\ServiceProvider;
use App\Models\ServiceRequest;
use App\Models\ServiceReview;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $openRequests = ServiceRequest::query()->open()->count();
        $completedRequests = ServiceRequest::query()
            ->where('status', ServiceRequestStatus::Completed)
            ->count();
        $pendingProviders = ServiceProvider::query()
            ->where('status', ServiceProviderStatus::Pending)
            ->count();
        $activeProviders = ServiceProvider::query()
            ->where('status', ServiceProviderStatus::Active)
            ->count();
        $reportedReviews = ServiceReview::query()
            ->where('is_reported', true)
            ->count();
        $pendingCommissions = ServiceCommission::query()
            ->where('status', CommissionStatus::Pending);
        $pendingCommissionAmount = (float) (clone $pendingCommissions)->sum('amount');
        $pendingCommissionCount = (clone $pendingCommissions)->count();

        return [
            Stat::make('طلبات مفتوحة', number_format($openRequests))
                ->description('طلبات قيد المتابعة')
                ->descriptionIcon('heroicon-o-clipboard-document-list')
                ->color('info'),

            Stat::make('طلبات مكتملة', number_format($completedRequests))
                ->description('إجمالي الخدمات المنفذة')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('مقدمو خدمة نشطون', number_format($activeProviders))
                ->description($pendingProviders.' بانتظار الاعتماد')
                ->descriptionIcon('heroicon-o-user-group')
                ->color($pendingProviders > 0 ? 'warning' : 'success'),

            Stat::make('عمولات مستحقة', number_format($pendingCommissionAmount, 2).' ج.م')
                ->description($pendingCommissionCount.' عمولة غير محصلة')
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('warning'),

            Stat::make('بلاغات تقييمات', number_format($reportedReviews))
                ->description('تحتاج مراجعة الإدارة')
                ->descriptionIcon('heroicon-o-flag')
                ->color($reportedReviews > 0 ? 'danger' : 'gray'),
        ];
    }
}
