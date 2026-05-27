<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    public function index()
    {
        $levels = Level::orderBy('level_order')->get();
        return view('master.levels.index', compact('levels'));
    }

    public function create()
    {
        return view('master.levels.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:levels',
            'level_order' => 'required|integer|min:0'
        ]);
        Level::create($request->all());
        return redirect()->route('levels.index')->with('success', 'Tingkat berhasil ditambahkan');
    }

    public function edit(Level $level)
    {
        return view('master.levels.edit', compact('level'));
    }

    public function update(Request $request, Level $level)
    {
        $request->validate([
            'name' => 'required|unique:levels,name,' . $level->id,
            'level_order' => 'required|integer|min:0'
        ]);
        $level->update($request->all());
        return redirect()->route('levels.index')->with('success', 'Tingkat berhasil diperbarui');
    }

    public function destroy(Level $level)
    {
        $level->delete();
        return redirect()->route('levels.index')->with('success', 'Tingkat berhasil dihapus');
    }
}
