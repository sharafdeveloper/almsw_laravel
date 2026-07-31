<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

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

    public function index()
    {
        $customers = Customer::active()->orderBy('id', 'asc')->paginate(15);
        return view('admin.customers', [
            'customers' => $customers,
            'cities'    => self::CITIES,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'city'            => 'required|string|max:100',
            'opening_amount'  => 'nullable|numeric|min:0',
            'balance_type'    => 'required|in:dr,cr',
        ]);

        $customer = Customer::create([
            'name'            => $data['name'],
            'city'            => $data['city'],
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
            'city'            => 'required|string|max:100',
            'opening_amount'  => 'nullable|numeric|min:0',
            'balance_type'    => 'required|in:dr,cr',
        ]);

        $customer->update([
            'name'            => $data['name'],
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

    public function ledger(Request $request, Customer $customer)
    {
        $from = $request->input('from');
        $to   = $request->input('to');
        $rows = $customer->buildLedger($from, $to);

        return view('admin.customer-ledger', [
            'customer' => $customer,
            'rows'     => $rows,
            'from'     => $from,
            'to'       => $to,
            'opening'  => (float) $customer->opening_balance,
        ]);
    }
}
