<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WaGatewayController extends Controller
{
    public function index()
    {
        $wagateways = \App\Models\WaGateway::latest()->get();
        return view('master.wagateways.index', compact('wagateways'));
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
}
