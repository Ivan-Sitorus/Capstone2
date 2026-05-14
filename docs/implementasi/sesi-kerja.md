# Sesi Kerja Kasir & Kitchen — POS Cafe W9

## Gambaran Umum

Sesi kerja (WorkSession) adalah mekanisme untuk mengelola jadwal kerja staff kasir dan dapur. Setiap staff memiliki jadwal mingguan yang menentukan hari dan jam kerja mereka. Sistem menggunakan jadwal ini untuk dua tujuan:

1. **Popup peringatan** — mengingatkan staff 5 menit sebelum jam kerja berakhir
2. **Laporan kehadiran** — admin dapat melihat siapa yang seharusnya bekerja real-time

Sesi kerja bersifat **opsional**. Staff tetap bisa login dan bekerja meskipun tidak memiliki jadwal untuk hari tersebut.

---

## WorkSession Model

File: `app/Models/WorkSession.php`

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WorkSession extends Model
{
    protected $fillable = [
        'user_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'array',     // JSON: [1,2,3,4,5] untuk Senin-Jumat
            'start_time'  => 'datetime:H:i',
            'end_time'    => 'datetime:H:i',
            'is_active'   => 'boolean',
        ];
    }

    // Relasi
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scope: session yang berlaku hari ini
    public function scopeForToday(Builder $query): void
    {
        $today = now()->dayOfWeek;          // 1 = Senin, 7 = Minggu
        $query->whereJsonContains('day_of_week', $today)
              ->where('is_active', true);
    }

    // Scope: session untuk user tertentu
    public function scopeForUser(Builder $query, int $userId): void
    {
        $query->where('user_id', $userId);
    }

    // Helper: cek apakah session sedang berlangsung sekarang
    public function isOngoing(): bool
    {
        $now = now();
        $start = $now->copy()->setTimeFromTimeString($this->start_time->format('H:i'));
        $end = $now->copy()->setTimeFromTimeString($this->end_time->format('H:i'));

        return $now->between($start, $end);
    }

    // Helper: berapa menit lagi sampai session berakhir
    public function minutesUntilEnd(): ?int
    {
        if (!$this->isOngoing()) {
            return null;
        }

        $now = now();
        $end = $now->copy()->setTimeFromTimeString($this->end_time->format('H:i'));

        return max(0, $now->diffInMinutes($end, false));
    }

    // Format hari: [1,2,3,4,5] → "Senin - Jumat"
    public function getDayOfWeekLabelAttribute(): string
    {
        $days = [
            1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu',
            4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu',
        ];

        $labels = array_map(fn($d) => $days[$d] ?? '?', $this->day_of_week);

        if (count($labels) === 1) {
            return $labels[0];
        }

        return $labels[0] . ' - ' . $labels[count($labels) - 1];
    }
}
```

### Contoh Data

| user_id | day_of_week | start_time | end_time | is_active |
|---|---|---|---|---|
| 2 (Kasir A) | `[1,2,3,4,5]` | `08:00` | `16:00` | `true` |
| 3 (Kasir B) | `[1,2,3,4,5]` | `14:00` | `22:00` | `true` |
| 4 (Dapur A) | `[1,2,3,4,5,6]` | `10:00` | `20:00` | `true` |
| 5 (Part-time) | `[6,7]` | `09:00` | `15:00` | `true` |

---

## Migration

```php
// database/migrations/xxxx_xx_xx_create_work_sessions_table.php

Schema::create('work_sessions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->json('day_of_week');                    // [1,2,3,4,5] — Monday to Friday
    $table->time('start_time');
    $table->time('end_time');
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index('user_id');
    $table->index('is_active');
});
```

---

## Middleware: CheckWorkSession

Middleware ini berjalan di setiap request untuk antarmuka kasir dan dapur. Fungsinya mengecek apakah staff memiliki WorkSession hari ini dan, jika ya, memberikan informasi waktu tersisa ke frontend.

File: `app/Http/Middleware/CheckWorkSession.php`

```php
namespace App\Http\Middleware;

