<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UnitsRelationManager extends RelationManager
{
    protected static string $relationship = 'units';

    protected static ?string $title = 'Course Units';

    protected static ?string $icon = 'heroicon-o-rectangle-stack';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                
                Forms\Components\TextInput::make('order')
                    ->numeric()
                    ->default(fn () => $this->getOwnerRecord()->units()->max('order') + 1)
                    ->required()
                    ->minValue(1)
                    ->helperText('Order in which this unit appears in the course'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('lessons'))
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->width(80),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('lessons_count')
                    ->label('Lessons')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['unitable_id'] = $this->getOwnerRecord()->id;
                        $data['unitable_type'] = get_class($this->getOwnerRecord());
                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                
                Tables\Actions\Action::make('manage_lessons')
                    ->label('Manage Lessons')
                    ->icon('heroicon-o-academic-cap')
                    ->color('info')
                    ->url(fn ($record) => static::getUrl('lessons', [
                        'record' => $this->getOwnerRecord(),
                        'unit' => $record,
                    ]))
                    ->visible(fn () => false), // We'll use nested relation manager instead
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}