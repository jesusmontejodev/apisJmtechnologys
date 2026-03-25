<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactResource\Pages;
use App\Models\Contact;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ContactResource extends Resource
{
    protected static ?string $model = Contact::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Contactos';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Información del Contacto')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nombre')
                            ->disabled(),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->disabled(),
                        Forms\Components\TextInput::make('phone')
                            ->label('Teléfono')
                            ->disabled(),
                        Forms\Components\TextInput::make('subject')
                            ->label('Asunto')
                            ->disabled(),
                        Forms\Components\Textarea::make('message')
                            ->label('Mensaje')
                            ->disabled(),
                    ])->columns(2),
                Forms\Components\Section::make('Datos Técnicos')
                    ->schema([
                        Forms\Components\TextInput::make('ip_address')
                            ->label('IP Address')
                            ->disabled(),
                        Forms\Components\TextInput::make('recaptcha_score')
                            ->label('reCAPTCHA Score')
                            ->disabled(),
                        Forms\Components\Select::make('status')
                            ->label('Estado')
                            ->options([
                                'received' => 'Recibido',
                                'processing' => 'Procesando',
                                'sent' => 'Enviado',
                                'failed' => 'Fallido',
                            ])
                            ->disabled(),
                        Forms\Components\Textarea::make('error_message')
                            ->label('Mensaje de Error')
                            ->disabled(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                Tables\Columns\TextColumn::make('project.name')
                    ->label('Proyecto')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono'),
                Tables\Columns\TextColumn::make('subject')
                    ->label('Asunto')
                    ->searchable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->label('Estado')
                    ->colors([
                        'warning' => 'received',
                        'info' => 'processing',
                        'success' => 'sent',
                        'danger' => 'failed',
                    ])
                    ->formatStateUsing(fn(string $state): string => match($state) {
                        'received' => 'Recibido',
                        'processing' => 'Procesando',
                        'sent' => 'Enviado',
                        'failed' => 'Fallido',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Recibido')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'received' => 'Recibido',
                        'processing' => 'Procesando',
                        'sent' => 'Enviado',
                        'failed' => 'Fallido',
                    ]),
                Tables\Filters\SelectFilter::make('project_id')
                    ->label('Proyecto')
                    ->relationship('project', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContacts::route('/'),
            'view' => Pages\ViewContact::route('/{record}'),
        ];
    }
}
