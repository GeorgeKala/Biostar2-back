<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DeviceController extends Controller
{
    public function fetchDeviceData(Request $request)
    {

        $sessionId = $request->header()['bs-session-id'][0];

        $deviceUrl = 'https://10.150.20.173/api/devices';

        try {
            $deviceResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $sessionId,
                ])
                ->get($deviceUrl);
            if ($deviceResponse->successful()) {
                $deviceData = $deviceResponse->json();

                return response()->json($deviceData);
            } else {
                return response()->json(['error' => 'Failed to fetch device data'], $deviceResponse->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function scanCard(Request $request, $deviceId)
    {
        $sessionId = $request->header()['bs-session-id'][0];
        $scanCardUrl = "https://10.150.20.173/api/devices/{$deviceId}/scan_card";

        try {
            $scanCardResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    'bs-session-id' => $sessionId,
                ])
                ->post($scanCardUrl);
            if ($scanCardResponse->successful()) {
                $scanCardData = $scanCardResponse->json();

                return response()->json($scanCardData);
            } else {
                return response()->json(['error' => 'Failed to scan card'], $scanCardResponse->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
