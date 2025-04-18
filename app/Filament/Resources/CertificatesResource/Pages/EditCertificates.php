<?php

namespace App\Filament\Resources\CertificatesResource\Pages;

use App\Filament\Resources\CertificatesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCertificates extends EditRecord
{
    protected static string $resource = CertificatesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