use App\Models\WorkSession;
use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CheckWorkSession
{
    public function handle(Request $request, Closure $next)
    {
        $actorUserId = $request->input('actor_user_id');

        if (!$actorUserId) {
            return $next($request);
        }

        // Cari WorkSession untuk user ini hari ini
        $session = WorkSession::forToday()->forUser($actorUserId)->first();

        if (!$session) {
            // Tidak ada jadwal hari ini — tidak ada popup
            return $next($request);
        }

        $minutesUntilEnd = $session->minutesUntilEnd();

        // Share data ke frontend via Inertia
        Inertia::share('workSession', [
            'has_session'       => true,
            'end_time'          => $session->end_time->format('H:i'),
            'minutes_remaining' => $minutesUntilEnd,
            'show_warning'      => $minutesUntilEnd !== null && $minutesUntilEnd <= 5,
            'is_ended'          => $minutesUntilEnd !== null && $minutesUntilEnd <= 0,
        ]);

        return $next($request);
    }
}
```

### Registrasi Middleware

```php
// bootstrap/app.php

->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'check.work_session' => \App\Http\Middleware\CheckWorkSession::class,
    ]);
})
```

### Penerapan di Route

```php
Route::prefix('cashier')->middleware([
    'auth',
    'resolve.actor',
    'check.work_session',
])->group(function () {
    // ...
});

Route::prefix('kitchen')->middleware([
    'auth',
    'resolve.actor',
    'check.work_session',
])->group(function () {
    // ...
});
```

---

## Komponen Popup Warning

### Logika di Frontend

Komponen React `WorkSessionWarning` membaca `workSession` dari props Inertia dan menampilkan popup sesuai kondisi:

```jsx
// resources/js/Components/Common/WorkSessionWarning.jsx

import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { Clock, LogOut } from 'lucide-react';

