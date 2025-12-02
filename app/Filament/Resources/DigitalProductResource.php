<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DigitalProductResource\Pages;
use App\Models\DigitalProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Builder;

class DigitalProductResource extends Resource
{
    protected static ?string $model = DigitalProduct::class;
    protected static ?string $navigationIcon = 'heroicon-o-archive-box';
    protected static ?string $navigationGroup = 'إدارة المنتجات';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('title')->required(),
            Forms\Components\TextInput::make('slug')->required()->unique(ignoreRecord: true),

            Forms\Components\Select::make('type')
                ->options([
                    'e-book' => 'E-Book',
                    'audio' => 'Audio',
                    'video' => 'Video',
                    'software' => 'Software',
                ])->required(),

            Forms\Components\Textarea::make('description')->required()->columnSpanFull(),

            Forms\Components\FileUpload::make('file_path')
                ->directory('digital_products')
                ->label('Digital File')
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'draft' => 'Draft',
                    'published' => 'Published',
                ])->default('draft')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('type')->colors([
                    'primary' => 'e-book',
                    'info' => 'audio',
                    'success' => 'video',
                    'warning' => 'software',
                ]),
                Tables\Columns\BadgeColumn::make('status')->colors([
                    'success' => 'published',
                    'danger'  => 'draft',
                ]),
                Tables\Columns\TextColumn::make('created_at')->date()->sortable()
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['draft'=>'Draft','published'=>'Published']),
                Tables\Filters\SelectFilter::make('type')
                    ->options(['e-book'=>'E-Book','audio'=>'Audio','video'=>'Video','software'=>'Software']),
                Tables\Filters\TrashedFilter::make()
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDigitalProducts::route('/'),
            'create' => Pages\CreateDigitalProduct::route('/create'),
            'edit'   => Pages\EditDigitalProduct::route('/{record}/edit'),
        ];
    }
}
