<?php

namespace App\Filament\Pages;

use App\Models\Session;
use Filament\Pages\Page;
use Filament\Forms\Form;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\DeleteAction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Sessions extends Page implements HasForms, HasTable
{
    use InteractsWithForms, InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-computer-desktop';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 5;
    protected static string $view = 'filament.pages.sessions';

    /*
    |----------------------------------------------------------------------
    | Access Control
    |----------------------------------------------------------------------
    | Determines if the authenticated user has permission to access this page.
    */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->can('profile.view');
    }

    /*
    |----------------------------------------------------------------------
    | Table Configuration
    |----------------------------------------------------------------------
    | Defines the columns and actions for the sessions table.
    | Displays user device name, IP address, and last activity.
    */
    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user_agent')
                    ->label('Device Name')
                    ->sortable()
                    ->limit(50),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->sortable(),

                TextColumn::make('last_activity')
                    ->label('Last Activity')
                    ->dateTime()
                    ->sortable(),
            ])
            ->query(Session::query()->where('user_id', Auth::id()))
            ->defaultSort('last_activity', 'desc')
            ->actions([
                DeleteAction::make()
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function (Session $record) {
                        if ($record->id === session()->getId()) {
                            Auth::logout();
                            session()->invalidate();
                            session()->regenerateToken();
                            $this->redirect('/login');
                        }
                        DB::table('sessions')->where('id', $record->id)->delete();
                        \Log::info("Session ID [{$record->id}] deleted.");
                    }),
            ]);
    }
}