export default function WorkSessionWarning() {
    const { workSession } = usePage().props;
    const [showWarning, setShowWarning] = useState(false);
    const [showExpired, setShowExpired] = useState(false);

    useEffect(() => {
        if (!workSession?.has_session) return;

        const interval = setInterval(() => {
            const remaining = workSession.minutes_remaining;

            if (remaining !== null && remaining <= 5 && remaining > 0) {
                setShowWarning(true);
            } else if (remaining !== null && remaining <= 0) {
                setShowWarning(false);
                setShowExpired(true);
            }
        }, 10000); // cek setiap 10 detik

        return () => clearInterval(interval);
    }, [workSession]);

    const handleExtendSession = () => {
        setShowWarning(false);
        setShowExpired(false);
    };

    const handleLogout = () => {
        // Submit form logout via Inertia
        document.getElementById('logout-form')?.submit();
    };

    if (showWarning && !showExpired) {
        return (
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div className="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
                    <div className="flex items-center gap-3 mb-4">
                        <Clock className="w-6 h-6 text-yellow-500" />
                        <h3 className="text-lg font-semibold text-gray-900">
                            Sesi Hampir Berakhir
                        </h3>
                    </div>
                    <p className="text-sm text-gray-600 mb-6">
                        Sesi kerja Anda akan berakhir dalam{' '}
                        <strong>{workSession.minutes_remaining} menit</strong>{' '}
                        pada pukul {workSession.end_time}. Pastikan semua pesanan sudah diproses.
                    </p>
                    <div className="flex gap-3">
                        <button
                            onClick={handleExtendSession}
                            className="flex-1 py-2.5 px-4 rounded-lg bg-blue-600
                                       text-white text-sm font-semibold hover:bg-blue-700"
                        >
                            Lanjutkan Bekerja
                        </button>
                        <button
                            onClick={handleLogout}
                            className="flex-1 py-2.5 px-4 rounded-lg border border-red-300
                                       text-red-600 text-sm font-semibold hover:bg-red-50"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    if (showExpired) {
        return (
            <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                <div className="bg-white rounded-xl p-6 max-w-sm w-full mx-4 shadow-xl">
                    <div className="flex items-center gap-3 mb-4">
                        <LogOut className="w-6 h-6 text-red-500" />
                        <h3 className="text-lg font-semibold text-gray-900">
                            Sesi Berakhir
                        </h3>
                    </div>
                    <p className="text-sm text-gray-600 mb-6">
                        Jam kerja Anda untuk hari ini telah berakhir pada pukul{' '}
                        {workSession.end_time}. Anda tetap dapat melanjutkan bekerja
                        tanpa sesi, atau logout.
                    </p>
                    <div className="flex gap-3">
                        <button
                            onClick={handleExtendSession}
                            className="flex-1 py-2.5 px-4 rounded-lg bg-gray-100
                                       text-gray-700 text-sm font-semibold hover:bg-gray-200"
                        >
                            Lanjut Tanpa Sesi
                        </button>
                        <button
                            onClick={handleLogout}
                            className="flex-1 py-2.5 px-4 rounded-lg bg-red-600
                                       text-white text-sm font-semibold hover:bg-red-700"
                        >
                            Logout
                        </button>
                    </div>
                </div>
            </div>
        );
    }

    return null; // Tidak ada popup jika tidak ada kondisi khusus
}
```

### Penempatan di Layout

Komponen dipasang di layout kasir dan dapur:

```jsx
// resources/js/Layouts/CashierLayout.jsx

import WorkSessionWarning from '@/Components/Common/WorkSessionWarning';

export default function CashierLayout({ children }) {
    return (
        <div className="flex h-screen">
            <Sidebar />
            <main className="flex-1 overflow-auto bg-gray-50 p-6">
                {children}
            </main>
            <WorkSessionWarning />
        </div>
    );
}
```

### ⚠️ Popup TIDAK Mengganggu State

Poin penting: popup peringatan dan popup berakhir **tidak mengganggu** state aplikasi yang sedang aktif:

- **Cart/keranjang** tetap utuh — tidak dikosongkan
- **Form yang sedang diisi** tetap mempertahankan isinya
- **Modal yang sedang terbuka** tidak ditutup
- Popup hanya overlay di atas konten; user bisa menutupnya dan melanjutkan

---

## CRUD via Filament Resource

WorkSession dikelola oleh admin melalui panel Filament.

### WorkSessionResource

```php
// app/Filament/Resources/WorkSessionResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkSessionResource\Pages;
use App\Models\WorkSession;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class WorkSessionResource extends Resource
{
    protected static ?string $model = WorkSession::class;
    protected static ?string $navigationIcon = 'heroicon-o-clock';
    protected static ?string $navigationGroup = 'Operasional';
    protected static ?string $label = 'Sesi Kerja';
    protected static ?string $pluralLabel = 'Sesi Kerja';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('user_id')
                ->label('Staff')
                ->options(
                    User::whereIn('role', ['cashier', 'kitchen'])
                        ->pluck('name', 'id')
                )
                ->searchable()
                ->required(),

            Forms\Components\CheckboxList::make('day_of_week')
                ->label('Hari Kerja')
                ->options([
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                    7 => 'Minggu',
                ])
                ->columns(4)
                ->required(),

            Forms\Components\TimePicker::make('start_time')
                ->label('Jam Mulai')
                ->seconds(false)
                ->required(),

            Forms\Components\TimePicker::make('end_time')
                ->label('Jam Selesai')
                ->seconds(false)
                ->after('start_time')
                ->required(),

            Forms\Components\Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Staff')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('day_of_week_label')
                    ->label('Hari Kerja'),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListWorkSessions::route('/'),
            'create' => Pages\CreateWorkSession::route('/create'),
            'edit'   => Pages\EditWorkSession::route('/{record}/edit'),
        ];
    }
}
```

### Widget: Staff Aktif Real-Time

Admin dapat melihat daftar staff yang seharusnya bekerja saat ini melalui widget di dashboard Filament:

```php
// app/Filament/Widgets/ActiveStaffOverview.php

namespace App\Filament\Widgets;

