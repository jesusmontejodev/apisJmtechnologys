<?php

namespace App\Filament\Resources\ContactResource\Pages;

use App\Filament\Resources\ContactResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewContact extends ViewRecord
{
    protected static string $resource = ContactResource::class;

    public function getTitle(): string | Htmlable
    {
        return '👤 ' . ($this->record->name ?? 'Contact');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->hidden(),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \Filament\Widgets\TextEntry::make('contact_summary')
                ->label('')
                ->content(fn () => view('layouts.contact-summary', ['contact' => $this->record]))
                ->hidden(),
        ];
    }
}

