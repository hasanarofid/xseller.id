<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ProductController extends Controller
{
    /**
     * Display the products directory and CRUD management page.
     */
    public function index(Request $request)
    {
        $type = $request->input('type'); // ro, po, or null (all)

        $query = Product::query();

        if (in_array($type, ['ro', 'po'])) {
            $query->where('type', $type);
        }

        $products = $query->latest()->get();

        return Inertia::render('Admin/Products/Index', [
            'products' => $products,
            'current_type' => $type ?: 'all',
        ]);
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:ro,po',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image_file' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string',
        ]);

        $imagePath = $request->input('image_url');

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $imagePath = Storage::url($path);
        }

        Product::create([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'points' => $validated['points'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'image' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan!');
    }

    /**
     * Update the specified product in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'type' => 'required|in:ro,po',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
            'points' => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'image_file' => 'nullable|image|max:2048',
            'image_url' => 'nullable|string',
        ]);

        $imagePath = $product->image;

        if ($request->hasFile('image_file')) {
            $path = $request->file('image_file')->store('products', 'public');
            $imagePath = Storage::url($path);
        } elseif ($request->filled('image_url')) {
            $imagePath = $request->input('image_url');
        }

        $product->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'price' => $validated['price'],
            'quantity' => $validated['quantity'],
            'points' => $validated['points'],
            'description' => $validated['description'] ?? null,
            'is_active' => $request->boolean('is_active', true),
            'image' => $imagePath,
        ]);

        return redirect()->back()->with('success', 'Data produk berhasil diperbarui!');
    }

    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus!');
    }
}
