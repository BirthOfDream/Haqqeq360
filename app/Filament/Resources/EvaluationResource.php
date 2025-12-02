<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EvaluationResource\Pages;
use App\Models\Evaluation;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EvaluationResource extends Resource
{
    protected static ?string $model = Evaluation::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    
    protected static ?string $navigationLabel = 'التقييمات';
    
    protected static ?string $modelLabel = 'تقييم';
    
    protected static ?string $pluralModelLabel = 'التقييمات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('معلومات المنتج')
                    ->schema([
                        Forms\Components\Select::make('product_type')
                            ->label('نوع المنتج')
                            ->options([
                                'course' => 'دورة',
                                'bootcamp' => 'معسكر',
                                'workshop' => 'ورشة',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Forms\Set $set) => $set('product_id', null)),

                        Forms\Components\Select::make('product_id')
    ->label('اسم المنتج')
    ->options(function (Forms\Get $get) {
        $type = $get('product_type');
        if (!$type) return [];

        return match($type) {
            'course' => \App\Models\Course::pluck('title', 'id'), // Change 'name' to 'title'
            'bootcamp' => \App\Models\Bootcamp::pluck('title', 'id'),
            'workshop' => \App\Models\Workshop::pluck('title', 'id'),
            default => [],
        };
    })
    ->required()
    ->searchable()
    ->disabled(fn (Forms\Get $get) => !$get('product_type'))
    ->live()
    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set, $state) {
        if (!$state) return;
        
        $type = $get('product_type');
        $model = match($type) {
            'course' => \App\Models\Course::find($state),
            'bootcamp' => \App\Models\Bootcamp::find($state),
            'workshop' => \App\Models\Workshop::find($state),
            default => null,
        };
        
        if ($model) {
            // Update this line too to match your actual column
            $set('product_name', $model->title ?? $model->name);
        }
    }),
                        Forms\Components\Hidden::make('product_name'),

                        Forms\Components\Toggle::make('is_active')
                            ->label('نشط')
                            ->default(true)
                            ->inline(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('الأسئلة')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('load_standard')
                                ->label('تحميل الأسئلة القياسية')
                                ->icon('heroicon-o-arrow-path')
                                ->action(function (Forms\Set $set) {
                                    $evaluation = new Evaluation();
                                    $questions = $evaluation->loadStandardQuestions();
                                    $set('questions', $questions);
                                })
                                ->visible(fn (string $operation) => $operation === 'create'),
                        ]),

                        Forms\Components\Repeater::make('questions')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('question_text')
                                    ->label('نص السؤال')
                                    ->required()
                                    ->columnSpanFull(),

                                Forms\Components\Select::make('question_type')
                                    ->label('نوع السؤال')
                                    ->options([
                                        'rating' => 'تقييم بالنجوم',
                                        'scale' => 'مقياس',
                                        'yes_no' => 'نعم/لا',
                                        'text' => 'نص حر',
                                        'grade' => 'تقدير',
                                    ])
                                    ->required()
                                    ->live(),

                                Forms\Components\TagsInput::make('options')
                                    ->label('الخيارات')
                                    ->placeholder('اضغط Enter بعد كل خيار')
                                    ->visible(fn (Forms\Get $get) => 
                                        in_array($get('question_type'), ['scale', 'yes_no', 'grade'])
                                    ),

                                Forms\Components\Hidden::make('order')
                                    ->default(fn ($get) => $get('../../questions') ? count($get('../../questions')) + 1 : 1),

                                Forms\Components\Toggle::make('is_required')
                                    ->label('إجباري')
                                    ->default(true)
                                    ->inline(false),
                            ])
                            ->defaultItems(0)
                            ->reorderable()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['question_text'] ?? null)
                            ->addActionLabel('إضافة سؤال')
                            ->columns(2),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product_type')
                    ->label('نوع المنتج')
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'course' => 'دورة',
                        'bootcamp' => 'معسكر',
                        'workshop' => 'ورشة',
                        default => $state,
                    })
                    ->badge()
                    ->color(fn (string $state): string => match($state) {
                        'course' => 'success',
                        'bootcamp' => 'warning',
                        'workshop' => 'info',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('product_name')
                    ->label('اسم المنتج')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('questions_count')
                    ->label('عدد الأسئلة')
                    ->counts('questions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('responses_count')
                    ->label('عدد الردود')
                    ->counts('responses')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('الحالة')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('product_type')
                    ->label('نوع المنتج')
                    ->options([
                        'course' => 'دورة',
                        'bootcamp' => 'معسكر',
                        'workshop' => 'ورشة',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('الحالة')
                    ->placeholder('الكل')
                    ->trueLabel('نشط')
                    ->falseLabel('غير نشط'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_results')
                    ->label('عرض النتائج')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (Evaluation $record): string => 
                        route('filament.admin.resources.evaluations.results', $record)
                    )
                    ->openUrlInNewTab(),
                
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\ListEvaluations::route('/'),
            'create' => Pages\CreateEvaluation::route('/create'),
            'edit' => Pages\EditEvaluation::route('/{record}/edit'),
            'results' => Pages\ViewEvaluationResults::route('/{record}/results'),
        ];
    }
}