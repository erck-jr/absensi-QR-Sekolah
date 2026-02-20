<?php

namespace App\Http\Controllers;

use App\Models\CardTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CardTemplateController extends Controller
{
    public function index()
    {
        // Ensure default records exist for both Student and Teacher cards
        $keys = ['student_front', 'student_back', 'teacher_front', 'teacher_back'];
        foreach ($keys as $key) {
            CardTemplate::firstOrCreate(['key' => $key], ['file_name' => 'default.png']);
        }

        $templates = CardTemplate::all();
        return view('settings.cards.index', compact('templates'));
    }

    public function update(Request $request, CardTemplate $cardTemplate)
    {
        $request->validate([
            'file_name' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('file_name')) {
            $file = $request->file('file_name');
            $filename = $cardTemplate->key . '.' . $file->getClientOriginalExtension();
            
            // Move to public/templates_card
            $file->move(public_path('templates_card'), $filename);

            $cardTemplate->update(['file_name' => $filename]);
        }

        return redirect()->route('card-templates.index')->with('success', 'Template berhasil diupload');
    }
}
