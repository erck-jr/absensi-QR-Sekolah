<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::query();

        if ($request->filled('date')) {
            $query->whereDate('check_in', $request->date);
        }

        $guests = $query->latest('check_in')->get();
        return view('guests.index', compact('guests'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'necessity' => 'required',
        ]);

        Guest::create([
            'name' => $request->name,
            'origin' => $request->origin,
            'meet_with' => $request->meet_with,
            'necessity' => $request->necessity,
            'phone' => $request->phone,
            'check_in' => Carbon::now(),
        ]);

        return redirect()->back()->with('success', 'Tamu berhasil dicatat.');
    }

    public function update(Request $request, $id)
    {
        // For checkout
        $guest = Guest::findOrFail($id);
        $guest->update(['check_out' => Carbon::now()]);
        
        return redirect()->back()->with('success', 'Checkout berhasil.');
    }
}
