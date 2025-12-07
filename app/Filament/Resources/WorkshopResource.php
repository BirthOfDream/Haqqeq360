<?php

namespace App\Filament\Resources;

use App\Filament\Resources\WorkshopResource\Pages;
use App\Models\Workshop;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class WorkshopResource extends Resource
{
    protected static ?string $model = Workshop::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationGroup = 'إدارة المنتجات';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Basic Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null
                            ),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),

                        Forms\Components\Select::make('user_id')
                            ->label('Instructor')
                            ->relationship('instructor', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\RichEditor::make('description')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'link',
                                'bulletList',
                                'orderedList',
                            ]),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Media')
                    ->schema([
                        Forms\Components\FileUpload::make('cover_image')
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                            ])
                            ->directory('workshops/covers')
                            ->maxSize(2048),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Additional Image')
                            ->image()
                            ->directory('workshops/images')
                            ->maxSize(2048),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Details')
                    ->schema([
                        Forms\Components\TextInput::make('duration_hours')
                            ->numeric()
                            ->suffix('hours')
                            ->minValue(1),

                        Forms\Components\Select::make('level')
                            ->options([
                                'beginner' => 'Beginner',
                                'intermediate' => 'Intermediate',
                                'advanced' => 'Advanced',
                            ])
                            ->required()
                            ->default('beginner'),

                        Forms\Components\Select::make('mode')
                            ->options([
                                'online' => 'Online',
                                'hybrid' => 'Hybrid',
                                'offline' => 'Offline',
                            ])
                            ->required()
                            ->default('online'),

                        Forms\Components\Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->required()
                            ->default('draft'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Pricing')
                    ->schema([
                        Forms\Components\TextInput::make('price')
                            ->numeric()
                            ->prefix('$')
                            ->required()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\TextInput::make('discounted_price')
                            ->numeric()
                            ->prefix('$')
                            ->minValue(0)
                            ->lte('price')
                            ->helperText('Must be less than the regular price'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Workshop Content')
                    ->schema([
                        Forms\Components\Repeater::make('units')
                            ->relationship('units')
                            ->schema([
                                Forms\Components\TextInput::make('title')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Forms\Components\TextInput::make('order')
                                    ->numeric()
                                    ->default(0)
                                    ->minValue(0)
                                    ->helperText('Order of the unit in the workshop'),

                                Forms\Components\Repeater::make('lessons')
                                    ->relationship('lessons')
                                    ->schema([
                                        Forms\Components\TextInput::make('title')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpanFull(),

                                        Forms\Components\RichEditor::make('content')
                                            ->columnSpanFull()
                                            ->toolbarButtons([
                                                'bold',
                                                'italic',
                                                'link',
                                                'bulletList',
                                                'orderedList',
                                                'codeBlock',
                                            ]),

                                        Forms\Components\TextInput::make('order')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->helperText('Order of the lesson in the unit'),

                                        Forms\Components\TextInput::make('video_url')
                                            ->url()
                                            ->maxLength(255)
                                            ->placeholder('https://youtube.com/watch?v=...')
                                            ->helperText('YouTube, Vimeo, or other video URL'),

                                        Forms\Components\TextInput::make('resource_link')
                                            ->url()
                                            ->maxLength(255)
                                            ->placeholder('https://example.com/resource')
                                            ->helperText('External resource or reference link'),

                                        Forms\Components\FileUpload::make('attachment_path')
                                            ->label('Attachment')
                                            ->directory('lessons/attachments')
                                            ->maxSize(10240)
                                            ->acceptedFileTypes(['application/pdf', 'application/zip', 'application/x-rar'])
                                            ->helperText('PDF, ZIP, or RAR files (max 10MB)'),

                                        Forms\Components\Toggle::make('published')
                                            ->default(true)
                                            ->helperText('Is this lesson visible to students?'),

                                        Forms\Components\Section::make('Assignment')
                                            ->schema([
                                                Forms\Components\TextInput::make('assignment.title')
                                                    ->label('Assignment Title')
                                                    ->maxLength(255),

                                                Forms\Components\RichEditor::make('assignment.description')
                                                    ->label('Assignment Description')
                                                    ->columnSpanFull()
                                                    ->toolbarButtons([
                                                        'bold',
                                                        'italic',
                                                        'link',
                                                        'bulletList',
                                                        'orderedList',
                                                    ]),

                                                Forms\Components\DateTimePicker::make('assignment.due_date')
                                                    ->label('Due Date')
                                                    ->native(false),

                                                Forms\Components\TextInput::make('assignment.max_score')
                                                    ->label('Maximum Score')
                                                    ->numeric()
                                                    ->default(100)
                                                    ->minValue(0),

                                                Forms\Components\FileUpload::make('assignment.attachment_path')
                                                    ->label('Assignment Attachment')
                                                    ->directory('assignments/attachments')
                                                    ->maxSize(5120)
                                                    ->helperText('Additional files for the assignment (max 5MB)'),

                                                Forms\Components\Toggle::make('assignment.published')
                                                    ->label('Published')
                                                    ->default(true)
                                                    ->helperText('Is this assignment active?'),
                                            ])
                                            ->columns(2)
                                            ->collapsed()
                                            ->collapsible(),
                                    ])
                                    ->orderColumn('order')
                                    ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Lesson')
                                    ->collapsed()
                                    ->collapsible()
                                    ->columnSpanFull()
                                    ->defaultItems(0)
                                    ->addActionLabel('Add Lesson')
                                    ->reorderable()
                                    ->cloneable(),
                            ])
                            ->orderColumn('order')
                            ->itemLabel(fn (array $state): ?string => $state['title'] ?? 'Unit')
                            ->collapsed()
                            ->collapsible()
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->addActionLabel('Add Unit')
                            ->reorderable()
                            ->cloneable(),
                    ])
                    ->columnSpanFull()
                    ->collapsed()
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('cover_image')
                    ->circular()
                    ->defaultImageUrl(url('/images/placeholder.png')),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('instructor.name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('units_count')
                    ->counts('units')
                    ->label('Units')
                    ->badge()
                    ->color('info'),

                Tables\Columns\BadgeColumn::make('level')
                    ->colors([
                        'success' => 'beginner',
                        'warning' => 'intermediate',
                        'danger' => 'advanced',
                    ]),

                Tables\Columns\BadgeColumn::make('mode')
                    ->colors([
                        'info' => 'online',
                        'warning' => 'hybrid',
                        'success' => 'offline',
                    ]),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('discounted_price')
                    ->money('USD')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'published',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
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
                    ]),

                Tables\Filters\SelectFilter::make('mode')
                    ->options([
                        'online' => 'Online',
                        'hybrid' => 'Hybrid',
                        'offline' => 'Offline',
                    ]),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('instructor')
                    ->relationship('instructor', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListWorkshops::route('/'),
            'create' => Pages\CreateWorkshop::route('/create'),
            'edit' => Pages\EditWorkshop::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}