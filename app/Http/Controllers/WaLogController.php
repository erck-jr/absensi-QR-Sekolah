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

    public function export(Request $request)
    {
        $query = WaLog::with(['gateway']);

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $logs = $query->orderBy('created_at', 'desc')->get();

        $content = "RIWAYAT LOG WHATSAPP\n";
        $content .= "Diekspor Pada: " . now()->toDateTimeString() . "\n";
        $content .= "Total Log: " . $logs->count() . "\n";
        $content .= "================================================================================\n\n";

        foreach ($logs as $log) {
            $content .= "[" . $log->created_at->toDateTimeString() . "]\n";
            $content .= "Penerima     : " . $log->recipient_number . "\n";
            $content .= "Tipe Absensi : " . strtoupper($log->attendance_type) . "\n";
            $content .= "Gateway      : " . ($log->gateway->name ?? '-') . "\n";
            $content .= "Status       : " . strtoupper($log->status) . "\n";
            if ($log->error_details) {
                $content .= "Error Detail : " . $log->error_details . "\n";
            }
            $content .= "Isi Pesan    :\n" . $log->message_content . "\n";
            $content .= "================================================================================\n\n";
        }

        $fileName = 'wa_logs_export_' . date('Y-m-d_H-i-s') . '.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function clear(Request $request)
    {
        $request->validate([
            'confirm_checkbox' => 'required',
            'confirm_text' => 'required|string',
        ]);

        if (trim($request->confirm_text) !== 'BERSIHKAN LOG') {
            return redirect()->route('walogs.index')->with('error', 'Konfirmasi teks tidak cocok. Pembersihan log dibatalkan.');
        }

        try {
            WaLog::query()->delete();
            return redirect()->route('walogs.index')->with('success', 'Seluruh log WhatsApp berhasil dibersihkan.');
        } catch (\Exception $e) {
            return redirect()->route('walogs.index')->with('error', 'Gagal membersihkan log WhatsApp: ' . $e->getMessage());
        }
    }
}
