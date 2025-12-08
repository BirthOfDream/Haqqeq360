<?php

namespace App\Filament\Resources\CourseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LessonsRelationManager extends RelationManager
{
    protected static string $relationship = 'lessons';

    protected static ?string $title = 'Unit Lessons';

    protected static ?string $icon = 'heroicon-o-book-open';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Lesson Information')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),
                        
                        Forms\Components\RichEditor::make('content')
                            ->required()
                            ->columnSpanFull()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'link',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'blockquote',
                                'codeBlock',
                            ]),
                        
                        Forms\Components\TextInput::make('order')
                            ->numeric()
                            ->default(fn (RelationManager $livewire) => 
                                $livewire->getOwnerRecord()->lessons()->max('order') + 1
                            )
                            ->required()
                            ->minValue(1)
                            ->helperText('Order in which this lesson appears in the unit'),
                    ]),
                
                Forms\Components\Section::make('Resources')
                    ->schema([
                        Forms\Components\TextInput::make('video_url')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://youtube.com/watch?v=...')
                            ->helperText('YouTube, Vimeo, or direct video URL'),
                        
                        Forms\Components\TextInput::make('resource_link')
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com/resource')
                            ->helperText('External resource or reference link'),
                        
                        Forms\Components\FileUpload::make('attachment_path')
                            ->label('Attachment')
                            ->directory('lesson-attachments')
                            ->visibility('public')
                            ->acceptedFileTypes(['application/pdf', 'application/zip', 'image/*'])
                            ->maxSize(10240)
                            ->helperText('PDF, ZIP, or image files (max 10MB)'),
                    ])
                    ->columns(1),
                
                Forms\Components\Section::make('Publishing')
                    ->schema([
                        Forms\Components\Toggle::make('published')
                            ->label('Published')
                            ->default(false)
                            ->helperText('Make this lesson visible to students'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('assignments'))
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->sortable()
                    ->width(80),
                
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                
                Tables\Columns\IconColumn::make('video_url')
                    ->label('Video')
                    ->boolean()
                    ->trueIcon('heroicon-o-video-camera')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('attachment_path')
                    ->label('Attachment')
                    ->boolean()
                    ->trueIcon('heroicon-o-paper-clip')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('info')
                    ->falseColor('gray')
                    ->alignCenter(),
                
                Tables\Columns\IconColumn::make('resource_link')
                    ->label('Resource')
                    ->boolean()
                    ->trueIcon('heroicon-o-link')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('warning')
                    ->falseColor('gray')
                    ->alignCenter(),
                
                Tables\Columns\TextColumn::make('assignments_count')
                    ->label('Assignments')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                
                Tables\Columns\IconColumn::make('published')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('published')
                    ->label('Published Status')
                    ->placeholder('All Lessons')
                    ->trueLabel('Published Only')
                    ->falseLabel('Unpublished Only'),
                
                Tables\Filters\Filter::make('has_video')
                    ->label('Has Video')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('video_url')),
                
                Tables\Filters\Filter::make('has_attachment')
                    ->label('Has Attachment')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('attachment_path')),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('toggle_publish')
                    ->label(fn ($record) => $record->published ? 'Unpublish' : 'Publish')
                    ->icon(fn ($record) => $record->published ? 'heroicon-o-x-circle' : 'heroicon-o-check-circle')
                    ->color(fn ($record) => $record->published ? 'warning' : 'success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['published' => !$record->published])),
                
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('publish')
                        ->label('Publish Selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['published' => true]))
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\BulkAction::make('unpublish')
                        ->label('Unpublish Selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['published' => false]))
                        ->deselectRecordsAfterCompletion(),
                    
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('order', 'asc')
            ->reorderable('order');
    }
}