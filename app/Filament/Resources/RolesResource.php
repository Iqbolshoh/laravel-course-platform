<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RolesResource\Pages;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class RolesResource extends Resource
{
    protected static ?string $model = Role::class;
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.roles';
    protected static ?string $navigationGroup = 'Roles & Users';
    protected static ?int $navigationSort = 2;

    /*
    |--------------------------------------------------------------------------------
    | Role-Based Access Verification
    |--------------------------------------------------------------------------------
    | Ensures that only authenticated users with the 'superadmin' role can access 
    | this resource, restricting unauthorized access to role management features.
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
    | Defines the form structure for creating and editing roles, including fields 
    | for role name and a multi-select for assigning grouped permissions.
    */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->label('Role Name')
                    ->rule('regex:/^[a-zA-Z0-9-]+$/')
                    ->helperText('Only letters (a-z, A-Z), numbers (0-9), and dashes (-) are allowed.'),

                Select::make('permissions')
                    ->multiple()
                    ->relationship('permissions', 'name')
                    ->preload()
                    ->label('Permissions')
                    ->required()
                    ->minItems(1)
                    ->options(function () {
                        return Permission::all()
                            ->groupBy(function ($permission) {
                                return explode('.', $permission->name)[0];
                            })
                            ->mapWithKeys(function ($group, $key) {
                                return [
                                    ucfirst($key) => $group->pluck('name', 'id'),
                                ];
                            });
                    }),
            ]);
    }

    /*
    |--------------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------------
    | Configures the table layout for displaying roles, including columns for ID, 
    | role name, and permissions, with actions for editing and deleting non-superadmin roles.
    */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->sortable()
                    ->label('ID'),

                TextColumn::make('name')
                    ->sortable()
                    ->label('Role Name'),

                TextColumn::make('permissions.name')
                    ->label('Permissions')
                    ->badge()
                    ->separator(', '),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->name !== 'superadmin'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->name !== 'superadmin'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
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
    | pages for role management.
    */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRoles::route('/create'),
            'edit' => Pages\EditRoles::route('/{record}/edit'),
        ];
    }
}