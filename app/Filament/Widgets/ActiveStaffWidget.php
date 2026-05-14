<?php

namespace App\Filament\Widgets;

use App\Models\ActiveStaffSession;
use App\Models\WorkSession;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ActiveStaffWidget extends StatsOverviewWidget
{
    protected static ?int $sort = 0;

    protected function getStats(): array
    {
        $now = Carbon::now();
        $currentDayOfWeek = (int) $now->dayOfWeek;

        $activeStaff = ActiveStaffSession::with('user')
            ->where('active_context', 'active')
            ->active()
            ->get();

        $activeStaffCount = $activeStaff->count();

        $workSessionUserIds = WorkSession::where('is_active', true)
            ->whereJsonContains('day_of_week', $currentDayOfWeek)
            ->whereTime('start_time', '<=', $now->format('H:i:s'))
            ->whereTime('end_time', '>=', $now->format('H:i:s'))
            ->pluck('user_id')
            ->unique();

        $staffOnSchedule = $activeStaff->filter(fn ($s) => $workSessionUserIds->contains($s->user_id))->count();
        $staffOutOfSchedule = $activeStaffCount - $staffOnSchedule;

        return [
            Stat::make('Staff Aktif', $activeStaffCount)
                ->description($staffOnSchedule.' dalam jadwal, '.$staffOutOfSchedule.' di luar jadwal')
                ->descriptionIcon('heroicon-m-users')
                ->color($activeStaffCount > 0 ? 'success' : 'gray'),

            Stat::make('Sesi Kerja Hari Ini', WorkSession::where('is_active', true)
                ->whereJsonContains('day_of_week', $currentDayOfWeek)
                ->count())
                ->description('Jadwal aktif hari '.$now->translatedFormat('l'))
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('info'),

            Stat::make('Total Sesi Kerja', WorkSession::where('is_active', true)->count())
                ->description('Semua hari')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}
