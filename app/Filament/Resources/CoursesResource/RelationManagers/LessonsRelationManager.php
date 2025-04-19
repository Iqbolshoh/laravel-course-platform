<?php

namespace App\Filament\Resources\CoursesResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->label('Lesson Title')
                    ->required()
                    ->maxLength(255),

                TextInput::make('video_url')
                    ->label('YouTube Video URL')
                    ->url()
                    ->placeholder('https://www.youtube.com/watch?v=XXXX')
                    ->helperText('Faqat YouTube videosining asosiy URL qismini kiriting.')
                    ->dehydrateStateUsing(function ($state) {
                        parse_str(parse_url($state, PHP_URL_QUERY), $query);
                        return isset($query['v']) ? 'https://www.youtube.com/watch?v=' . $query['v'] : $state;
                    }),

                RichEditor::make('content')
                    ->label('Lesson Content')
                    ->maxLength(65535),

                TextInput::make('order')
                    ->label('Order')
                    ->numeric()
                    ->default(1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Title')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('order')
                    ->label('Order')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Created At'),
            ])
            ->filters([])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
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
}