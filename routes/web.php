<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SetPasswordController;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\Api\MachineryItemController;

// Route::redirect('/', '/agent/login');
Route::view('/', 'welcome');

Route::get('/set-password/{token}', [SetPasswordController::class, 'create'])->name('password.reset');
Route::post('/set-password', [SetPasswordController::class, 'store'])->name('password.update');
Route::get('/password-created', [SetPasswordController::class, 'passwordCreated'])->name('password.created');

Route::get('/calendar-events', function (Request $request) {
    $filters = $request->validate([
        'start' => ['required', 'date_format:Y-m-d'],
        'end' => ['required', 'date_format:Y-m-d', 'after_or_equal:start'],
        'agent' => ['required', 'integer', 'min:1'],
    ]);

    $agent = User::query()
        ->role('Agent')
        ->whereKey($filters['agent'])
        ->first(['id', 'is_active']);

    if (! $request->user()?->hasRole('Admin')) {
        abort_unless(
            $agent
            && $agent->is_active
            && $request->user()?->is($agent),
            404,
            'The selected agent is unavailable.'
        );
    } else {
        abort_unless(
            $agent,
            404,
            'The selected agent is unavailable.'
        );
    }

    $customers = Customer::query()
        ->whereBetween('booking_date', [$filters['start'], $filters['end']])
        ->where('agent_id', $filters['agent'])
        ->with('shipment')
        ->get();

    // Group by date and build events
    $eventsByDate = [];

    foreach ($customers as $customer) {
        $date = $customer->booking_date;
        
        if (!isset($eventsByDate[$date])) {
            $eventsByDate[$date] = [
                'title' => '',
                'start' => $date,
                'count' => 0,
                'amount' => 0,
                'statuses' => [],
                'states' => [],
                'backgroundColor' => '#16a34a',
                'borderColor' => '#16a34a',
                'textColor' => '#ffffff',
            ];
        }

        $eventsByDate[$date]['count']++;
        $eventsByDate[$date]['amount'] += $customer->price;

        $state = trim((string) $customer->state);
        if ($state !== '' && !in_array($state, $eventsByDate[$date]['states'], true)) {
            $eventsByDate[$date]['states'][] = $state;
        }

        // Determine shipment status for this customer
        $status = null;
        if ($customer->status === 'delivered') {
            $status = 'Delivered';
        } elseif (
            $customer->status === 'dispatched' ||
            filled($customer->shipment?->tracking_id)
        ) {
            $status = 'Shipped';
        }

        if ($status && !in_array($status, $eventsByDate[$date]['statuses'])) {
            $eventsByDate[$date]['statuses'][] = $status;
        }
    }

    return array_values($eventsByDate);

});

Route::get('/invoice/{customer}', [InvoiceController::class, 'download'])->name('invoice.download');

Route::get('/machines/serial/{serialCode}', [
    MachineryItemController::class,
    'machineSerial'
])->name('machine.by.serial');

Route::get('/machines/serial/{serialNumber}/qr', [
    MachineryItemController::class,
    'showQr'
])->name('machine.qr');
