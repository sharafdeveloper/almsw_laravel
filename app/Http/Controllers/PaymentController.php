<?php

namespace App\Http\Controllers;

use App\Models\CashbookEntry;
use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public const METHODS = ['Cash', 'Bank Transfer', 'Cheque', 'Online Transfer', 'Expense','Discount'];

    public function index(Request $request)
    {
        $q       = trim((string) $request->input('q', ''));
        $from    = $request->input('from');
        $to      = $request->input('to');
        $type    = $request->input('type'); // received | paid | null

        $query = Payment::with('customer')
            ->whereRaw('LOWER(COALESCE(method, "")) <> ?', ['discount'])
            ->orderBy('payment_date', 'asc')
            ->orderBy('id', 'asc');

        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('description', 'like', "%{$q}%")
                  ->orWhere('method', 'like', "%{$q}%")
                  ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$q}%"));
            });
        }
        if ($from) $query->where('payment_date', '>=', $from);
        if ($to)   $query->where('payment_date', '<=', $to);
        if ($type) $query->where('type', $type);

        $payments  = $query->paginate(15)->withQueryString();
        $customers = Customer::active()->orderBy('name')->get();

        return view('admin.payments', [
            'payments'  => $payments,
            'customers' => $customers,
            'methods'   => self::METHODS,
            'methods_in'     => ['Cash', 'Bank Transfer', 'Cheque', 'Online Transfer','Discount'],
            'methods_out'    => ['Cash', 'Bank Transfer', 'Cheque', 'Online Transfer', 'Expense','Discount'],
            'filters'   => compact('q', 'from', 'to', 'type'),
        ]);
    }

    /**
     * Combined Cash In + Cash Out submission.
     * Either side may be omitted (omit by leaving customer_id blank or amount = 0).
     */
    public function store(Request $request)
    {
        $request->validate([
            // Cash In side
            'cash_in.customer_id'    => 'nullable|exists:customers,id',
            'cash_in.payment_date'   => 'nullable|date',
            'cash_in.amount'         => 'nullable|numeric|min:0',
            'cash_in.method'         => 'nullable|string|max:50',
            'cash_in.description'    => 'nullable|string|max:500',

            // Cash Out side
            'cash_out.customer_id'   => 'nullable|exists:customers,id',
            'cash_out.payment_date'  => 'nullable|date',
            'cash_out.amount'        => 'nullable|numeric|min:0',
            'cash_out.method'        => 'nullable|string|max:50',
            'cash_out.description'   => 'nullable|string|max:500',
        ]);

        $in  = $request->input('cash_in', []);
        $out = $request->input('cash_out', []);

        $createdAny = false;

        DB::transaction(function () use ($in, $out, &$createdAny) {
            $isDiscountIn = trim(strtolower((string) ($in['method'] ?? ''))) === 'discount';
            if ((!empty($in['customer_id']) || $isDiscountIn) && !empty($in['amount']) && (float) $in['amount'] > 0) {
                $p = Payment::create([
                    'customer_id'  => $in['customer_id'] ?? null,
                    'payment_date' => $in['payment_date'] ?? now()->toDateString(),
                    'amount'       => (float) $in['amount'],
                    'type'         => 'received',
                    'method'       => $in['method'] ?? null,
                    'description'  => $in['description'] ?? null,
                ]);
                $this->syncCashbook($p);
                $createdAny = true;
            }

            $isDiscountOut = trim(strtolower((string) ($out['method'] ?? ''))) === 'discount';
            if ((!empty($out['customer_id']) || $isDiscountOut) && !empty($out['amount']) && (float) $out['amount'] > 0) {
                $p = Payment::create([
                    'customer_id'  => $out['customer_id'] ?? null,
                    'payment_date' => $out['payment_date'] ?? now()->toDateString(),
                    'amount'       => (float) $out['amount'],
                    'type'         => 'paid',
                    'method'       => $out['method'] ?? null,
                    'description'  => $out['description'] ?? null,
                ]);
                $this->syncCashbook($p);
                $createdAny = true;
            }
        });

        if (!$createdAny) {
            return redirect()->route('payments')->with('error', 'Please fill at least one side (Cash In or Cash Out).');
        }
        return redirect()->route('payments')->with('success', 'Payment(s) saved.');
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'customer_id'  => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'type'         => 'required|in:received,paid',
            'method'       => 'nullable|string|max:50',
            'description'  => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($payment, $data) {
            $payment->update($data);
            CashbookEntry::where('source_type', Payment::class)
                ->where('source_id', $payment->id)->delete();
            $this->syncCashbook($payment);
        });

        if ($request->expectsJson()) return response()->json(['success' => true]);
        return redirect()->route('payments')->with('success', 'Payment updated.');
    }

    public function destroy(Request $request, Payment $payment)
    {
        DB::transaction(function () use ($payment) {
            CashbookEntry::where('source_type', Payment::class)
                ->where('source_id', $payment->id)->delete();
            $payment->delete();
        });
        if ($request->expectsJson()) return response()->json(['success' => true]);
        return redirect()->route('payments')->with('success', 'Payment deleted.');
    }

    private function syncCashbook(Payment $payment): void
    {
        if (trim(strtolower((string) $payment->method)) === 'discount') {
            return; // Discount payments should not post to cashbook as cash-out entries.
        }

        CashbookEntry::create([
            'entry_date'  => $payment->payment_date,
            'type'        => $payment->type === 'received' ? 'in' : 'out',
            'amount'      => $payment->amount,
            'description' => ($payment->type === 'received' ? 'Payment received' : 'Payment paid')
                . ' - ' . optional($payment->customer)->name
                . ($payment->description ? ' (' . $payment->description . ')' : ''),
            'source_type' => Payment::class,
            'source_id'   => $payment->id,
        ]);
    }
}
