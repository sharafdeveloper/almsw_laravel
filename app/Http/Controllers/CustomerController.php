<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    public const CITIES = [
        'Karachi', 'Lahore', 'Islamabad', 'Rawalpindi', 'Faisalabad', 'Multan',
        'Peshawar', 'Quetta', 'Sialkot', 'Gujranwala', 'Hyderabad', 'Bahawalpur',
        'Sargodha', 'Sukkur', 'Larkana', 'Sheikhupura', 'Mardan', 'Mirpur Khas',
        'Rahim Yar Khan', 'Kasur', 'Sahiwal', 'Okara', 'Wah Cantt', 'Dera Ghazi Khan',
        'Mingora', 'Nawabshah', 'Chiniot', 'Kotri', 'Kamoke', 'Hafizabad',
        'Sadiqabad', 'Mianwali', 'Tando Adam', 'Jaranwala', 'Khanewal', 'Burewala',
        'Kohat', 'Muzaffargarh', 'Khanpur', 'Gojra', 'Bahawalnagar', 'Muridke',
        'Pakpattan', 'Abottabad', 'Tando Allahyar', 'Jhang', 'Mansehra', 'Other',
    ];

    // public function index()
    // {
    //     $customers = Customer::active()->orderBy('id', 'asc')->paginate(15);
    //     return view('admin.customers', [
    //         'customers' => $customers,
    //         'cities'    => self::CITIES,
    //     ]);
    // }

        public function index(Request $request)
    {
        $q = $request->input('q', '');
        $customers = Customer::active()
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('id', 'asc')
            ->paginate(15)
            ->withQueryString();
        return view('admin.customers', [
            'customers' => $customers,
            'cities'    => self::CITIES,
            'q'         => $q,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'city'            => 'required|string|max:100',
            'phone_number'   => 'required|string|max:20',
            'opening_amount'  => 'nullable|numeric|min:0',
            'balance_type'    => 'required|in:dr,cr',
        ]);

        $customer = Customer::create([
            'name'            => $data['name'],
            'city'            => $data['city'],
            'phone_number'   => $data['phone_number'],
            'opening_balance' => $this->signedBalance($data['balance_type'], $data['opening_amount'] ?? 0),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }
        return redirect()->route('customers')->with('success', 'Customer added.');
    }

    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'phone_number'   => 'required|string|max:20',
            'city'            => 'required|string|max:100',
            'opening_amount'  => 'nullable|numeric|min:0',
            'balance_type'    => 'required|in:dr,cr',
        ]);

        $customer->update([
            'name'            => $data['name'],
            'phone_number'   => $data['phone_number'],
            'city'            => $data['city'],
            'opening_balance' => $this->signedBalance($data['balance_type'], $data['opening_amount'] ?? 0),
        ]);

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'customer' => $customer]);
        }
        return redirect()->route('customers')->with('success', 'Customer updated.');
    }

    /**
     * Dr (debit) = customer owes us  => positive balance.
     * Cr (credit) = we owe customer  => negative balance.
     */
    private function signedBalance(string $type, $amount): float
    {
        $amount = abs((float) $amount);
        return $type === 'cr' ? -$amount : $amount;
    }

    public function destroy(Request $request, Customer $customer)
    {
        $hasInvoices = $customer->saleInvoices()->exists();
        $hasPayments = $customer->payments()->exists();
        $hasPurchases = $customer->purchaseInvoices()->exists();

        if ($hasInvoices || $hasPayments || $hasPurchases) {
            $customer->is_deleted = true;
            $customer->save();
            $msg = 'Customer archived (had related records).';
        } else {
            $customer->delete();
            $msg = 'Customer deleted.';
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $msg]);
        }
        return redirect()->route('customers')->with('success', $msg);
    }

 
//  public function ledger(Request $request, Customer $customer)
// {
//     $date = $request->input('date');

//     // Pehle saare rows bina filter ke calculate karo
//     $allRows = $customer->buildLedger(null, null);

//     // Opening aur closing hamesha full ledger se lo
//     $opening        = (float) $customer->opening_balance;
//     $closingBalance = !empty($allRows) ? end($allRows)['balance'] : $opening;

//     // Ab filtered rows lo
//     $rows = $customer->buildLedger($date ?: null, $date ?: null);

