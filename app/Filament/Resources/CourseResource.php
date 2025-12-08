<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Course Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $context, $state, callable $set) => 
                                $context === 'create' ? $set('slug', Str::slug($state)) : null
                            ),
                        
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL-friendly version of the title'),
                        
                        Forms\Components\Select::make('instructor_id')
                            ->relationship('instructor', 'name', function ($query) {
                                return $query->whereIn('role', ['instructor', 'admin']);
                            })
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
                                    ->unique('users', 'email')
                                    ->maxLength(255),
                                Forms\Components\TextInput::make('password')
                                    ->password()
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('role')
                                    ->options([
                                        'instructor' => 'Instructor',
                                        'admin' => 'Admin',
                                    ])
                                    ->default('instructor')
                                    ->required(),
                            ]),
                        
                        Forms\Components\Textarea::make('description')
                            ->required()
                            ->rows(4)
                            ->columnSpanFull(),
                        
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->imageEditor()
                            ->directory('course-covers')
                            ->visibility('public')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Course Details')
                    ->schema([
                        Forms\Components\TextInput::make('duration_weeks')
                            ->numeric()
                            ->suffix('weeks')
                            ->minValue(1)
                            ->maxValue(52)
                            ->helperText('Course duration in weeks'),
                        
                        Forms\Components\TextInput::make('seats')
                            ->label('Available Seats')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(1000)
                            ->helperText('Total number of seats available for this course')
                            ->required(),
                        
                        Forms\Components\Select::make('level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ])
                            ->required()
                            ->default('beginner')
                            ->native(false),
                        
                        Forms\Components\Select::make('mode')
                            ->options([
                                'online' => 'Online',
                                'hybrid' => 'Hybrid',
                                'offline' => 'Offline',
                            ])
                            ->required()
                            ->default('online')
                            ->native(false),
                        
                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft')
                            ->native(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name=' . urlencode($record->title) . '&color=7F9CF5&background=EBF4FF&size=128'),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\TextColumn::make('instructor.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\BadgeColumn::make('level')
                    ->colors([
                        'success' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                    ])
                    ->icons([
                        'heroicon-o-star' => 'beginner',
                        'heroicon-o-fire' => 'intermediate',
                        'heroicon-o-rocket-launch' => 'advanced',
                    ])
                    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('mode')
                    ->colors([
                        'info' => 'online',
                        'warning' => 'hybrid',
                        'secondary' => 'offline',
                    ])
                    ->icons([
                        'heroicon-o-globe-alt' => 'online',
                        'heroicon-o-arrow-path' => 'hybrid',
                        'heroicon-o-building-office-2' => 'offline',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('duration_weeks')
                    ->suffix(' weeks')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('seats')
                    ->label('Total Seats')
                    ->numeric()
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('enrollments_count')
                    ->counts('enrollments')
                    ->label('Enrolled')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('available_seats')
    ->label('Available')
    ->state(function (Course $record): int {
        return max(0, $record->seats - $record->enrollments_count);
    })
    ->badge()
    ->color(fn (int $state): string => match (true) {
        $state === 0 => 'danger',
        $state <= 5 => 'warning',
        default => 'success',
    })
    ->sortable(),
                
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                    ])
                    ->icons([
                        'heroicon-o-pencil' => 'draft',
                        'heroicon-o-check-circle' => 'published',
                    ])
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('assignments_count')
                    ->counts('assignments')
                    ->label('Assignments')
                    ->sortable()
                    ->toggleable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('level')
                    ->options([
                        'beginner' => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'advanced' => 'Advanced',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('mode')
                    ->options([
                        'online' => 'Online',
                        'hybrid' => 'Hybrid',
                        'offline' => 'Offline',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->multiple(),
                
                Tables\Filters\SelectFilter::make('instructor')
                    ->relationship('instructor', 'name')
                    ->searchable()
                    ->preload()
                    ->multiple(),
                
                Tables\Filters\Filter::make('seats_available')
                    ->label('Has Available Seats')
                    ->query(fn (Builder $query): Builder => 
                        $query->withCount('enrollments')
                              ->whereRaw('seats > enrollments_count')
                    ),
                
                Tables\Filters\Filter::make('fully_booked')
                    ->label('Fully Booked')
                    ->query(fn (Builder $query): Builder => 
                        $query->withCount('enrollments')
                              ->whereRaw('seats <= enrollments_count')
                    ),
                
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                
                Tables\Actions\Action::make('publish')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn (Course $record) => $record->update(['status' => 'published']))
                    ->visible(fn (Course $record) => $record->status === 'draft'),
                
                Tables\Actions\Action::make('unpublish')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn (Course $record) => $record->update(['status' => 'draft']))
                    ->visible(fn (Course $record) => $record->status === 'published'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'published']))
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['status' => 'draft']))
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\EnrollmentsRelationManager::class,
            RelationManagers\AssignmentsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'view' => Pages\ViewCourse::route('/{record}'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->withCount('enrollments')
            ->withTrashed();
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::where('status', 'published')->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}