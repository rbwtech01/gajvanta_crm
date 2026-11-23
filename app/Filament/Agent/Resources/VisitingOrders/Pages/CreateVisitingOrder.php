<?php

namespace App\Filament\Agent\Resources\VisitingOrders\Pages;

use App\Filament\Agent\Resources\VisitingOrders\VisitingOrderResource;
use App\Models\VisitingOrder;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Builder;

class CreateVisitingOrder extends CreateRecord
{
    protected static string $resource = VisitingOrderResource::class;

    public function canCreateAnother(): bool
    {
        return false;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['agent_id'] = auth()->id();

        // 7 calendar-day cooling period.
        // Created on 24 Sep -> available again from 01 Oct 00:00.
        $data['cooling_until'] = today()
            ->addDays(7)
            ->startOfDay();

        return $data;
    }

    protected function beforeCreate(): void
    {
        $primaryMobile = trim($this->data['primary_mobile'] ?? '');
        $secondaryMobile = trim($this->data['secondary_mobile'] ?? '');

        // Remove empty secondary mobile.
        $mobileNumbers = array_values(
            array_unique(
                array_filter([
                    $primaryMobile,
                    $secondaryMobile,
                ])
            )
        );

        if (empty($mobileNumbers)) {
            return;
        }

        /*
         * Check ALL agents' active visiting orders.
         *
         * Only records whose cooling period has not expired
         * are considered.
         */
        $activeVisitor = VisitingOrder::query()
            ->where('cooling_until', '>', now())
            ->where(function (Builder $query) use ($mobileNumbers) {
                foreach ($mobileNumbers as $mobile) {
                    $query
                        ->orWhere('primary_mobile', $mobile)
                        ->orWhere('secondary_mobile', $mobile);
                }
            })
            ->first();

        if ($activeVisitor) {
            Notification::make()
                ->danger()
                ->title('Visitor already exists')
                ->body(
                    "This visitor already has an active request. "
                    . "A new request can be created from "
                    . $activeVisitor->cooling_until->format('d M Y, h:i A') . "."
                )
                ->persistent()
                ->send();

            $this->halt();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}