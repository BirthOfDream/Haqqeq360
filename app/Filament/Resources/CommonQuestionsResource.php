<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CommonQuestionsResource\Pages;
use App\Models\CommonQuestion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CommonQuestionsResource extends Resource
{
    protected static ?string $model = CommonQuestion::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'إدارة صفحات الموقع';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('question')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Textarea::make('answer')
                    ->rows(6)
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ])
                    ->default('draft')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('question')->searchable()->limit(50),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'danger' => 'draft',
                        'success' => 'published',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d-m-Y H:i'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),
            ])
            ->defaultSort('id', 'desc')
            ->searchable();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCommonQuestions::route('/'),
            'create' => Pages\CreateCommonQuestions::route('/create'),
            'edit' => Pages\EditCommonQuestions::route('/{record}/edit'),
        ];
    }
}
