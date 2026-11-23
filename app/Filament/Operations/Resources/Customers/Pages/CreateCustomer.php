<?php

namespace App\Filament\Operations\Resources\Customers\Pages;

use App\Filament\Operations\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
