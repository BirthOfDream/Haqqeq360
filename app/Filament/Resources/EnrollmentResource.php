<?php
// ============================================================================
// File: app/Filament/Resources/EnrollmentResource.php
// ============================================================================

namespace App\Filament\Resources;

use App\Filament\Resources\EnrollmentResource\Pages;
use App\Models\Enrollment;
use App\Models\User;
use App\Models\Bootcamp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Columns\ProgressBarColumn;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Course;

class EnrollmentResource extends Resource
{
    protected static ?string $model = Enrollment::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-plus';

    protected static ?string $navigationGroup = 'Training';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Section::make('Enrollment Information')
                ->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Student')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->required()
                                ->maxLength(255),
                            Forms\Components\TextInput::make('password')
                                ->password()
                                ->required()
                                ->maxLength(255),
                        ]),

                    Forms\Components\Select::make('enrollable_type')
                        ->label('Enrollment Type')
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
                                ? 'Please select an enrollment type first' 
                                : null
                        ),

                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'active' => 'Active',
                            'completed' => 'Completed',
                        ])
                        ->required()
                        ->default('pending')
                        ->native(false),

                    Forms\Components\TextInput::make('progress')
                        ->label('Progress (%)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(100)
                        ->default(0)
                        ->suffix('%')
                        ->step(0.01),
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

                Tables\Columns\TextColumn::make('enrollable')
                    ->label('Program')
                    ->getStateUsing(function ($record) {
                        return $record->enrollable?->title ?? 'N/A';
                    })
                    ->searchable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'active',
                        'primary' => 'completed',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . '%')
                    ->color(fn (string $state): string => match (true) {
                        $state >= 75 => 'success',
                        $state >= 50 => 'info',
                        $state >= 25 => 'warning',
                        default => 'danger',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
    ->label('Progress')
    ->formatStateUsing(fn (string $state): string => number_format($state, 2) . '%')
    ->badge()
    ->color(fn (string $state): string => match (true) {
        $state >= 75 => 'success',
        $state >= 50 => 'info',
        $state >= 25 => 'warning',
        default => 'danger',
    })
    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Enrolled At')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'completed' => 'Completed',
                    ])
                    ->multiple(),

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

                Tables\Filters\Filter::make('progress')
                    ->form([
                        Forms\Components\TextInput::make('progress_from')
                            ->label('Progress From (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                        Forms\Components\TextInput::make('progress_to')
                            ->label('Progress To (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['progress_from'],
                                fn (Builder $query, $value): Builder => $query->where('progress', '>=', $value),
                            )
                            ->when(
                                $data['progress_to'],
                                fn (Builder $query, $value): Builder => $query->where('progress', '<=', $value),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('markAsActive')
                        ->label('Mark as Active')
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'active'])),
                    Tables\Actions\BulkAction::make('markAsCompleted')
                        ->label('Mark as Completed')
                        ->icon('heroicon-o-check-badge')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'completed', 'progress' => 100])),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Enrollment Details')
                    ->schema([
                        Infolists\Components\TextEntry::make('user.name')
                            ->label('Student Name')
                            ->icon('heroicon-o-user'),

                        Infolists\Components\TextEntry::make('user.email')
                            ->label('Student Email')
                            ->icon('heroicon-o-envelope')
                            ->copyable(),

                        Infolists\Components\TextEntry::make('enrollable_type')
                            ->label('Enrollment Type')
                            ->formatStateUsing(fn (string $state): string => class_basename($state))
                            ->badge()
                            ->color('info'),

                        Infolists\Components\TextEntry::make('enrollable')
                            ->label('Program Name')
                            ->getStateUsing(function ($record) {
                                return $record->enrollable?->title ?? 'N/A';
                            }),

                        Infolists\Components\TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'pending' => 'warning',
                                'active' => 'success',
                                'completed' => 'primary',
                            }),

                        Infolists\Components\TextEntry::make('progress')
                            ->label('Progress')
                            ->formatStateUsing(fn (string $state): string => number_format($state, 2) . '%')
                            ->color(fn (string $state): string => match (true) {
                                $state >= 75 => 'success',
                                $state >= 50 => 'info',
                                $state >= 25 => 'warning',
                                default => 'danger',
                            }),
                    ])
                    ->columns(2),

                Infolists\Components\Section::make('Timeline')
                    ->schema([
                        Infolists\Components\TextEntry::make('created_at')
                            ->label('Enrolled At')
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
            'index' => Pages\ListEnrollments::route('/'),
            'create' => Pages\CreateEnrollment::route('/create'),
            'view' => Pages\ViewEnrollment::route('/{record}'),
            'edit' => Pages\EditEnrollment::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'active')->count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['user.name', 'user.email'];
    }
}
