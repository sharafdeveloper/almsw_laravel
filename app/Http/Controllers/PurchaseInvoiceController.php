<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseInvoiceController extends Controller
{
    // public function index()
    // {
    //     $invoices = PurchaseInvoice::with('supplier', 'items.product')
    //         ->orderBy('id', 'asc')
    //         ->paginate(15);
    //     return view('admin.purchase-invoice', compact('invoices'));
    // }


        public function index(Request $request)
    {
        $q = $request->input('q', '');
        $invoices = PurchaseInvoice::with('supplier', 'items.product')
            ->when($q, fn($query) => $query->whereHas('supplier', fn($query) => $query->where('name', 'like', "%{$q}%")))
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();
        return view('admin.purchase-invoice', compact('invoices', 'q'));
    }

    public function create()
    {
        return view('admin.purchase-invoice-form', [
            'invoice'   => null,
            'suppliers' => Customer::active()->orderBy('name')->get(),
            'products'  => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);
        $invoice = DB::transaction(fn () => $this->persistInvoice(null, $data));
        return redirect()->route('purchase-invoice')->with('success', 'Purchase invoice ' . $invoice->formattedId() . ' created.');
    }

    public function show(PurchaseInvoice $purchase_invoice)
    {
        $purchase_invoice->load(['items.product', 'supplier']);
        return view('admin.purchase-invoice-show', ['invoice' => $purchase_invoice]);
    }

    public function edit(PurchaseInvoice $purchase_invoice)
    {
        $purchase_invoice->load('items.product');
        return view('admin.purchase-invoice-form', [
            'invoice'   => $purchase_invoice,
            'suppliers' => Customer::active()->orderBy('name')->get(),
            'products'  => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, PurchaseInvoice $purchase_invoice)
    {
        $data = $this->validateInvoice($request);
        $invoice = DB::transaction(fn () => $this->persistInvoice($purchase_invoice, $data));
        return redirect()->route('purchase-invoice')->with('success', 'Purchase invoice ' . $invoice->formattedId() . ' updated.');
    }

    public function destroy(Request $request, PurchaseInvoice $purchase_invoice)
    {
        // Reverse inventory quantity AND weight, then recompute weighted average.
        DB::transaction(function () use ($purchase_invoice) {
            $productIds = [];
            foreach ($purchase_invoice->items as $item) {
                $inv = Inventory::where('product_id', $item->product_id)->first();
                if ($inv) {
                    $inv->quantity = max(0, (float) $inv->quantity - (float) $item->quantity);
                    $inv->weight   = max(0, (float) $inv->weight   - (float) $item->weight);
                    $inv->save();
                }
                $productIds[] = $item->product_id;
            }
            $purchase_invoice->delete();

            foreach (array_unique($productIds) as $pid) {
                $inv = Inventory::where('product_id', $pid)->first();
                if ($inv) {
                    $inv->recalcWeightedAvg();
                }
            }
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('purchase-invoice')->with('success', 'Purchase invoice deleted.');
    }

    public function print(PurchaseInvoice $purchase_invoice)
    {
        $purchase_invoice->load(['items.product', 'supplier']);
        $data = ['invoice' => $purchase_invoice];

        // Direct PDF download (A5 portrait) when DomPDF is installed.
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $filename = 'purchase-invoice-' . $purchase_invoice->formattedId() . '.pdf';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.purchase-invoice-print', $data)
                ->setPaper('a5', 'portrait');
            return $pdf->stream($filename);
        }

        return view('admin.purchase-invoice-print', $data + ['browserPrint' => true]);
    }

    /* ---------- helpers ---------- */

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'supplier_id'        => 'required|exists:customers,id',
            'bill_date'          => 'required|date',
            'description'        => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity'   => 'required|numeric|min:0',
            'items.*.price'      => 'required|numeric|min:0',
            'items.*.weight'     => 'required|numeric|min:0',
        ], [
            'supplier_id.required' => 'Supplier is required.',
            'items.required'       => 'Add at least one line item.',
            'items.*.product_id.required' => 'Select a product for each row.',
        ]);
    }

    private function persistInvoice(?PurchaseInvoice $existing, array $data): PurchaseInvoice
    {
        $itemsResolved = [];
        $total = 0;
        foreach ($data['items'] as $row) {
            // Amount = Price * Weight (per client request)
            $amount = round(((float) $row['price']) * ((float) $row['weight']), 2);
            $total += $amount;
            $itemsResolved[] = [
                'product_id' => $row['product_id'],
                'quantity'   => (float) $row['quantity'],
                'price'      => (float) $row['price'],
                'weight'     => (float) $row['weight'],
                'amount'     => $amount,
            ];
        }
        $total = round($total, 2);

        if ($existing) {
            // Reverse old quantities AND weights from inventory first
            foreach ($existing->items as $old) {
                $inv = Inventory::where('product_id', $old->product_id)->first();
                if ($inv) {
                    $inv->quantity = max(0, (float) $inv->quantity - (float) $old->quantity);
                    $inv->weight   = max(0, (float) $inv->weight   - (float) $old->weight);
                    $inv->save();
                }
            }
            $existing->items()->delete();
            $existing->update([
                'supplier_id'  => $data['supplier_id'] ?? null,
                'bill_date'    => $data['bill_date'],
                'description'  => $data['description'] ?? null,
                'total_amount' => $total,
            ]);
            $invoice = $existing;
        } else {
            $invoice = PurchaseInvoice::create([
                'supplier_id'  => $data['supplier_id'] ?? null,
                'bill_date'    => $data['bill_date'],
                'description'  => $data['description'] ?? null,
                'total_amount' => $total,
            ]);
        }

        foreach ($itemsResolved as $row) {
            $row['purchase_invoice_id'] = $invoice->id;
            PurchaseInvoiceItem::create($row);

            // Update / create inventory record
            $inv = Inventory::firstOrCreate(
                ['product_id' => $row['product_id']],
                ['quantity' => 0, 'price' => 0, 'weight' => 0, 'avg_price' => 0]
            );

            // Add quantity and weight to existing stock; keep latest unit price
            $inv->quantity = (float) $inv->quantity + (float) $row['quantity'];
            $inv->weight   = (float) $inv->weight   + (float) $row['weight'];
            $inv->price    = (float) $row['price'];
            $inv->save();

            // Weighted average = total purchase amount / total purchase weight
            $inv->recalcWeightedAvg();
        }

        return $invoice->fresh(['items', 'supplier']);
    }
}
