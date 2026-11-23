<?php

namespace App\Filament\Packing\Resources\Customers\Pages;

use App\Filament\Packing\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
