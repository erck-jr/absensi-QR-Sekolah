<?php

namespace App\Http\Controllers;

use App\Models\Level;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with('level')->latest()->get();
        return view('master.classes.index', compact('classes'));
    }

    public function create()
    {
        $levels = Level::all();
        return view('master.classes.create', compact('levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'level_id' => 'required',
        ]);

        SchoolClass::create($request->all());

        return redirect()->route('classes.index')->with('success', 'Kelas berhasil ditambahkan');
    }

    public function edit(SchoolClass $class)
    {
        $levels = Level::all();
        return view('master.classes.edit', compact('class', 'levels'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $request->validate([
            'name' => 'required',
            'level_id' => 'required',
        ]);

        $class->update($request->all());

        return redirect()->route('classes.index')->with('success', 'Kelas berhasil diperbarui');
    }

    public function destroy(SchoolClass $class)
    {
        $class->delete();
        return redirect()->route('classes.index')->with('success', 'Kelas berhasil dihapus');
    }
}
