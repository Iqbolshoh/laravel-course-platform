<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UsersResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class UsersResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Roles & Users';
    protected static ?int $navigationSort = 3;

    /*
    |--------------------------------------------------------------------------------
    | Role-Based Access Verification
    |--------------------------------------------------------------------------------
    | Ensures that only authenticated users with the 'superadmin' role can access 
    | this resource, restricting unauthorized access to user management features.
    */
    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && $user->hasRole('superadmin');
    }

    /*
    |--------------------------------------------------------------------------------
    | Form Configuration
    |--------------------------------------------------------------------------------
    | Defines the form structure for creating and editing users, including fields 
    | for name, email, password, and role assignment with dynamic role synchronization.
    */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                TextInput::make('password')
                    ->password()
                    ->label('Password')
                    ->required(fn(string $context) => $context === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->maxLength(255)
                    ->same('passwordConfirmation'),

                TextInput::make('passwordConfirmation')
                    ->password()
                    ->label('Confirm Password')
                    ->required(fn(string $context) => $context === 'create')
                    ->dehydrated(false),

                Select::make('role')
                    ->label('Select Role')
                    ->options(fn() => Role::pluck('name', 'name')->toArray())
                    ->required()
                    ->searchable()
                    ->reactive()
                    ->default(function ($record) {
                        return $record?->roles?->first()?->name ?? null;
                    })
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->name)
                    ->afterStateUpdated(function ($state, $record) {
                        if ($state && $record) {
                            $record->syncRoles([$state]);
                        }
                    })
                    ->afterStateHydrated(function (Select $component, $record) {
                        if ($record) {
                            $component->state($record->roles->first()?->name);
                        }
                    }),
            ]);
    }

    /*
    |--------------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------------
    | Configures the table layout for displaying users, including columns for ID, 
    | name, email, role, and creation date, with actions for editing and deleting users.
    */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),

                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->label('Name'),

                Tables\Columns\TextColumn::make('email')
                    ->sortable()
                    ->label('Email'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->sortable()
                    ->label('Role')
                    ->badge()
                    ->getStateUsing(fn($record) => $record->roles->pluck('name')->implode(', ')),

                Tables\Columns\TextColumn::make('created_at')
                    ->sortable()
                    ->label('Created')
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /*
    |--------------------------------------------------------------------------------
    | Relations Configuration
    |--------------------------------------------------------------------------------
    | Specifies related models for the resource. Currently, no relations are defined.
    */
    public static function getRelations(): array
    {
        return [];
    }

    /*
    |--------------------------------------------------------------------------------
    | Page Routes Configuration
    |--------------------------------------------------------------------------------
    | Defines the routes for the resource pages, including list, create, and edit 
    | pages for user management.
    */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUsers::route('/create'),
            'edit' => Pages\EditUsers::route('/{record}/edit'),
        ];
    }
}