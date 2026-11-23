<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MachinerySerial;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SerialVerificationController extends Controller
{
    public function verify(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'serial_number' => ['required', 'string', 'max:100'],
        ]);

        $serial = trim($validated['serial_number']);

        $machinerySerial = MachinerySerial::with('item.category')
            ->where(function ($query) use ($serial) {
                $query->where('serial_code', $serial)
                      ->orWhere('serial_number', $serial);
            })
            ->first();

        if (! $machinerySerial) {
            return response()->json([
                'success' => true,
                'verified' => false,
                'message' => 'Serial number not found.',
            ], 200);
        }

        if (! $machinerySerial->is_active) {
            return response()->json([
                'success' => true,
                'verified' => false,
                'message' => 'Serial number is inactive.',
            ], 200);
        }

        return response()->json([
            'success' => true,
            'verified' => true,
            'message' => 'Serial number verified successfully.',
            'data' => [
                'serial_number' => $machinerySerial->serial_number,
                'serial_code' => $machinerySerial->serial_code,
                'model_name' => $machinerySerial->item?->name,
                'model_code' => $machinerySerial->item?->model_code,
                'model_tag' => $machinerySerial->item?->model_tag,
                'category' => $machinerySerial->item?->category?->name,
                'is_active' => $machinerySerial->is_active,
            ],
        ], 200);
    }
}