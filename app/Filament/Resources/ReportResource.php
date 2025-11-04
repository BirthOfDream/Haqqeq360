<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportResource\Pages;
use App\Models\Report;
use App\Models\User;
use App\Models\Bootcamp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\ProgressBarColumn;
use App\Models\Course;



class ReportResource extends Resource
{
    protected static ?string $model = Report::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    protected static ?string $navigationGroup = 'Training';

    protected static ?int $navigationSort = 3;

   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Report Information')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Student')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('enrollable_type')
                        ->label('Program Type')
                        ->options([
                            'App\\Models\\Bootcamp' => 'Bootcamp',
                            'App\\Models\\Course' => 'Course',
                        ])
                        ->required()
                        ->live()
                        ->afterStateUpdated(fn (Forms\Set $set) => $set('enrollable_id', null)),

                    Forms\Components\Select::make('enrollable_id')
                        ->label('Select Program')
                        ->options(function (Forms\Get $get) {
                            $type = $get('enrollable_type');
                            if (!$type) return [];
                            
                            if ($type === 'App\\Models\\Bootcamp') {
                                return Bootcamp::pluck('title', 'id')->toArray();
                            }
                            
                            if ($type === 'App\\Models\\Course') {
                                return Course::pluck('title', 'id')->toArray();
                            }
                            
                            return [];
                        })
                        ->searchable()
                        ->required()
                        ->disabled(fn (Forms\Get $get) => !$get('enrollable_type'))
                        ->helperText(fn (Forms\Get $get) => 
                            !$get('enrollable_type') 
                                ? 'Please select a program type first' 
                                : null
                        ),

                    Forms\Components\TextInput::make('completion_rate')
                        ->label('Completion Rate (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->suffix('%')
                        ->step(0.01)
                        ->required(),

                    Forms\Components\TextInput::make('grade_avg')
                        ->label('Average Grade (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->suffix('%')
                        ->step(0.01),

                    Forms\Components\Textarea::make('feedback_summary')
                        ->label('Feedback Summary')
                        ->rows(4)
                        ->columnSpanFull(),
                ])
                ->columns(2),
        ]);
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Student')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('enrollable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('enrollable_id')
                    ->label('Program')
                    ->getStateUsing(function ($record) {
                        $model = app($record->enrollable_type);
                        $program = $model->find($record->enrollable_id);
                        return $program?->title ?? 'N/A';
                    })
                    ->limit(30),

                Tables\Columns\TextColumn::make('completion_rate')
                    ->label('Completion')
                    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . '%')
                    ->color(fn (string $state): string => match (true) {
                        $state >= 90 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('completion_rate')
    ->label('Completion')
    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . '%')
    ->badge()
    ->color(fn (string $state): string => match (true) {
        $state >= 90 => 'success',
        $state >= 75 => 'info',
        $state >= 50 => 'warning',
        default => 'danger',
    })
    ->sortable(),

                Tables\Columns\TextColumn::make('grade_avg')
                    ->label('Avg Grade')
                    ->formatStateUsing(fn ($state): string => $state ? number_format($state, 2) . '%' : 'N/A')
                    ->color(fn ($state): string => match (true) {
                        !$state => 'gray',
                        $state >= 90 => 'success',
                        $state >= 80 => 'info',
                        $state >= 70 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('feedback_summary')
                    ->label('Feedback')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->feedback_summary)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Generated At')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('enrollable_type')
                    ->label('Type')
                    ->options([
                        'App\\Models\\Bootcamp' => 'Bootcamp',
                        'App\\Models\\Course' => 'Course',
                    ])
                    ->multiple(),

                Tables\Filters\SelectFilter::make('user')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Student'),

                Tables\Filters\Filter::make('completion_rate')
                    ->form([
                        Forms\Components\TextInput::make('completion_from')
                            ->label('Completion From (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('completion_to')
                            ->label('Completion To (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['completion_from'],
                                fn (Builder $query, $value): Builder => $query->where('completion_rate', '>=', $value),
                            )
                            ->when(
                                $data['completion_to'],
                                fn (Builder $query, $value): Builder => $query->where('completion_rate', '<=', $value),
                            );
                    }),

                Tables\Filters\Filter::make('grade_avg')
                    ->form([
                        Forms\Components\TextInput::make('grade_from')
                            ->label('Grade From (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('grade_to')
                            ->label('Grade To (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['grade_from'],
                                fn (Builder $query, $value): Builder => $query->where('grade_avg', '>=', $value),
                            )
                            ->when(
                                $data['grade_to'],
                                fn (Builder $query, $value): Builder => $query->where('grade_avg', '<=', $value),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        // Implement PDF download logic here
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('exportReports')
                        ->label('Export Selected')
                        ->icon('heroicon-o-document-arrow-down')
                        ->color('info')
                        ->action(function ($records) {
                            // Implement bulk export logic here
                        }),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Report Overview')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Student Name')
                            ->icon('heroicon-o-user')
                            ->size(Infolists\Components\TextEntry\TextEntrySize::Large),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Student Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('enrollable_type')
                            ->label('Program Type')
                            ->formatStateUsing(fn (string $state): string => class_basename($state))
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('enrollable_id')
                            ->label('Program Name')
                            ->getStateUsing(function ($record) {
                                $model = app($record->enrollable_type);
                                $program = $model->find($record->enrollable_id);
                                return $program?->title ?? 'N/A';
                            }),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Performance Metrics')
                    ->schema([
                        Infolists\Components\TextEntry::make('completion_rate')
                            ->label('Completion Rate')
                            ->formatStateUsing(fn (string $state): string => number_format($state, 2) . '%')
                            ->badge()
                            ->color(fn (string $state): string => match (true) {
                                $state >= 90 => 'success',
                                $state >= 75 => 'info',
                                $state >= 50 => 'warning',
                                default => 'danger',
                            })
                            ->icon('heroicon-o-chart-bar'),

                        Infolists\Components\TextEntry::make('grade_avg')
                            ->label('Average Grade')
                            ->formatStateUsing(fn ($state): string => $state ? number_format($state, 2) . '%' : 'N/A')
                            ->badge()
                            ->color(fn ($state): string => match (true) {
                                !$state => 'gray',
                                $state >= 90 => 'success',
                                $state >= 80 => 'info',
                                $state >= 70 => 'warning',
                                default => 'danger',
                            })
                            ->icon('heroicon-o-academic-cap'),

                        Infolists\Components\TextEntry::make('performance_level')
                            ->label('Performance Level')
                            ->getStateUsing(function ($record) {
                                if (!$record->grade_avg) return 'Not Graded';
                                return match (true) {
                                    $record->grade_avg >= 90 => 'Excellent',
                                    $record->grade_avg >= 80 => 'Very Good',
                                    $record->grade_avg >= 70 => 'Good',
                                    $record->grade_avg >= 60 => 'Satisfactory',
                                    default => 'Needs Improvement',
                                };
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Excellent' => 'success',
                                'Very Good' => 'info',
                                'Good' => 'primary',
                                'Satisfactory' => 'warning',
                                'Needs Improvement' => 'danger',
                                default => 'gray',
                            }),
                    ])
                    ->columns(3),

                Infolists\Components\Section::make('Feedback')
                    ->schema([
                        Infolists\Components\TextEntry::make('feedback_summary')
                            ->label('Summary')
                            ->columnSpanFull()
                            ->prose()
                            ->placeholder('No feedback provided'),
                    ]),

                Infolists\Components\Section::make('Report Metadata')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Generated At')
                            ->dateTime('F d, Y H:i'),

                        Infolists\Components\TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F d, Y H:i'),
                    ])
                    ->columns(2)
                    ->collapsed(),
            ]);
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
            'index' => Pages\ListReports::route('/'),
            'create' => Pages\CreateReport::route('/create'),
            'view' => Pages\ViewReport::route('/{record}'),
            'edit' => Pages\EditReport::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'user.email', 'feedback_summary'];
    }
}