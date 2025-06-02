<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CertificatesResource\Pages;
use App\Filament\Resources\CertificatesResource\RelationManagers;
use App\Models\Certificate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CertificatesResource extends Resource
{
    protected static ?string $model = Certificate::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-check';
    protected static ?string $navigationGroup = 'Documents';
    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('certificate.view') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('User')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required()
                    ->preload(),

                Select::make('course_id')
                    ->label('Course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->required()
                    ->preload(),

                TextInput::make('certificate_number')
                    ->label('Certificate Number')
                    ->default(fn() => Certificate::generateCertificateNumber())
                    ->unique(ignoreRecord: true)
                    ->required()
                    ->maxLength(255),

                DatePicker::make('issued_at')
                    ->label('Issued At')
                    ->default(today())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('certificate_number')
                    ->label('Certificate Number')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('course.title')
                    ->label('Course')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('issued_at')
                    ->label('Issued At')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('course')
                    ->relationship('course', 'title')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([Tables\Actions\ViewAction::make()])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCertificates::route('/') 
        ];
    }
}