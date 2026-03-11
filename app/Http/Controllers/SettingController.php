<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');
        return view('settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        // Only admin can update settings
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah pengaturan.');
        }

        $request->validate([
            'app_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'app_name' => 'nullable|string|max:255',
            'school_name' => 'nullable|string|max:255',
            'app_description' => 'nullable|string|max:500',
            'welcome_text' => 'nullable|string|max:1000',
        ]);

        $data = $request->except('_token', '_method', 'app_logo');

        // Handle checkboxes for WA Notifications
        $waCheckboxes = [
            'wa_notif_student_in',
            'wa_notif_student_out',
            'wa_notif_teacher_in',
            'wa_notif_teacher_out'
        ];

        foreach ($waCheckboxes as $checkbox) {
            $data[$checkbox] = $request->has($checkbox) ? '1' : '0';
        }

        if ($request->hasFile('app_logo')) {
            $file = $request->file('app_logo');
            $filename = 'app_logo_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('assets/images');
            
            // Ensure directory exists
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }

            // Move file
            $file->move($destinationPath, $filename);

            // Delete old logo if exists (optional, but good for cleanup)
            $oldLogo = Setting::where('key', 'app_logo')->value('value');
            if ($oldLogo && file_exists(public_path($oldLogo))) {
                @unlink(public_path($oldLogo));
            }

            // Save new path
            Setting::updateOrCreate(
                ['key' => 'app_logo'],
                ['value' => 'assets/images/' . $filename]
            );
        }
        
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        // Clear cache
        \Illuminate\Support\Facades\Cache::forget('app_settings');

        return redirect()->route('settings.index')->with('success', 'Pengaturan berhasil disimpan');
    }
}
