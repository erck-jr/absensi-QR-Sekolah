<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class WaGatewayController extends Controller
{
    public function index()
    {
        $wagateways = \App\Models\WaGateway::latest()->get();
        $settings = Setting::all()->pluck('value', 'key');
        return view('master.wagateways.index', compact('wagateways', 'settings'));
    }

    public function create()
    {
        return view('master.wagateways.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'api_url' => 'required|url',
            'api_token' => 'required',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        \App\Models\WaGateway::create($data);

        return redirect()->route('wagateways.index')->with('success', 'WhatsApp Gateway berhasil ditambahkan');
    }

    public function edit(\App\Models\WaGateway $wagateway)
    {
        return view('master.wagateways.edit', compact('wagateway'));
    }

    public function update(Request $request, \App\Models\WaGateway $wagateway)
    {
        $request->validate([
            'name' => 'required',
            'api_url' => 'required|url',
            'api_token' => 'required',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->has('is_active');

        $wagateway->update($data);

        return redirect()->route('wagateways.index')->with('success', 'WhatsApp Gateway berhasil diperbarui');
    }

    public function destroy(\App\Models\WaGateway $wagateway)
    {
        $wagateway->delete();
        return redirect()->route('wagateways.index')->with('success', 'WhatsApp Gateway berhasil dihapus');
    }

    public function updateSettings(Request $request)
    {
        // Only admin can update settings
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk mengubah pengaturan.');
        }

        $waCheckboxes = [
            'wa_notif_student_in',
            'wa_notif_student_out',
            'wa_notif_teacher_in',
            'wa_notif_teacher_out'
        ];

        foreach ($waCheckboxes as $checkbox) {
            Setting::updateOrCreate(
                ['key' => $checkbox],
                ['value' => $request->has($checkbox) ? '1' : '0']
            );
        }

        // Clear cache
        Cache::forget('app_settings');

        return redirect()->route('wagateways.index')->with('success', 'Pengaturan Notifikasi WA berhasil disimpan');
    }

    public function processQueue()
    {
        // Only admin can process queue
        if (auth()->user()->role !== 'admin') {
            return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk memproses antrian.');
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
            return redirect()->back()->with('success', 'Antrian notifikasi berhasil diproses (Queue worker dijalankan).');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal memproses antrian: ' . $e->getMessage());
        }
    }

    public function RetryQueue()
{
    // Only admin can process queue
    if (auth()->user()->role !== 'admin') {
        return redirect()->back()->with('error', 'Anda tidak memiliki akses untuk memproses antrian.');
    }

    try {
        // Panggil queue:retry dengan argumen 'all' dipisah
        \Illuminate\Support\Facades\Artisan::call('queue:retry', [
            'id' => ['all']
        ]);

        return redirect()->back()->with('success', 'Semua antrian gagal berhasil dimasukkan kembali ke queue.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal memproses antrian: ' . $e->getMessage());
    }
}

    public function cronProcessQueue(Request $request)
    {
        // Simple token for security from external cron service
        $token = $request->query('token');
        $expectedToken = 'cron12345'; // Token sederhana untuk keamanan

        if ($token !== $expectedToken) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized access'], 401);
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('queue:work', ['--stop-when-empty' => true]);
            return response()->json(['status' => 'success', 'message' => 'Queue worker executed successfully']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
