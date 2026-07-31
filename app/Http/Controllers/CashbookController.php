<?php

namespace App\Http\Controllers;

use App\Models\BusinessSetting;
use App\Models\CashbookEntry;
use App\Models\Payment;
use App\Models\SaleInvoice;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CashbookController extends Controller
{
    private const OPENING_BALANCE_KEY = 'cashbook_opening_balance';

    public function index(Request $request)
    {
        [$from, $to, $opening, $entries, $totals] = $this->compute($request);

        // Paginate the computed entries for the web view (15 per page)
        $page = (int) $request->input('page', 1);
        $perPage = 15;
        $total = count($entries);
        $slice = array_slice($entries, ($page - 1) * $perPage, $perPage);
        $paginator = new LengthAwarePaginator($slice, $total, $perPage, $page, [
            'path'  => Paginator::resolveCurrentPath(),
            'query' => $request->query(),
        ]);

        return view('admin.cashbook', [
            'entries'        => $paginator,
            'from'           => $from,
            'to'             => $to,
            'opening'        => $opening,
            'totalIn'        => $totals['in'],
            'totalOut'       => $totals['out'],
            'netMovement'    => $totals['net'],
            'closingBalance' => $totals['closing'],
            'monthly'        => $this->monthlyBreakdown($entries, $opening),
        ]);
    }

    public function updateOpening(Request $request)
    {
        $request->validate(['opening_balance' => 'required|numeric']);
        BusinessSetting::set(self::OPENING_BALANCE_KEY, (float) $request->input('opening_balance'));
        return redirect()->route('cashbook')->with('success', 'Opening balance updated.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        [$from, $to, $opening, $entries, $totals] = $this->compute($request);

        $filename = 'cashbook_' . $from . '_to_' . $to . '.csv';

        return response()->streamDownload(function () use ($from, $to, $opening, $entries, $totals) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Cashbook Report']);
            fputcsv($out, ['From', $from, 'To', $to]);
            fputcsv($out, ['Opening Balance', number_format($opening, 2)]);
            fputcsv($out, []);
            fputcsv($out, ['Date', 'Description', 'Voucher/Ref', 'Cash In (Debit)', 'Cash Out (Credit)', 'Balance']);
            foreach ($entries as $e) {
                fputcsv($out, [
                    $e['date'],
                    $e['description'],
                    $e['voucher'],
                    $e['in']  > 0 ? number_format($e['in'], 2)  : '',
                    $e['out'] > 0 ? number_format($e['out'], 2) : '',
                    number_format($e['balance'], 2),
                ]);
            }
            fputcsv($out, []);
            fputcsv($out, ['Total Cash In',  number_format($totals['in'], 2)]);
            fputcsv($out, ['Total Cash Out', number_format($totals['out'], 2)]);
            fputcsv($out, ['Net Movement',   number_format($totals['net'], 2)]);
            fputcsv($out, ['Closing Balance',number_format($totals['closing'], 2)]);
            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function print(Request $request)
    {
        [$from, $to, $opening, $entries, $totals] = $this->compute($request);

        // Prepare paginated print pages: 15 Cash In and 15 Cash Out per printed page
        $cashIn = array_values(array_filter($entries, fn($e) => ($e['in'] ?? 0) > 0));
        $cashOut = array_values(array_filter($entries, fn($e) => ($e['out'] ?? 0) > 0));

        $chunkSize = 20;
        $inChunks = array_chunk($cashIn, $chunkSize);
        $outChunks = array_chunk($cashOut, $chunkSize);

        $pages = max(count($inChunks), count($outChunks));
        $printPages = [];
        for ($i = 0; $i < $pages; $i++) {
            $printPages[] = [
                'in' => $inChunks[$i] ?? [],
                'out' => $outChunks[$i] ?? [],
            ];
        }

        return view('admin.cashbook-print', compact('from', 'to', 'opening', 'totals', 'printPages'));
    }

    /**
     * Compute the entries for the period and build running balance starting from opening.
     * @return array{0:string,1:string,2:float,3:array,4:array}
     */
    private function compute(Request $request): array
    {
        $from = $request->input('from');
        $to   = $request->input('to');

        $defaultFrom = CashbookEntry::min('entry_date') ?: now()->startOfMonth()->toDateString();
        $from = $from ?: $defaultFrom;
        $to   = $to   ?: now()->toDateString();

        $opening = BusinessSetting::getFloat(self::OPENING_BALANCE_KEY, 0);

        // Add entries before $from to opening
        $before = (float) CashbookEntry::where('entry_date', '<', $from)->where('type', 'in')->sum('amount')
                - (float) CashbookEntry::where('entry_date', '<', $from)->where('type', 'out')->sum('amount');
        $runningOpening = $opening + $before;

        $raw = CashbookEntry::with('source')
            ->whereBetween('entry_date', [$from, $to])
            ->orderBy('entry_date')->orderBy('id')->get();

        $entries = [];
        $balance = $runningOpening;
        $totalIn = 0; $totalOut = 0;

        foreach ($raw as $e) {
            $in  = $e->type === 'in'  ? (float) $e->amount : 0;
            $out = $e->type === 'out' ? (float) $e->amount : 0;
            $balance = $balance + $in - $out;
            $totalIn  += $in;
            $totalOut += $out;

            // Prefer showing the payment method first, then the payment's description
            $description = $e->description;
            if ($e->source_type === Payment::class) {
                try {
                    $p = Payment::find($e->source_id);
                    if ($p) {
                        $parts = [];
                        if (!empty($p->method)) {
                            $parts[] = $p->method;
                        }
                        if (!empty($p->customer?->name)) {
                            $parts[] = $p->customer->name;
                        }
                        if (!empty($p->description)) {
                            $parts[] = $p->description;
                        } elseif (!empty($e->description)) {
                            $parts[] = $e->description;
                        }
                        if (!empty($parts)) {
                            $description = implode(' - ', $parts);
                        }
                    }
                } catch (\Throwable $ex) {
                    // ignore and fall back to stored description
                }
            }

            $entries[] = [
                'date'        => $e->entry_date->toDateString(),
                'type'        => $e->type === 'out' ? 'Cash Out' : 'Cash In',
                'description' => $description,
                'amount'      => round((float) $e->amount, 2),
                'voucher'     => $this->voucherFor($e),
                'in'          => $in,
                'out'         => $out,
                'balance'     => round($balance, 2),
                'raw'         => $e,
            ];
        }

        $totals = [
            'opening_for_range' => round($runningOpening, 2),
            'in'      => round($totalIn, 2),
            'out'     => round($totalOut, 2),
            'net'     => round($totalIn - $totalOut, 2),
            'closing' => round($balance, 2),
        ];

        return [$from, $to, $runningOpening, $entries, $totals];
    }

    private function voucherFor(CashbookEntry $e): string
    {
        if (!$e->source_type || $e->source_type === 'manual') return '-';
        try {
            if ($e->source_type === Payment::class) {
                $p = Payment::find($e->source_id);
                return $p ? $p->formattedId() : '-';
            }
            if ($e->source_type === SaleInvoice::class) {
                $s = SaleInvoice::find($e->source_id);
                return $s ? $s->formattedId() : '-';
            }
        } catch (\Throwable $ex) {
            // ignore
        }
        return '-';
    }

    /**
     * Group entries by year-month for monthly summary.
     */
    private function monthlyBreakdown(array $entries, float $opening): array
    {
        $groups = [];
        foreach ($entries as $e) {
            $ym = substr($e['date'], 0, 7);
            if (!isset($groups[$ym])) {
                $groups[$ym] = ['month' => $ym, 'in' => 0, 'out' => 0, 'closing' => 0];
            }
            $groups[$ym]['in']      += $e['in'];
            $groups[$ym]['out']     += $e['out'];
            $groups[$ym]['closing'] = $e['balance'];
        }
        $out = array_values($groups);
        foreach ($out as &$g) {
            $g['net']     = round($g['in'] - $g['out'], 2);
            $g['in']      = round($g['in'], 2);
            $g['out']     = round($g['out'], 2);
            $g['closing'] = round($g['closing'], 2);
        }
        return $out;
    }
}
