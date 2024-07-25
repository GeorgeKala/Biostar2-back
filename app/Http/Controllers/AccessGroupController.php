<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AccessGroupController extends Controller
{
    public function index(Request $request)
    {
        $sessionId = $request->header('bs-session-id');

        $accessGroupsUrl = 'https://10.150.20.173/api/access_groups'; 

        try {
            $accessGroupsResponse = Http::withOptions(['verify' => false])
                ->withHeaders([
                    "bs-session-id" => $sessionId
                ])
                ->get($accessGroupsUrl);

            if ($accessGroupsResponse->successful()) {
                $accessGroupsData = $accessGroupsResponse->json();
                return response()->json($accessGroupsData);
            } else {
                return response()->json(['error' => 'Failed to fetch access groups data'], $accessGroupsResponse->status());
            }
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
