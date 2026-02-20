<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        $shifts = Shift::latest()->get();
        return view('master.shifts.index', compact('shifts'));
    }

    public function create()
    {
        return view('master.shifts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'check_in_time' => 'required',
            'check_out_time' => 'required',
            'late_check_in_minute' => 'required|numeric',
        ]);

        $data = $request->all();
        // If creating first shift, make it active by default
        if (Shift::count() === 0) {
            $data['is_active'] = true;
        }

        $shift = Shift::create($data);

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil ditambahkan');
    }

    public function edit(Shift $shift)
    {
        return view('master.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift)
    {
        $request->validate([
            'name' => 'required',
            'check_in_time' => 'required',
            'check_out_time' => 'required',
            'late_check_in_minute' => 'required|numeric',
        ]);

        // Prevent deactivating the only active shift via update if not activating another (logic handled by toggle mostly)
        // Ideally, edit shouldn't change active status unless we add a checkbox. 
        // User asked for "toggle button" separately. 
        // But if we edit properties, we keep is_active as is.

        $shift->update($request->all());

        return redirect()->route('shifts.index')->with('success', 'Shift berhasil diperbarui');
    }

    public function destroy(Shift $shift)
    {
        if ($shift->is_active) {
            return redirect()->route('shifts.index')->with('error', 'Tidak dapat menghapus shift yang sedang aktif. Silahkan aktifkan shift lain terlebih dahulu.');
        }

        $shift->delete();
        return redirect()->route('shifts.index')->with('success', 'Shift berhasil dihapus');
    }

    public function toggle(Shift $shift)
    {
        if ($shift->is_active) {
            // Check if there are other shifts. If this is the only one, maybe allow toggle off (leaving 0 active)? 
            // User requirement: "resulting only 1 shift active". 
            // If we turn off the only active one, we have 0. That violates "only 1 shift active" ideally.
            // But maybe they want to switch to another? 
            // Better UX: Activating another auto-deactivates this one.
            // If clicking on already active one -> Do nothing or warn "Already active".
            return redirect()->back()->with('warning', 'Shift ini sudah aktif.');
        }

        // Deactivate all others
        Shift::where('id', '!=', $shift->id)->update(['is_active' => false]);
        
        $shift->update(['is_active' => true]);

        return redirect()->back()->with('success', 'Shift berhasil diaktifkan.');
    }
}
