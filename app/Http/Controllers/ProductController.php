<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of products (not deleted).
     */
    // public function index()
    // {
    //     $products = Product::active()->orderBy('id', 'asc')->paginate(10);
    //     return view('admin.products', compact('products'));
    // }

        public function index(Request $request)
    {
        $q = $request->input('q', '');
        $products = Product::active()
            ->when($q, fn($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->withQueryString();
        return view('admin.products', compact('products', 'q'));
    }

    /**
     * Store a newly created product.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $product = Product::create(['name' => $data['name']]);
        $msg = 'Product created successfully.';
        if ($request->expectsJson()) {
            session()->flash('success', $msg);
            return response()->json(['success' => true, 'product' => $product, 'message' => $msg], 201);
        }

        return redirect()->route('products')->with('success', $msg);
    }

    /**
     * Update the specified product.
     */
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        if ($product->is_deleted) {
            return redirect()->route('products')->with('error', 'Cannot update a deleted product.');
        }

        $product->update(['name' => $data['name']]);

        $msg = 'Product updated successfully.';
        if ($request->expectsJson()) {
            session()->flash('success', $msg);
            return response()->json(['success' => true, 'product' => $product, 'message' => $msg], 200);
        }

        return redirect()->route('products')->with('success', $msg);
    }

    /**
     * Soft-delete the specified product. If product has references, soft-delete still preserves integrity.
     */
    public function destroy(\Illuminate\Http\Request $request, Product $product)
    {
        if ($product->is_deleted) {
            if ($request->expectsJson()) {
                session()->flash('error', 'Product already deleted.');
                return response()->json(['success' => false, 'message' => 'Product already deleted.'], 400);
            }

            return redirect()->route('products')->with('error', 'Product already deleted.');
        }

        // Check common dependency models if they exist and count references
        $dependencyCount = 0;
        $refs = [
            \App\Models\SaleInvoiceItem::class => 'sale invoice items',
            \App\Models\PurchaseInvoiceItem::class => 'purchase invoice items',
            \App\Models\Inventory::class => 'inventory entries',
        ];

        foreach ($refs as $class => $label) {
            if (class_exists($class)) {
                try {
                    $dependencyCount += $class::where('product_id', $product->id)->count();
                } catch (\Throwable $e) {
                    // ignore any unexpected errors while checking references
                }
            }
        }

        // Perform permanent delete (developer warned earlier about references)
        try {
            $product->delete();
        } catch (\Throwable $e) {
            if ($request->expectsJson()) {
                session()->flash('error', 'Failed to delete product.');
                return response()->json(['success' => false, 'message' => 'Failed to delete product.'], 500);
            }

            return redirect()->route('products')->with('error', 'Failed to delete product.');
        }

        // Set session flash so a reload shows the success message for both AJAX and non-AJAX flows
        $msg = $dependencyCount > 0 ? "Product deleted. Referenced in {$dependencyCount} related records." : 'Product deleted successfully.';
        if ($request->expectsJson()) {
            session()->flash('success', $msg);
            return response()->json(['success' => true, 'deleted' => true, 'references' => $dependencyCount, 'message' => $msg], 200);
        }

        return redirect()->route('products')->with('success', $msg);
    }
}
