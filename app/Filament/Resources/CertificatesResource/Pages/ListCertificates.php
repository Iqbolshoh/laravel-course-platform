<?php

namespace App\Filament\Resources\CertificatesResource\Pages;

use App\Filament\Resources\CertificatesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCertificates extends ListRecords
{
    protected static string $resource = CertificatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
