<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ItemController extends Controller
{
    public function index(): Response
    {
        $items = Item::with(['category', 'stocks.room.unit', 'stocks.rack'])
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($item) {
                $stocks = $item->stocks->where('quantity', '>', 0);
                
                $totalQuantity = $stocks->sum('quantity');
                
                $prices = $stocks->pluck('price')->unique()->sort();
                if ($prices->isEmpty()) {
                    $priceRange = '-';
                } elseif ($prices->count() === 1) {
                    $priceRange = 'Rp ' . number_format($prices->first(), 0, ',', '.');
                } else {
                    $priceRange = 'Rp ' . number_format($prices->first(), 0, ',', '.') . ' - Rp ' . number_format($prices->last(), 0, ',', '.');
                }

                $stockDetails = $stocks->map(function ($stock) {
                    $location = $stock->room->name . ', ' . $stock->rack->name . ', Tingkat ' . $stock->tier;
                    if ($stock->room->unit) {
                        $location = '[' . $stock->room->unit->name . '] ' . $location;
                    }
                    return [
                        'id' => $stock->id,
                        'location' => $location,
                        'expiry' => $stock->expiry_date ? date('Y-m-d', strtotime($stock->expiry_date)) : '-',
                        'price' => (float)$stock->price,
                        'quantity' => $stock->quantity,
                    ];
                })->values();

                return [
                    'id' => $item->id,
                    'category' => $item->category->name,
                    'code' => $item->code,
                    'name' => $item->name,
                    'unit_name' => $item->unit_name,
                    'price_range' => $priceRange,
                    'total_quantity' => $totalQuantity,
                    'stocks' => $stockDetails,
                ];
            });

        $categories = Category::orderBy('name', 'asc')->get();

        return Inertia::render('Items/Index', [
            'items' => $items,
            'categories' => $categories,
        ]);
    }
}
