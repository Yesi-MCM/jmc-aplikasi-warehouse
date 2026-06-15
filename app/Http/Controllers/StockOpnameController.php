<?php

namespace App\Http\Controllers;

use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use App\Models\InventoryStock;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class StockOpnameController extends Controller
{
    public function index(): Response
    {
        $agendas = StockOpname::with(['unit'])
            ->latest()
            ->get()
            ->map(function ($so) {
                // Calculate checked percentage
                $totalDetails = $so->details()->count('*');
                $checkedDetails = $so->details()->where('physical_quantity', '>=', 0, 'and')->count('*'); // if default is -1 or unchecked
                
                // Qty comparison
                $totalSystemQty = $so->details()->sum('system_quantity');
                $totalPhysicalQty = $so->details()->sum('physical_quantity');

                return [
                    'id' => $so->id,
                    'date' => $so->date,
                    'title' => $so->title,
                    'unit' => $so->unit->name,
                    'status' => $so->status,
                    'checked_by' => $so->checked_by,
                    'total_items' => $totalSystemQty,
                    'checked_items' => $totalPhysicalQty,
                    'percentage' => $totalDetails > 0 ? round(($checkedDetails / $totalDetails) * 100) : 0,
                ];
            });

        $units = Unit::orderBy('name', 'asc')->get();

        return Inertia::render('StockOpname/Index', [
            'agendas' => $agendas,
            'units' => $units,
        ]);
    }

    public function show(StockOpname $stockOpname): Response
    {
        $stockOpname->load('unit');
        
        $details = $stockOpname->details()
            ->with(['item', 'room', 'rack'])
            ->get()
            ->map(function ($detail) {
                return [
                    'id' => $detail->id,
                    'location' => $detail->room->name . ', ' . $detail->rack->name . ', Tingkat ' . $detail->tier,
                    'item_code' => $detail->item->code,
                    'item_name' => $detail->item->name,
                    'unit_name' => $detail->item->unit_name,
                    'price' => (float)$detail->price,
                    'expiry' => $detail->expiry_date ? date('Y-m-d', strtotime($detail->expiry_date)) : '-',
                    'system_qty' => $detail->system_quantity,
                    'physical_qty' => $detail->physical_quantity,
                    'notes' => $detail->notes ?? '',
                ];
            });

        // Summary details
        $totalSystemQty = $details->sum('system_qty');
        $totalPhysicalQty = $details->sum('physical_qty');
        $totalLocations = $details->pluck('location')->unique()->count();
        $totalItems = $details->count();
        $checkedItems = $stockOpname->details()->where('physical_quantity', '>=', 0, 'and')->whereNotNull('physical_quantity')->count('*');
        $percentage = $totalItems > 0 ? round(($checkedItems / $totalItems) * 100) : 0;

        return Inertia::render('StockOpname/Show', [
            'agenda' => [
                'id' => $stockOpname->id,
                'title' => $stockOpname->title,
                'unit_name' => $stockOpname->unit->name,
                'date' => $stockOpname->date,
                'status' => $stockOpname->status,
                'checked_by' => $stockOpname->checked_by,
                'notes' => $stockOpname->notes ?? '',
                'attachment_path' => $stockOpname->attachment_path,
                'total_locations' => $totalLocations,
                'total_system_qty' => $totalSystemQty,
                'total_physical_qty' => $totalPhysicalQty,
                'percentage' => $percentage,
            ],
            'details' => $details,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'unit_id' => 'required|exists:units,id',
            'date' => 'required|date',
            'checked_by' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png,bmp,doc,docx,xls,xlsx|max:8192',
        ]);

        DB::transaction(function () use ($request) {
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('stock_opnames', 'public');
            }

            $so = StockOpname::create([
                'title' => $request->title,
                'unit_id' => $request->unit_id,
                'date' => $request->date,
                'checked_by' => $request->checked_by,
                'notes' => $request->notes,
                'attachment_path' => $attachmentPath,
                'status' => 'draft',
            ]);

            // Snap active stocks for this Unit
            $stocks = InventoryStock::whereHas('room', function ($q) use ($request) {
                $q->where('unit_id', $request->unit_id);
            })->get();

            foreach ($stocks as $stock) {
                StockOpnameDetail::create([
                    'stock_opname_id' => $so->id,
                    'inventory_stock_id' => $stock->id,
                    'item_id' => $stock->item_id,
                    'room_id' => $stock->room_id,
                    'rack_id' => $stock->rack_id,
                    'tier' => $stock->tier,
                    'price' => $stock->price,
                    'expiry_date' => $stock->expiry_date,
                    'system_quantity' => $stock->quantity,
                    'physical_quantity' => $stock->quantity, // default to matching system count
                    'notes' => null,
                ]);
            }
        });

        return redirect()->route('stock-opname.index')->with('success', 'Stock opname agenda started successfully.');
    }

    public function updateDetail(Request $request, StockOpnameDetail $detail): RedirectResponse
    {
        $request->validate([
            'physical_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $detail->update([
            'physical_quantity' => $request->physical_quantity,
            'notes' => $request->notes,
        ]);

        return redirect()->back()->with('success', 'Item count updated.');
    }

    public function finalize(StockOpname $stockOpname): RedirectResponse
    {
        if ($stockOpname->status === 'completed') {
            return redirect()->back()->with('error', 'This stock opname is already completed.');
        }

        DB::transaction(function () use ($stockOpname) {
            $stockOpname->update(['status' => 'completed']);

            // Adjust inventory stocks
            foreach ($stockOpname->details as $detail) {
                if ($detail->inventory_stock_id) {
                    $stock = InventoryStock::find($detail->inventory_stock_id, ['*']);
                    if ($stock) {
                        $stock->quantity = $detail->physical_quantity;
                        $stock->save();
                    }
                } else {
                    // Create new stock if not existed originally
                    InventoryStock::create([
                        'item_id' => $detail->item_id,
                        'room_id' => $detail->room_id,
                        'rack_id' => $detail->rack_id,
                        'tier' => $detail->tier,
                        'price' => $detail->price,
                        'expiry_date' => $detail->expiry_date,
                        'quantity' => $detail->physical_quantity,
                    ]);
                }
            }
        });

        return redirect()->route('stock-opname.show', $stockOpname->id)->with('success', 'Stock opname completed. Inventory adjusted.');
    }
}
