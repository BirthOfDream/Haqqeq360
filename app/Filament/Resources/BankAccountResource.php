<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BankAccountResource\Pages;
use App\Models\BankAccount;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BankAccountResource extends Resource
{
    protected static ?string $model = BankAccount::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-library';

    protected static ?string $navigationGroup = 'Financial';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Bank Information')
                    ->schema([
                        Forms\Components\TextInput::make('bank_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Bank Name'),
                        
                        Forms\Components\TextInput::make('beneficiary_name')
                            ->required()
                            ->maxLength(255)
                            ->label('Beneficiary Name'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Account Details')
                    ->schema([
                        Forms\Components\TextInput::make('account_number')
                            ->required()
                            ->maxLength(255)
                            ->label('Account Number'),
                        
                        Forms\Components\TextInput::make('iban')
                            ->maxLength(255)
                            ->label('IBAN')
                            ->placeholder('e.g., EG380002000156789012345678901'),
                        
                        Forms\Components\TextInput::make('swift_code')
                            ->maxLength(255)
                            ->label('SWIFT Code')
                            ->placeholder('e.g., NBEGEGCXXXX'),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Additional Information')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->maxLength(65535)
                            ->columnSpanFull()
                            ->label('Notes'),
                        
                        Forms\Components\Toggle::make('is_active')
                            ->default(true)
                            ->label('Active Status')
                            ->helperText('Only active accounts will be visible to users'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('bank_name')
                    ->searchable()
                    ->sortable()
                    ->label('Bank Name'),
                
                Tables\Columns\TextColumn::make('beneficiary_name')
                    ->searchable()
                    ->sortable()
                    ->label('Beneficiary'),
                
                Tables\Columns\TextColumn::make('account_number')
                    ->searchable()
                    ->label('Account Number')
                    ->copyable()
                    ->copyMessage('Account number copied'),
                
                Tables\Columns\TextColumn::make('iban')
                    ->searchable()
                    ->label('IBAN')
                    ->copyable()
                    ->copyMessage('IBAN copied')
                    ->toggleable(isToggledHiddenByDefault: false),
                
                Tables\Columns\TextColumn::make('swift_code')
                    ->searchable()
                    ->label('SWIFT Code')
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All accounts')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBankAccounts::route('/'),
            'create' => Pages\CreateBankAccount::route('/create'),
            'edit' => Pages\EditBankAccount::route('/{record}/edit'),
        ];
    }
}