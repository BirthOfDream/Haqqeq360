<?php

// app/Filament/Resources/PlanResource.php
namespace App\Filament\Resources;

use App\Filament\Resources\PlanResource\Pages;
use App\Models\Plan;
use App\Models\Workshop;
use App\Models\Bootcamp;
use App\Models\Program;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Subscriptions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Plan Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Premium Plan, Basic Package'),
                        
                        Forms\Components\Textarea::make('description')
                            ->maxLength(65535)
                            ->rows(3)
                            ->columnSpanFull(),
                        
                        Forms\Components\TextInput::make('price')
                            ->required()
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->minValue(0),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Only active plans will be visible to users'),
                    ])->columns(2),

                Forms\Components\Section::make('Plan Type')
                    ->description('Select what this plan is for')
                    ->schema([
                        Forms\Components\Select::make('planable_type')
                            ->label('Type')
                            ->options([
                                'App\Models\Workshop' => 'Workshop',
                                'App\Models\Bootcamp' => 'Bootcamp',
                                'App\Models\Program' => 'Program',
                                'App\Models\Course' => 'Course',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('planable_id', null)),
                        
                        Forms\Components\Select::make('planable_id')
                            ->label('Select Item')
                            ->options(function (Get $get) {
                                $type = $get('planable_type');
                                if (!$type) {
                                    return [];
                                }
                                
                                return match($type) {
                                    'App\Models\Workshop' => Workshop::pluck('title', 'id'),
                                    'App\Models\Bootcamp' => Bootcamp::pluck('title', 'id'),
                                    'App\Models\Program' => Program::pluck('title', 'id'),
                                    'App\Models\Course' => Course::pluck('title', 'id'),
                                    default => [],
                                };
                            })
                            ->required()
                            ->searchable()
                            ->preload()
                            ->helperText('Select the specific workshop, bootcamp, program, or course'),
                    ])->columns(2),

                Forms\Components\Section::make('Features')
                    ->schema([
                        Forms\Components\KeyValue::make('features')
                            ->label('Plan Features')
                            ->keyLabel('Feature Name')
                            ->valueLabel('Feature Value')
                            ->reorderable()
                            ->addActionLabel('Add Feature')
                            ->helperText('Add features that come with this plan (e.g., "Duration": "3 months", "Support": "24/7")'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                Tables\Columns\TextColumn::make('plan_type')
                    ->label('Type')
                    ->badge()
                    ->colors([
                        'primary' => 'Bootcamp',
                        'success' => 'Course',
                        'warning' => 'Workshop',
                        'info' => 'Program',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('planable.title')
                    ->label('Item')
                    ->searchable()
                    ->sortable()
                    ->limit(30),
                
                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->counts('subscriptions')
                    ->label('Subscriptions')
                    ->badge()
                    ->color('success')
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
                SelectFilter::make('planable_type')
                    ->label('Type')
                    ->options([
                        'App\Models\Workshop' => 'Workshop',
                        'App\Models\Bootcamp' => 'Bootcamp',
                        'App\Models\Program' => 'Program',
                        'App\Models\Course' => 'Course',
                    ])
                    ->multiple(),
                
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->boolean()
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only')
                    ->native(false),
                
                Filter::make('price')
                    ->form([
                        Forms\Components\TextInput::make('price_from')
                            ->label('Min Price')
                            ->numeric()
                            ->prefix('$'),
                        Forms\Components\TextInput::make('price_to')
                            ->label('Max Price')
                            ->numeric()
                            ->prefix('$'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['price_from'],
                                fn (Builder $query, $price): Builder => $query->where('price', '>=', $price),
                            )
                            ->when(
                                $data['price_to'],
                                fn (Builder $query, $price): Builder => $query->where('price', '<=', $price),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['price_from'] ?? null) {
                            $indicators[] = 'Min: $' . number_format($data['price_from'], 2);
                        }
                        if ($data['price_to'] ?? null) {
                            $indicators[] = 'Max: $' . number_format($data['price_to'], 2);
                        }
                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('toggle_active')
                        ->label(fn (Plan $record) => $record->is_active ? 'Deactivate' : 'Activate')
                        ->icon(fn (Plan $record) => $record->is_active ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                        ->color(fn (Plan $record) => $record->is_active ? 'danger' : 'success')
                        ->requiresConfirmation()
                        ->action(fn (Plan $record) => $record->update(['is_active' => !$record->is_active])),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('Activate Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => true])),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('Deactivate Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_active' => false])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlans::route('/'),
            'create' => Pages\CreatePlan::route('/create'),
            'edit' => Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}


?>