<?php

namespace App\Filament\Shipment\Resources\Customers\Pages;

use App\Filament\Shipment\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
