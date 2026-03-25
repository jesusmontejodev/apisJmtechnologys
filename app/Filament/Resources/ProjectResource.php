<?php

namespace App\Filament\Resources;

use App\Models\Project;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProjectResource extends Resource
{
    protected static ?string $model = Project::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $label = 'Projects';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                    
                Forms\Components\TextInput::make('endpoint_destino')
                    ->label('Endpoint Destination')
                    ->url()
                    ->required(),
                    
                Forms\Components\Select::make('recaptcha_type')
                    ->options([
                        'v2' => 'reCAPTCHA v2',
                        'v3' => 'reCAPTCHA v3',
                    ])
                    ->required(),
                    
                Forms\Components\Textarea::make('recaptcha_site_key')
                    ->label('reCAPTCHA Site Key')
                    ->required()
                    ->rows(2),
                    
                Forms\Components\Textarea::make('recaptcha_secret_key')
                    ->label('reCAPTCHA Secret Key')
                    ->required()
                    ->rows(2)
                    ->password(),
                    
                Forms\Components\TextInput::make('recaptcha_min_score')
                    ->label('Minimum Score (v3 only)')
                    ->numeric()
                    ->step(0.1)
                    ->minValue(0)
                    ->maxValue(1)
                    ->default(0.5),
                    
                Forms\Components\TagsInput::make('allowed_origins')
                    ->label('Allowed Origins')
                    ->placeholder('https://example.com')
                    ->separator(','),
                    
                Forms\Components\TextInput::make('project_token')
                    ->label('Project Token')
                    ->disabled()
                    ->copyable(),
                    
                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('recaptcha_type')
                    ->label('Type')
                    ->badge(),
                    
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Owner'),
                    
                Tables\Columns\BooleanColumn::make('is_active'),
                
                Tables\Columns\TextColumn::make('submissionLogs')
                    ->label('Submissions')
                    ->getStateUsing(fn (Project $record) => $record->submissionLogs()->count()),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('recaptcha_type')
                    ->options([
                        'v2' => 'v2',
                        'v3' => 'v3',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('regenerate_token')
                    ->label('Regenerate Token')
                    ->icon('heroicon-m-arrow-path')
                    ->requiresConfirmation()
                    ->action(fn (Project $record) => $record->update(['project_token' => \Illuminate\Support\Str::uuid()])),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProjects::route('/'),
            'create' => Pages\CreateProject::route('/create'),
            'edit' => Pages\EditProject::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orWhere('user_id', auth()->id());
    }
}