//     return view('admin.customer-ledger', [
//         'customer'       => $customer,
//         'rows'           => $rows,
//         'date'           => $date ?? '',
//         'opening'        => $opening,
//         'closingBalance' => $closingBalance,
//     ]);
// }
   
// public function ledger(Request $request, Customer $customer)
// {
//     $from = $request->input('from');
//     $to   = $request->input('to');

//     $allRows = $customer->buildLedger(null, null);

//     $opening        = (float) $customer->opening_balance;
//     $closingBalance = !empty($allRows) ? end($allRows)['balance'] : $opening;

//     $rows = $customer->buildLedger($from ?: null, $to ?: null);

//     return view('admin.customer-ledger', [
//         'customer'       => $customer,
//         'rows'           => $rows,
//         'from'           => $from ?? '',
//         'to'             => $to ?? '',
//         'opening'        => $opening,
//         'closingBalance' => $closingBalance,
//     ]);
// }



public function ledger(Request $request, Customer $customer)
{
    $from = $request->input('from') ?: null;
    $to   = $request->input('to')   ?: null;

    // Full ledger (no filter) — balance calculation ke liye
    $allRows = $customer->buildLedger(null, null);

    // 1. OPENING BALANCE for the filtered view:
    //    from-date se pehle ka last row ka balance.
    //    Agar from filter nahi lagaya to customer ka original opening_balance.
    if ($from) {
        $beforeRows = array_filter($allRows, fn($r) => $r['date'] < $from);
        $lastBefore = !empty($beforeRows) ? end($beforeRows) : null;
        $opening = $lastBefore ? (float) $lastBefore['balance'] : (float) $customer->opening_balance;
    } else {
        $opening = (float) $customer->opening_balance;
    }

    // 2. FILTERED ROWS — sirf from..to ke beech wale
    if ($from || $to) {
        $filteredRows = array_values(array_filter($allRows, function ($r) use ($from, $to) {
            if ($from && $r['date'] < $from) return false;
            if ($to   && $r['date'] > $to)   return false;
            return true;
        }));
    } else {
        $filteredRows = $allRows;
    }

    // 3. CLOSING BALANCE:
    //    Filtered rows mein se last row ka balance.
    //    Agar filtered rows empty hain to opening hi closing hai.
    $closingBalance = !empty($filteredRows)
        ? (float) end($filteredRows)['balance']
        : $opening;

    return view('admin.customer-ledger', [
        'customer'       => $customer,
        'rows'           => $filteredRows,
        'from'           => $from ?? '',
        'to'             => $to ?? '',
        'opening'        => $opening,
        'closingBalance' => $closingBalance,
    ]);
}

public function printLedger(Request $request, Customer $customer)
{
    $from = $request->input('from') ?: null;
    $to   = $request->input('to')   ?: null;

    $allRows = $customer->buildLedger(null, null);

    if ($from) {
        $beforeRows = array_filter($allRows, fn($r) => $r['date'] < $from);
        $lastBefore = !empty($beforeRows) ? end($beforeRows) : null;
        $opening = $lastBefore ? (float) $lastBefore['balance'] : (float) $customer->opening_balance;
    } else {
        $opening = (float) $customer->opening_balance;
    }

    if ($from || $to) {
        $filteredRows = array_values(array_filter($allRows, function ($r) use ($from, $to) {
            if ($from && $r['date'] < $from) return false;
            if ($to   && $r['date'] > $to)   return false;
            return true;
        }));
    } else {
        $filteredRows = $allRows;
    }

    $closingBalance = !empty($filteredRows)
        ? (float) end($filteredRows)['balance']
        : $opening;

    $filename = 'customer-ledger-' . Str::slug($customer->name ?: 'ledger') . '-' . ($from ?: 'all') . '-to-' . ($to ?: 'all') . '.pdf';

    if (class_exists(Pdf::class)) {
        $pdf = Pdf::loadView('admin.customer-ledger-print', [
            'customer'       => $customer,
            'rows'           => $filteredRows,
            'from'           => $from,
            'to'             => $to,
            'opening'        => $opening,
            'closingBalance' => $closingBalance,
        ]);

        return $pdf->stream($filename);
    }

    return view('admin.customer-ledger-print', [
        'customer'       => $customer,
        'rows'           => $filteredRows,
        'from'           => $from,
        'to'             => $to,
        'opening'        => $opening,
        'closingBalance' => $closingBalance,
    ]);
}

}
