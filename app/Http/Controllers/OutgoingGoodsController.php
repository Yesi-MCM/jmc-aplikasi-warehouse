<?php

namespace App\Http\Controllers;

use App\Models\OutgoingGoods;
use App\Models\OutgoingGoodsDetail;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OutgoingGoodsController extends Controller
{
    public function create(): Response
    {
        $units = Unit::orderBy('name', 'asc')->get();
        return Inertia::render('OutgoingGoods/Create', [
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'receiver_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        try {
            DB::transaction(function () use ($request) {
                // 1. Create Outgoing Goods Header
                $outgoing = OutgoingGoods::create([
                    'unit_id' => $request->unit_id,
                    'receiver_name' => $request->receiver_name,
                    'contact_person' => $request->contact_person,
                    'phone_number' => $request->phone_number,
                    'notes' => $request->notes,
                    'created_by' => Auth::id() ?? 1,
                ]);

                // 2. Process each item deduction
                foreach ($request->items as $itemData) {
                    $itemId = $itemData['item_id'];
                    $qtyToDeduct = $itemData['quantity'];

                    // Fetch active stocks in this Unit
                    $stocks = InventoryStock::where('item_id', '=', $itemId, 'and')
                        ->whereHas('room', function ($q) use ($request) {
                            $q->where('unit_id', '=', $request->unit_id, 'and');
                        })
                        ->where('quantity', '>', 0, 'and')
                        ->orderBy('created_at', 'asc') // FIFO
                        ->get();

                    $totalAvailable = $stocks->sum('quantity');
                    if ($totalAvailable < $qtyToDeduct) {
                        $item = Item::find($itemId, ['*']);
                        throw new \Exception("Insufficient stock for item: {$item->name}. Available: {$totalAvailable}, Requested: {$qtyToDeduct}");
                    }

                    // Deduct from stocks in FIFO order
                    foreach ($stocks as $stock) {
                        if ($qtyToDeduct <= 0) {
                            break;
                        }

                        $deductedQty = min($stock->quantity, $qtyToDeduct);
                        
                        // Create detail line
                        OutgoingGoodsDetail::create([
                            'outgoing_goods_id' => $outgoing->id,
                            'item_id' => $itemId,
                            'room_id' => $stock->room_id,
                            'rack_id' => $stock->rack_id,
                            'tier' => $stock->tier,
                            'price' => $stock->price,
                            'quantity' => $deductedQty,
                            'unit_name' => $stock->item->unit_name,
                        ]);

                        // Subtract from database inventory
                        $stock->quantity -= $deductedQty;
                        $stock->save();

                        $qtyToDeduct -= $deductedQty;
                    }
                }
            });
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => $e->getMessage()])->withInput();
        }

        return redirect()->route('items.index')->with('success', 'Outgoing goods recorded successfully and stock deducted.');
    }

    // API to load items with active stocks in a unit
    public function getAvailableItems(Unit $unit): JsonResponse
    {
        $items = Item::whereHas('stocks', function ($q) use ($unit) {
            $q->whereHas('room', function ($qr) use ($unit) {
                $qr->where('unit_id', '=', $unit->id, 'and');
            })->where('quantity', '>', 0, 'and');
        })
        ->get()
        ->map(function ($item) use ($unit) {
            // Get available quantity and average price for estimate
            $stocks = $item->stocks()
                ->whereHas('room', function ($qr) use ($unit) {
                    $qr->where('unit_id', '=', $unit->id, 'and');
                })
                ->where('quantity', '>', 0, 'and')
                ->get();
            
            $availableQty = $stocks->sum('quantity');
            $avgPrice = $stocks->avg('price') ?? 0;

            return [
                'id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'unit_name' => $item->unit_name,
                'available_quantity' => $availableQty,
                'est_price' => (float)$avgPrice,
            ];
        });

        return response()->json($items);
    }
}