use App\Models\WorkSession;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class ActiveStaffOverview extends BaseWidget
{
    protected static ?string $heading = 'Staff yang Seharusnya Aktif';
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $today = now()->dayOfWeek;

        return $table
            ->query(
                WorkSession::query()
                    ->whereJsonContains('day_of_week', $today)
                    ->where('is_active', true)
                    ->with('user')
            )
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Staff'),

                Tables\Columns\TextColumn::make('user.role')
                    ->label('Role')
                    ->badge(),

                Tables\Columns\TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i'),

                Tables\Columns\TextColumn::make('minutes_until_end')
                    ->label('Sisa Waktu')
                    ->state(function (WorkSession $record): string {
                        $minutes = $record->minutesUntilEnd();

                        if ($minutes === null) {
                            return '-';
                        }

                        if ($minutes <= 0) {
                            return 'Selesai';
                        }

                        $hours = floor($minutes / 60);
                        $mins = $minutes % 60;

                        return $hours > 0
                            ? "{$hours}j {$mins}m"
                            : "{$mins}m";
                    }),
            ]);
    }
}
```

---

## Edge Cases

### 1. Tidak Ada Jadwal Hari Ini

Jika staff tidak memiliki WorkSession untuk hari ini:
- Middleware tidak meng-share `workSession` ke frontend
- Komponen `WorkSessionWarning` menerima `null` → tidak menampilkan popup apapun
- Staff tetap bisa login dan bekerja normal (sesi kerja bersifat opsional)

### 2. Hari Libur

Hari Minggu (day_of_week = 7) biasanya tidak ada staff yang dijadwalkan. Namun jika admin membuat jadwal khusus untuk hari Minggu, popup akan tetap muncul. Sistem tidak memiliki konsep "hari libur nasional" — itu menjadi tanggung jawab admin untuk tidak menjadwalkan staff pada hari libur.

### 3. Shift Malam (Melewati Tengah Malam)

Shift yang melewati tengah malam (misalnya 22:00 — 06:00) membutuhkan penanganan khusus. Saat ini sistem mengasumsikan `start_time` < `end_time` dalam hari yang sama.

Untuk shift malam, admin dapat membuat dua WorkSession:
- `day_of_week: [5], start_time: 22:00, end_time: 23:59`
- `day_of_week: [6], start_time: 00:00, end_time: 06:00`

> 💡 Fitur shift lintas hari akan dievaluasi di fase berikutnya jika diperlukan.

### 4. Perubahan Jadwal di Tengah Shift

Jika admin mengubah jadwal saat staff sedang bekerja:
- Middleware membaca data WorkSession real-time dari database setiap request
- Perubahan langsung terlihat di request berikutnya
- Popup akan menyesuaikan dengan jadwal baru

### 5. Staff Bekerja di Luar Jadwal

Staff tetap bisa login dan bekerja meskipun tidak memiliki WorkSession. Middleware hanya menambahkan informasi jadwal ke response; tidak memblokir akses. Ini disengaja untuk fleksibilitas operasional (lembur, penggantian shift mendadak, dll).

### 6. Multiple WorkSession untuk Satu Staff

Secara teknis, satu staff bisa memiliki beberapa WorkSession (misalnya shift pagi dan shift malam di hari yang sama). Middleware akan mengambil **semua** session yang cocok dan meng-share ke frontend. Komponen popup akan menampilkan session dengan `end_time` terdekat.

---

## Perbedaan dengan CashierSession (Legacy)

Sistem saat ini memiliki model `CashierSession` (dengan kolom `shift_start`, `shift_end`, `total_sales`, `total_transactions`) yang digunakan untuk tracking shift kasir. Perbedaan utama:

| Aspek | CashierSession (legacy) | WorkSession (baru) |
|---|---|---|
| **Tujuan** | Mencatat shift yang sudah berjalan | Menjadwalkan shift yang akan datang |
| **Dibuat oleh** | Sistem (otomatis saat login) | Admin (via Filament) |
| **Data** | `shift_start`, `shift_end`, total penjualan | `day_of_week`, `start_time`, `end_time` |
| **Sifat** | Historis (recording) | Proaktif (scheduling + notifikasi) |
| **Cakupan** | Hanya kasir | Kasir + kitchen |

`CashierSession` akan tetap dipertahankan untuk backward compatibility data historis. Ke depan, data shift aktual akan direkam di model baru `StaffAttendance` (fase berikutnya).
