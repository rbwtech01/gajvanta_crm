<?php

namespace App\Filament\Operations\Resources\Complaints\Pages;

use App\Filament\Operations\Resources\Complaints\ComplaintResource;
use Filament\Resources\Pages\CreateRecord;

class CreateComplaint extends CreateRecord
{
    protected static string $resource = ComplaintResource::class;
}
