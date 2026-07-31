<?php

namespace App\Http\Controllers;

use App\Models\CashbookEntry;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\SaleInvoice;
use App\Models\SaleInvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SaleInvoiceController extends Controller
{
    public function index()
    {
        $invoices = SaleInvoice::with('customer')
            ->orderBy('id', 'asc')
            ->paginate(15);
        return view('admin.sale-invoice', compact('invoices'));
    }

    public function create()
    {
        return view('admin.sale-invoice-form', [
            'invoice'   => null,
            'customers' => Customer::active()->orderBy('name')->get(),
            'products'  => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validateInvoice($request);
        $invoice = DB::transaction(fn () => $this->persistInvoice(null, $data));
        return redirect()->route('sale-invoice.show', $invoice)->with('success', 'Sale invoice created.');
    }

    public function show(SaleInvoice $sale_invoice)
    {
        $sale_invoice->load(['items.product', 'customer']);
        return view('admin.sale-invoice-show', ['invoice' => $sale_invoice]);
    }

    public function edit(SaleInvoice $sale_invoice)
    {
        $sale_invoice->load('items.product');
        return view('admin.sale-invoice-form', [
            'invoice'   => $sale_invoice,
            'customers' => Customer::active()->orderBy('name')->get(),
            'products'  => Product::active()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, SaleInvoice $sale_invoice)
    {
        $data = $this->validateInvoice($request);
        $invoice = DB::transaction(fn () => $this->persistInvoice($sale_invoice, $data));
        return redirect()->route('sale-invoice.show', $invoice)->with('success', 'Sale invoice updated.');
    }

    public function destroy(Request $request, SaleInvoice $sale_invoice)
    {
        DB::transaction(function () use ($sale_invoice) {
            // Reverse inventory: add back BOTH quantity and weight that were sold
            foreach ($sale_invoice->items as $item) {
                $inv = Inventory::where('product_id', $item->product_id)->first();
                if ($inv) {
                    $inv->quantity = (float) $inv->quantity + (float) $item->quantity;
                    $inv->weight   = (float) $inv->weight   + (float) $item->weight;
                    $inv->save();
                }
            }
            // Remove related cashbook entries
            CashbookEntry::where('source_type', SaleInvoice::class)
                ->where('source_id', $sale_invoice->id)->delete();
            $sale_invoice->delete();
        });

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return redirect()->route('sale-invoice')->with('success', 'Sale invoice deleted.');
    }

    public function print(SaleInvoice $sale_invoice)
    {
        $sale_invoice->load(['items.product', 'customer']);
        $data = ['invoice' => $sale_invoice];

        // Direct PDF download (A5 portrait) when DomPDF is installed.
        if (class_exists(\Barryvdh\DomPDF\Facade\Pdf::class)) {
            $filename = 'sale-invoice-' . $sale_invoice->formattedId() . '.pdf';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.sale-invoice-print', $data)
                ->setPaper('a5', 'portrait');
            return $pdf->download($filename);
        }

        // Fallback: HTML view that auto-opens the browser print dialog.
        return view('admin.sale-invoice-print', $data + ['browserPrint' => true]);
    }

    /* ---------- helpers ---------- */

    private function validateInvoice(Request $request): array
    {
        return $request->validate([
            'customer_id'        => 'required|exists:customers,id',
            'bill_date'          => 'required|date',
            'description'        => 'nullable|string|max:1000',
            'labour_cost'        => 'required|numeric|min:0',
            'loading'            => 'required|numeric|min:0',
            'cash_received'      => 'required|numeric|min:0',
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.rate'       => 'required|numeric|min:0',
            'items.*.quantity'   => 'required|numeric|min:0',
            'items.*.weight'     => 'required|numeric|min:0',
        ], [
            'customer_id.required' => 'Customer is required.',
            'items.required'       => 'Add at least one line item.',
            'items.*.product_id.required' => 'Select a product for each row.',
        ]);
    }

    private function persistInvoice(?SaleInvoice $existing, array $data): SaleInvoice
    {
        $labour  = (float) ($data['labour_cost']   ?? 0);
        $loading = (float) ($data['loading']       ?? 0);
        $cash    = (float) ($data['cash_received'] ?? 0);

        $subTotal = 0;
        $itemsResolved = [];
        foreach ($data['items'] as $row) {
            // Amount = Rate * Weight (per client confirmation)
            $amount = round(((float) $row['rate']) * ((float) $row['weight']), 2);
            $subTotal += $amount;
            $itemsResolved[] = [
                'product_id' => $row['product_id'],
                'rate'       => (float) $row['rate'],
                'quantity'   => (float) $row['quantity'],
                'weight'     => (float) $row['weight'],
                'amount'     => $amount,
            ];
        }
        $total = round($subTotal + $labour + $loading, 2);

        if ($existing) {
            // Reverse old item quantities AND weights from inventory before applying new ones
            foreach ($existing->items as $oldItem) {
                $inv = Inventory::where('product_id', $oldItem->product_id)->first();
                if ($inv) {
                    $inv->quantity = (float) $inv->quantity + (float) $oldItem->quantity;
                    $inv->weight   = (float) $inv->weight   + (float) $oldItem->weight;
                    $inv->save();
                }
            }
            $existing->items()->delete();
            $existing->update([
                'customer_id'   => $data['customer_id'],
                'bill_date'     => $data['bill_date'],
                'description'   => $data['description'] ?? null,
                'sub_total'     => $subTotal,
                'labour_cost'   => $labour,
                'loading'       => $loading,
                'total'         => $total,
                'cash_received' => $cash,
            ]);
            $invoice = $existing;

            // Replace cashbook entry
            CashbookEntry::where('source_type', SaleInvoice::class)
                ->where('source_id', $invoice->id)->delete();
        } else {
            $invoice = SaleInvoice::create([
                'customer_id'   => $data['customer_id'],
                'bill_date'     => $data['bill_date'],
                'description'   => $data['description'] ?? null,
                'sub_total'     => $subTotal,
                'labour_cost'   => $labour,
                'loading'       => $loading,
                'total'         => $total,
                'cash_received' => $cash,
            ]);
        }

        foreach ($itemsResolved as $row) {
            $row['sale_invoice_id'] = $invoice->id;
            SaleInvoiceItem::create($row);

            // Reduce inventory BOTH quantity AND weight by what was sold.
            // Example: purchased qty=1000, weight=2000. Sold qty=500, weight=1000.
            // Remaining inventory: qty=500, weight=1000.
            $inv = Inventory::where('product_id', $row['product_id'])->first();
            if ($inv) {
                $inv->quantity = (float) $inv->quantity - (float) $row['quantity'];
                $inv->weight   = (float) $inv->weight   - (float) $row['weight'];
                $inv->save();
            }
        }

        // Cashbook entry if cash received > 0
        if ($cash > 0) {
            CashbookEntry::create([
                'entry_date'  => $invoice->bill_date,
                'type'        => 'in',
                'amount'      => $cash,
                'description' => 'Cash received on Sale Invoice ' . $invoice->formattedId(),
                'source_type' => SaleInvoice::class,
                'source_id'   => $invoice->id,
            ]);
        }

        return $invoice->fresh(['items', 'customer']);
    }
}
