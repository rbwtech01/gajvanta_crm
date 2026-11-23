<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachineryItem;
use App\Models\MachinerySerial;

class MachineryItemController extends Controller
{
    public function index()
    {
        $items = MachineryItem::with('category')
            ->where('is_active', true)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    public function show(MachineryItem $machineryItem)
    {
        $machineryItem->load('category');

        return response()->json([
            'success' => true,
            'data' => $machineryItem,
        ]);
    }

    public function machineSerial($serialNumber)
    {
        $serial = MachinerySerial::where('serial_number', $serialNumber)
            ->where('is_active', true)
            ->firstOrFail();

        $machineryItem = MachineryItem::with('category')
            ->findOrFail($serial->machinery_item_id);

        return view('qr.index', [
            'machineryItem' => $machineryItem,
            'serial' => $serial,
        ]);
    }

    public function showQr($serialNumber)
    {
        $serial = MachinerySerial::where('serial_number', $serialNumber)
            ->where('is_active', true)
            ->firstOrFail();

        // YOUR EXISTING MACHINE DETAILS URL
        $machineUrl = route('machine.by.serial', [
            'serialCode' => $serial->serial_number,
        ]);

        return view('qr.show', [
            'serial' => $serial,
            'machineUrl' => $machineUrl,
        ]);
    }
}
