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
                Forms\Components\Section::make('📊 Información del Contacto')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->label('👤 Nombre')
                                    ->disabled(),
                                Forms\Components\TextInput::make('email')
                                    ->label('📧 Email')
                                    ->disabled()
                                    ->copyable(),
                            ]),
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('phone')
                                    ->label('📱 Teléfono')
                                    ->disabled()
                                    ->copyable(),
                                Forms\Components\TextInput::make('subject')
                                    ->label('📝 Asunto')
                                    ->disabled(),
                            ]),
                        Forms\Components\Textarea::make('message')
                            ->label('💬 Mensaje')
                            ->disabled()
                            ->autosize(),
                    ]),

                Forms\Components\Section::make('🔧 Información Técnica')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('ip_address')
                                    ->label('IP Address')
                                    ->disabled()
                                    ->copyable(),
                                Forms\Components\TextInput::make('recaptcha_score')
                                    ->label('reCAPTCHA Score')
                                    ->disabled(),
                            ]),
                        Forms\Components\Textarea::make('user_agent')
                            ->label('User Agent')
                            ->disabled()
                            ->autosize(),
                    ]),

                Forms\Components\Section::make('📋 Estado del Envío')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('status')
                                    ->label('Estado')
                                    ->options([
                                        'received' => 'Recibido',
                                        'processing' => 'Procesando',
                                        'sent' => 'Enviado',
                                        'failed' => 'Fallido',
                                    ])
                                    ->disabled(),
                                Forms\Components\TextInput::make('email_sent_at')
                                    ->label('Email Enviado')
                                    ->disabled(),
                            ]),
                        Forms\Components\Textarea::make('error_message')
                            ->label('Mensaje de Error')
                            ->disabled()
                            ->autosize()
                            ->hidden(fn ($get) => !$get('error_message')),
                    ]),

                Forms\Components\Section::make('📊 Datos Completos del Formulario')
                    ->collapsed()
                    ->schema([
                        Forms\Components\View::make('components.contact-json-display')
                            ->viewData([
                                'data' => fn ($record) => $record?->form_data ?? [],
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\Layout\Stack::make([
                    Tables\Columns\Layout\Grid::make([
                        'md' => 3,
                    ])->schema([
                        Tables\Columns\TextColumn::make('name')
                            ->label('👤 Nombre')
                            ->searchable()
                            ->sortable()
                            ->weight('bold')
                            ->size('lg'),
                        
                        Tables\Columns\TextColumn::make('email')
                            ->label('📧 Email')
                            ->searchable()
                            ->copyable()
                            ->icon('heroicon-m-envelope'),
                        
                        Tables\Columns\TextColumn::make('phone')
                            ->label('📱 Teléfono')
                            ->searchable()
                            ->copyable()
                            ->icon('heroicon-m-phone'),
                    ]),

                    Tables\Columns\Layout\Grid::make([
                        'md' => 2,
                    ])->schema([
                        Tables\Columns\TextColumn::make('project.name')
                            ->label('🏢 Proyecto')
                            ->badge()
                            ->color('info'),
                        
                        Tables\Columns\TextColumn::make('subject')
                            ->label('📝 Asunto')
                            ->searchable()
                            ->limit(50),
                    ]),

                    Tables\Columns\TextColumn::make('message')
                        ->label('💬 Mensaje')
                        ->limit(100)
                        ->markdown()
                        ->wrap(),

                    Tables\Columns\Layout\Grid::make([
                        'md' => 4,
                    ])->schema([
                        Tables\Columns\BadgeColumn::make('status')
                            ->label('Estado')
                            ->colors([
                                'warning' => 'received',
                                'info' => 'processing',
                                'success' => 'sent',
                                'danger' => 'failed',
                            ])
                            ->formatStateUsing(fn(string $state): string => match($state) {
                                'received' => '📥 Recibido',
                                'processing' => '⏳ Procesando',
                                'sent' => '✅ Enviado',
                                'failed' => '❌ Fallido',
                                default => $state,
                            }),
                        
                        Tables\Columns\IconColumn::make('email_sent_at')
                            ->label('Email')
                            ->icon(fn ($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->color(fn ($state) => $state ? 'success' : 'danger'),
                        
                        Tables\Columns\TextColumn::make('ip_address')
                            ->label('🌐 IP')
                            ->copyable()
                            ->toggleable(isToggledHiddenByDefault: true),
                        
                        Tables\Columns\TextColumn::make('created_at')
                            ->label('📅 Fecha')
                            ->dateTime('d/m/Y H:i')
                            ->sortable(),
                    ]),
                ]),
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
