<?php

namespace App\Http\Controllers;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;

class MessageTemplateController extends Controller
{
    public function index()
    {
        // Ensure default templates exist
        $templates = [
            'student_checkin', 'student_checkout', 
            'teacher_checkin', 'teacher_checkout', 
            'student_late_checkin', 'student_early_checkout'
        ];
        foreach ($templates as $key) {
            MessageTemplate::firstOrCreate(['key' => $key], ['content' => 'Absensi {status} pada {time}.']);
        }

        $messages = MessageTemplate::all();
        return view('settings.messages.index', compact('messages'));
    }

    public function update(Request $request, MessageTemplate $messageTemplate)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $messageTemplate->update(['content' => $request->content]);

        return redirect()->route('message-templates.index')->with('success', 'Template pesan berhasil diperbarui');
    }
}
