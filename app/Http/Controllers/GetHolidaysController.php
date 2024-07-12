<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class GetHolidaysController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $holidays = Holiday::all();

        return response()->json($holidays);
    }
}
