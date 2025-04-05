<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Pages\Actions\Action;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Helpers\Utils;

class Roles extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static string $view = 'filament.pages.roles';
    protected static ?string $navigationGroup = 'Roles & Users';
    protected static ?int $navigationSort = 2;

    /*
    |----------------------------------------------------------------------
    | Access Control Check
    |----------------------------------------------------------------------
    | Checks if the logged-in user has permission to view, create, edit, or delete roles
    */
    public static function canAccess(string $permission = 'view'): bool
    {
        if (!$user = auth()->user())
            return false;

        return match ($permission) {
            'view' => $user->can('role.view'),
            'create' => $user->can('role.create'),
            'edit' => $user->can('role.edit'),
            'delete' => $user->can('role.delete'),
            default => false,
        };
    }

    /*
    |----------------------------------------------------------------------
    | Get Table Query
    |----------------------------------------------------------------------
    | Fetches the roles data from the database with their associated permissions
    */
    protected function getTableQuery()
    {
        return Role::query()->with('permissions');
    }

    /*
    |----------------------------------------------------------------------
    | Define Table Columns
    |----------------------------------------------------------------------
    | Sets up the columns to display role ID, name, permissions, and creation date
    */
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('id')->label('ID')->sortable(),
            TextColumn::make('name')->label('Role Name')->sortable()->searchable(),
            TextColumn::make('permissions')
                ->label('Permissions')
                ->getStateUsing(fn(Role $role) => $role->permissions->pluck('name')->implode(', '))
                ->wrap()
                ->sortable()
                ->searchable(),
            TextColumn::make('created_at')->label('Created At')->sortable()->dateTime(),
        ];
    }

    /*
    |----------------------------------------------------------------------
    | Define Form Schema
    |----------------------------------------------------------------------
    | Creates the form structure for adding or editing roles with grouped permissions
    */
    protected function getFormSchema(): array
    {
        $permissions = Permission::all()
            ->pluck('name')
            ->groupBy(fn($perm) => explode('.', $perm, 2)[0])
            ->map(fn($group) => $group->mapWithKeys(fn($perm) => [$perm => ucfirst(explode('.', $perm)[1])])->all())
            ->all();

        $schema = [
            TextInput::make('roleName')
                ->label('Role Name')
                ->required()
                ->regex('/^[a-zA-Z0-9_]+$/')
                ->maxLength(255)
                ->placeholder('e.g., user_role')
                ->unique(Role::class, 'name', ignoreRecord: true)
                ->validationMessages([
                    'unique' => 'This role name already exists.',
                    'regex' => 'Only letters (A-Z), numbers (0-9), and underscores (_) allowed.',
                ]),
        ];

        foreach ($permissions as $group => $perms) {
            $schema[] = Section::make(ucfirst($group))
                ->schema([
                    CheckboxList::make('permissions')
                        ->options($perms)
                        ->columns(min(4, count($perms)))
                        ->bulkToggleable()
                ])
                ->collapsible()
                ->compact();
        }
        return $schema;
    }

    /*
    |----------------------------------------------------------------------
    | Define Table Actions
    |----------------------------------------------------------------------
    | Sets up edit and delete actions for each role in the table
    */
    protected function getTableActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn(Role $role) => $this->canAccess('edit') && $role->name !== 'superadmin')
                ->form(fn(Form $form) => $form->schema($this->getFormSchema()))
                ->fillForm(fn(Role $role): array => [
                    'roleName' => $role->name,
                    'permissions' => $role->permissions->pluck('name')->toArray(),
                ])
                ->action(fn(Role $role, array $data) => $this->updateRole($role, $data))
                ->color('primary')
                ->modalHeading('Edit Role')
                ->modalSubmitActionLabel('Update Role'),

            DeleteAction::make()
                ->visible(fn(Role $role) => $this->canAccess('delete') && $role->name !== 'superadmin')
                ->action(function (Role $role) {
                    try {
                        $roleName = $role->name;
                        $role->delete();
                        Utils::notify('Success', "Role '{$roleName}' deleted successfully!", 'success');
                    } catch (\Exception $e) {
                        Utils::notify('Error', 'Delete failed: ' . $e->getMessage(), 'danger');
                    }
                })
        ];
    }

    /*
    |----------------------------------------------------------------------
    | Define Bulk Table Actions
    |----------------------------------------------------------------------
    | Enables bulk deletion of roles, excluding the superadmin role
    */
    protected function getTableBulkActions(): array
    {
        return $this->canAccess('delete')
            ? [
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->action(function ($records) {
                            $records = collect($records);
                            foreach ($records as $role) {
                                if ($role->name === 'superadmin') {
                                    Utils::notify('Error', 'You cannot delete the Superadmin role!', 'danger');
                                    return;
                                }
                            }

                            foreach ($records as $role) {
                                if ($role->name !== 'superadmin') {
                                    $role->delete();
                                }
                            }

                            Utils::notify('Success', 'Selected roles have been deleted successfully!', 'success');
                        })
                ])
            ]
            : [];
    }

    /*
    |----------------------------------------------------------------------
    | Define Header Actions
    |----------------------------------------------------------------------
    | Adds a "Create Role" button to the page header if the user has permission
    */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label('Create Role')
                ->icon('heroicon-o-plus-circle')
                ->form($this->getFormSchema())
                ->action(fn(array $data) => $this->createRole($data))
                ->color('primary')
                ->visible(fn() => $this->canAccess('create')),
        ];
    }

    /*
    |----------------------------------------------------------------------
    | Create New Role
    |----------------------------------------------------------------------
    | Creates a new role with the provided name and permissions
    */
    protected function createRole(array $data): void
    {
        if (empty($data['permissions'])) {
            Utils::notify('No Permissions', 'Select at least one permission.', 'warning');
            return;
        }

        try {
            $role = Role::create(['name' => $data['roleName']]);
            $role->syncPermissions($data['permissions']);
            Utils::notify('Success', "Role '{$role->name}' created successfully!", 'success');
        } catch (\Exception $e) {
            Utils::notify('Error', 'Creation failed: ' . $e->getMessage(), 'danger');
        }
    }

    /*
    |----------------------------------------------------------------------
    | Update Existing Role
    |----------------------------------------------------------------------
    | Updates a role's name and permissions based on form data
    */
    protected function updateRole(Role $role, array $data): void
    {
        if (empty($data['permissions'])) {
            Utils::notify('No Permissions', 'Select at least one permission.', 'warning');
            return;
        }

        try {
            $role->update(['name' => $data['roleName']]);
            $role->syncPermissions($data['permissions']);
            Utils::notify('Success', "Role '{$role->name}' updated successfully!", 'success');
        } catch (\Exception $e) {
            Utils::notify('Error', 'Update failed: ' . $e->getMessage(), 'danger');
        }
    }
}