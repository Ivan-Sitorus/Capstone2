<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkSessionResource\Pages\CreateWorkSession;
use App\Filament\Resources\WorkSessionResource\Pages\EditWorkSession;
use App\Filament\Resources\WorkSessionResource\Pages\ListWorkSessions;
use App\Models\WorkSession;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;

class WorkSessionResource extends Resource
{
    protected static ?string $model = WorkSession::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';

    protected static string|\UnitEnum|null $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Sesi Kerja';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('user_id')
                ->label('Pengguna')
                ->relationship('user', 'name')
                ->required()
                ->searchable()
                ->preload(),
            CheckboxList::make('day_of_week')
                ->label('Hari Kerja')
                ->options([
                    0 => 'Minggu',
                    1 => 'Senin',
                    2 => 'Selasa',
                    3 => 'Rabu',
                    4 => 'Kamis',
                    5 => 'Jumat',
                    6 => 'Sabtu',
                ])
                ->required()
                ->columns(4)
                ->descriptions([
                    0 => 'Sunday',
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                ]),
            TimePicker::make('start_time')
                ->label('Jam Mulai')
                ->required()
                ->native(false),
            TimePicker::make('end_time')
                ->label('Jam Selesai')
                ->required()
                ->native(false)
                ->after('start_time'),
            Toggle::make('is_active')
                ->label('Aktif')
                ->default(true),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Pengguna')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('day_of_week')
                    ->label('Hari Kerja')
                    ->getStateUsing(function (WorkSession $record): string {
                        $dayLabels = [
                            0 => 'Min',
                            1 => 'Sen',
                            2 => 'Sel',
                            3 => 'Rab',
                            4 => 'Kam',
                            5 => 'Jum',
                            6 => 'Sab',
                        ];

                        return collect($record->day_of_week)
                            ->map(fn (int $day) => $dayLabels[$day] ?? $day)
                            ->implode(', ');
                    }),
                TextColumn::make('start_time')
                    ->label('Jam Mulai')
                    ->time('H:i')
                    ->sortable(),
                TextColumn::make('end_time')
                    ->label('Jam Selesai')
                    ->time('H:i')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('active')
                    ->label('Aktif')
                    ->query(fn ($query) => $query->active()),
                Filter::make('inactive')
                    ->label('Nonaktif')
                    ->query(fn ($query) => $query->where('is_active', false)),
            ])
            ->recordActions([
                EditAction::make()->modal()->modalWidth('2xl'),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('user_id', 'asc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkSessions::route('/'),
            'create' => CreateWorkSession::route('/create'),
            'edit' => EditWorkSession::route('/{record}/edit'),
        ];
    }
}
