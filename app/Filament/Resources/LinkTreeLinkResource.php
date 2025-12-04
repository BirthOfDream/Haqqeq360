<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkTreeLinkResource\Pages;
use App\Models\LinkTreeLink;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LinkTreeLinkResource extends Resource
{
    protected static ?string $model = LinkTreeLink::class;

    protected static ?string $navigationIcon = 'heroicon-o-link';
    
    protected static ?string $navigationLabel = 'شجرة الروابط';
    
    protected static ?string $modelLabel = 'رابط';
    
    protected static ?string $pluralModelLabel = 'الروابط';
    
    protected static ?string $navigationGroup = 'إدارة التسويق';
    
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الرابط')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم الرابط')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('مثال: حسابنا على تويتر'),

                        Forms\Components\TextInput::make('url')
                            ->label('عنوان URL')
                            ->required()
                            ->url()
                            ->maxLength(255)
                            ->placeholder('https://example.com')
                            ->validationMessages([
                                'required' => 'الرجاء إدخال رابط صالح',
                                'url' => 'الرابط غير صحيح، يجب أن يبدأ بـ http:// أو https://',
                            ]),

                        Forms\Components\TextInput::make('icon')
                            ->label('أيقونة (اختياري)')
                            ->maxLength(255)
                            ->placeholder('heroicon-o-globe-alt')
                            ->helperText('اسم الأيقونة من مكتبة Heroicons'),

                        Forms\Components\TextInput::make('order')
                            ->label('الترتيب')
                            ->numeric()
                            ->default(0)
                            ->helperText('سيتم ترتيب الروابط تلقائياً إذا تركت هذا الحقل فارغاً'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->helperText('الروابط غير النشطة لن تظهر في الصفحة'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('الإحصائيات')
                    ->schema([
                        Forms\Components\Placeholder::make('clicks')
                            ->label('عدد النقرات')
                            ->content(fn (?LinkTreeLink $record): string => 
                                $record ? number_format($record->clicks) : '0'
                            ),

                        Forms\Components\Placeholder::make('created_at')
                            ->label('تاريخ الإنشاء')
                            ->content(fn (?LinkTreeLink $record): string => 
                                $record?->created_at?->format('Y-m-d H:i') ?? '-'
                            ),
                    ])
                    ->columns(2)
                    ->hidden(fn (?LinkTreeLink $record) => $record === null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order')
                    ->label('#')
                    ->sortable()
                    ->width(50),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم الرابط')
                    ->searchable()
                    ->sortable()
                    ->description(fn (LinkTreeLink $record): string => $record->url)
                    ->limit(50),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('clicks')
                    ->label('النقرات')
                    ->sortable()
                    ->numeric()
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->reorderable('order')
            ->defaultSort('order', 'asc')
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('copy_url')
                        ->label('نسخ الرابط')
                        ->icon('heroicon-o-clipboard-document')
                        ->action(fn (LinkTreeLink $record) => null)
                        ->modalContent(fn (LinkTreeLink $record) => view('filament.modals.copy-url', ['url' => $record->url]))
                        ->modalSubmitAction(false)
                        ->modalCancelActionLabel('إغلاق'),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('activate')
                        ->label('تفعيل المحدد')
                        ->icon('heroicon-o-check-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => true]))
                        ->deselectRecordsAfterCompletion()
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('deactivate')
                        ->label('إلغاء تفعيل المحدد')
                        ->icon('heroicon-o-x-circle')
                        ->action(fn ($records) => $records->each->update(['is_active' => false]))
                        ->deselectRecordsAfterCompletion()
                        ->color('danger')
                        ->requiresConfirmation(),
                ]),
            ])
            ->emptyStateHeading('لا توجد روابط')
            ->emptyStateDescription('ابدأ بإضافة الروابط الأولى لشجرة الروابط')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('إضافة رابط جديد')
                    ->icon('heroicon-o-plus'),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLinkTreeLinks::route('/'),
            'create' => Pages\CreateLinkTreeLink::route('/create'),
            'edit' => Pages\EditLinkTreeLink::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::active()->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }
}