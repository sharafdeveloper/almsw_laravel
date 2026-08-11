<?php

namespace App\Http\Controllers;

use App\Models\Inventory;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    // public function index()
    // {
    //     $inventories = Inventory::with('product')
    //         ->whereHas('product', fn ($q) => $q->where('is_deleted', false))
    //         ->orderBy('id', 'asc')
    //         ->paginate(20);

    //     return view('admin.inventory', compact('inventories'));
    // }

    public function index(Request $request)
    {
        $q = $request->input('q', '');
        $inventories = Inventory::with('product')
            ->whereHas('product', fn ($query) => $query
                ->where('is_deleted', false)
                ->when($q, fn ($query) => $query->where('name', 'like', "%{$q}%"))
            )
            ->orderBy('id', 'asc')
            ->paginate(20)
            ->withQueryString();

        return view('admin.inventory', compact('inventories', 'q'));
    }

    public function print()
    {
        $inventories = Inventory::with('product')
            ->whereHas('product', fn ($q) => $q->where('is_deleted', false))
            ->orderBy('id')
            ->get();

        $data = ['inventories' => $inventories];

        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $filename = 'inventory-' . now()->format('Y-m-d_His') . '.pdf';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.inventory-print', $data)
                ->setPaper('a4', 'portrait');
            return $pdf->stream($filename);
        }

        return view('admin.inventory-print', $data + ['browserPrint' => true]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0',
            'weight'   => 'required|numeric|min:0',
            'price'    => 'nullable|numeric|min:0',   // optional now
        ]);
        $inventory->update(array_filter($data, fn ($v) => $v !== null));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'inventory' => $inventory->fresh('product')]);
        }
        return redirect()->route('inventory')->with('success', 'Inventory updated.');
    }

    public function destroy(Request $request, Inventory $inventory)
    {
        
        $inventory->delete();

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('inventory')->with('success', 'Inventory record deleted.');
    }
}
