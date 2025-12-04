<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LinkTreeSettingResource\Pages;
use App\Models\LinkTreeSetting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LinkTreeSettingResource extends Resource
{
    protected static ?string $model = LinkTreeSetting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    
    protected static ?string $navigationLabel = 'إعدادات شجرة الروابط';
    
    protected static ?string $modelLabel = 'إعدادات';
    
    protected static ?string $navigationGroup = 'إدارة التسويق';
    
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات الصفحة')
                    ->schema([
                        Forms\Components\TextInput::make('page_title')
                            ->label('عنوان الصفحة')
                            ->maxLength(255)
                            ->placeholder('أكاديمية التعليم')
                            ->helperText('سيظهر كعنوان في أعلى صفحة الروابط'),

                        Forms\Components\Textarea::make('page_description')
                            ->label('وصف الصفحة')
                            ->rows(3)
                            ->maxLength(500)
                            ->placeholder('تابعونا على جميع المنصات')
                            ->helperText('وصف قصير يظهر أسفل العنوان'),

                        Forms\Components\TextInput::make('slug')
                            ->label('رابط الصفحة')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->default('links')
                            ->prefix(url('/') . '/')
                            ->helperText('الرابط الذي سيتم استخدامه للوصول للصفحة')
                            ->rules(['alpha_dash']),

                        Forms\Components\Toggle::make('is_active')
                            ->label('تفعيل الصفحة')
                            ->default(true)
                            ->helperText('إذا تم تعطيلها، لن يتمكن أحد من الوصول للصفحة'),
                    ])
                    ->columns(1),

                Forms\Components\Section::make('تخصيص الشكل')
                    ->schema([
                        Forms\Components\ColorPicker::make('background_color')
                            ->label('لون الخلفية')
                            ->default('#ffffff')
                            ->helperText('اختر لون خلفية الصفحة'),

                        Forms\Components\ColorPicker::make('button_color')
                            ->label('لون الأزرار')
                            ->default('#000000')
                            ->helperText('اختر لون أزرار الروابط'),

                        Forms\Components\ColorPicker::make('text_color')
                            ->label('لون نص الأزرار')
                            ->default('#ffffff')
                            ->helperText('اختر لون النص داخل الأزرار'),

                        Forms\Components\Select::make('font_family')
                            ->label('نوع الخط')
                            ->options([
                                'Arial' => 'Arial',
                                'Helvetica' => 'Helvetica',
                                'Times New Roman' => 'Times New Roman',
                                'Courier New' => 'Courier New',
                                'Verdana' => 'Verdana',
                                'Georgia' => 'Georgia',
                                'Tahoma' => 'Tahoma',
                                'Trebuchet MS' => 'Trebuchet MS',
                                'Cairo' => 'Cairo (عربي)',
                                'Tajawal' => 'Tajawal (عربي)',
                                'IBM Plex Sans Arabic' => 'IBM Plex Sans Arabic',
                            ])
                            ->default('Arial')
                            ->searchable()
                            ->helperText('اختر نوع الخط المستخدم في الصفحة'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('معاينة مباشرة')
                    ->schema([
                        Forms\Components\ViewField::make('preview')
                            ->label('')
                            ->view('filament.forms.link-tree-preview'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('page_title')
                    ->label('عنوان الصفحة')
                    ->searchable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('الرابط')
                    ->prefix(url('/') . '/')
                    ->copyable()
                    ->copyMessage('تم نسخ الرابط!')
                    ->searchable(),

                Tables\Columns\ColorColumn::make('background_color')
                    ->label('لون الخلفية'),

                Tables\Columns\ColorColumn::make('button_color')
                    ->label('لون الأزرار'),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('نشط')
                    ->boolean(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('preview')
                    ->label('معاينة')
                    ->icon('heroicon-o-eye')
                    ->url(fn (LinkTreeSetting $record): string => url('/' . $record->slug))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLinkTreeSettings::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return LinkTreeSetting::count() === 0;
    }
}

// Pages/ManageLinkTreeSettings.php
namespace App\Filament\Resources\LinkTreeSettingResource\Pages;

use App\Filament\Resources\LinkTreeSettingResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageLinkTreeSettings extends ManageRecords
{
    protected static string $resource = LinkTreeSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => $this->getResource()::getModel()::count() === 0),
        ];
    }
}

// Pages for LinkTreeLinkResource



