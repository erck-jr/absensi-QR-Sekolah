<?php

namespace App\Http\Controllers;

use App\Models\WaLog;
use Illuminate\Http\Request;

class WaLogController extends Controller
{
    public function index(Request $request)
    {
        $query = WaLog::with(['gateway']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->appends($request->query());

        return view('master.walogs.index', compact('logs'));
    }
}
