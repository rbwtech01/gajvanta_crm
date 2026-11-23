<?php

namespace App\Filament\Resources\InactiveAgents\Pages;

use App\Filament\Resources\InactiveAgents\InactiveAgentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInactiveAgent extends CreateRecord
{
    protected static string $resource = InactiveAgentResource::class;
}
