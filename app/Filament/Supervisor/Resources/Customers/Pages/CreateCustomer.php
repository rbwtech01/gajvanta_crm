<?php

namespace App\Filament\Supervisor\Resources\Customers\Pages;

use App\Filament\Supervisor\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
