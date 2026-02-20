<?php

namespace App\Http\Controllers;

use App\Models\Holiday;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index()
    {
        // Group by group_id if it exists, otherwise use id (treat as single)
        // We use COALESCE(group_id, id) to achieve this.
        $holidays = Holiday::selectRaw('
                MAX(group_id) as group_id, 
                MIN(dates) as start_date, 
                MAX(dates) as end_date, 
                MIN(info) as info,
                MIN(id) as id
            ')
            ->groupBy(\Illuminate\Support\Facades\DB::raw('COALESCE(group_id, id)'))
            ->orderBy('start_date', 'desc')
            ->get();

        return view('master.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('master.holidays.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'info' => 'required|string',
        ]);

        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $info = $request->info;
        
        // Generate a unique group ID for this batch
        $groupId = \Illuminate\Support\Str::uuid();

        while ($startDate->lte($endDate)) {
            Holiday::create([
                'dates' => $startDate->format('Y-m-d'),
                'info' => $info,
                'group_id' => $groupId,
            ]);
            $startDate->addDay();
        }

        return redirect()->route('holidays.index')->with('success', 'Data libur berhasil ditambahkan');
    }

    public function edit($id)
    {
        // Find the group based on the ID passed (which is just one random ID from the group)
        $holiday = Holiday::findOrFail($id);
        
        if ($holiday->group_id) {
             $group = Holiday::where('group_id', $holiday->group_id)
                ->selectRaw('MIN(dates) as start_date, MAX(dates) as end_date, MIN(info) as info, group_id')
                ->groupBy('group_id')
                ->first();
             
             // Pass the group data to view, but we might need a way to identify it's a group update
             return view('master.holidays.edit', compact('group', 'holiday')); 
        }

        // Fallback for single legacy records
        return view('master.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, $id)
    {
        $holiday = Holiday::findOrFail($id);

        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'info' => 'required|string',
        ]);

        // Delete existing records in this group
        if ($holiday->group_id) {
            Holiday::where('group_id', $holiday->group_id)->delete();
            $groupId = $holiday->group_id; // Keep same group ID
        } else {
            $holiday->delete();
            $groupId = \Illuminate\Support\Str::uuid(); // Generate new if it was legacy
        }

        // Re-create records
        $startDate = \Carbon\Carbon::parse($request->start_date);
        $endDate = \Carbon\Carbon::parse($request->end_date);
        $info = $request->info;

        while ($startDate->lte($endDate)) {
            Holiday::create([
                'dates' => $startDate->format('Y-m-d'),
                'info' => $info,
                'group_id' => $groupId,
            ]);
            $startDate->addDay();
        }

        return redirect()->route('master.holidays.index')->with('success', 'Data libur berhasil diperbarui');
    }

    public function destroy($id)
    {
        $holiday = Holiday::findOrFail($id);
        
        if ($holiday->group_id) {
            Holiday::where('group_id', $holiday->group_id)->delete();
        } else {
            $holiday->delete();
        }
        
        return redirect()->route('master.holidays.index')->with('success', 'Data libur berhasil dihapus');
    }
}
