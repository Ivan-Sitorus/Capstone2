<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportHeaderTemplateResource\Pages;
use App\Models\ReportHeaderTemplate;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ReportHeaderTemplateResource extends Resource
{
    protected static ?string $model = ReportHeaderTemplate::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static string|\UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Template Header Laporan';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Template')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama Template')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('entity_name')
                        ->label('Nama Entitas')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('address')
                        ->label('Alamat')
                        ->nullable()
                        ->maxLength(255),
                    TextInput::make('phone')
                        ->label('Telepon')
                        ->nullable()
                        ->maxLength(255),
                    Textarea::make('additional_info')
                        ->label('Info Tambahan (JSON)')
                        ->nullable()
                        ->rows(4)
                        ->helperText('Format JSON, contoh: {"npwp": "12.345.678.9-012.000", "website": "w9cafe.com"}')
                        ->columnSpanFull(),
                    Toggle::make('is_default')
                        ->label('Jadikan Default')
                        ->helperText('Hanya satu template yang bisa menjadi default')
                        ->default(false),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Template')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('entity_name')
                    ->label('Nama Entitas')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_default')
                    ->label('Default')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d M Y')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportHeaderTemplates::route('/'),
            'create' => Pages\CreateReportHeaderTemplate::route('/create'),
            'edit' => Pages\EditReportHeaderTemplate::route('/{record}/edit'),
        ];
    }
}
