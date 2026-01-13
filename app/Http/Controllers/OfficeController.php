<?php

namespace App\Http\Controllers;

use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class OfficeController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        // Conecta automáticamente los métodos con la Policy
        $this->authorizeResource(Office::class, 'office');
    }

    public function index()
    {
        $offices = Office::all();
        return view('offices.index', compact('offices'));
    }

    public function create()
    {
        return view('offices.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:offices',
            'branch_code' => 'nullable|string|unique:offices',
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Office::create($validated);

        return redirect()->route('offices.index')->with('success', 'Oficina creada exitosamente.');
    }

    public function show(Office $office)
    {
        return view('offices.show', compact('office'));
    }

    public function edit(Office $office)
    {
        return view('offices.edit', compact('office'));
    }

    public function update(Request $request, Office $office)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:offices,name,' . $office->id,
            'branch_code' => 'nullable|string|unique:offices,branch_code,' . $office->id,
            'state' => 'nullable|string',
            'city' => 'nullable|string',
            'address' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $office->update($validated);

        return redirect()->route('offices.index')->with('success', 'Oficina actualizada exitosamente.');
    }

    public function destroy(Office $office)
    {
        $office->delete();

        return redirect()->route('offices.index')->with('success', 'Oficina eliminada exitosamente.');
    }
}