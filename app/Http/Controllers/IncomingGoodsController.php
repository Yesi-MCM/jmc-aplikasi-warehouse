<?php

namespace App\Http\Controllers;

use App\Models\IncomingGoods;
use App\Models\IncomingGoodsDetail;
use App\Models\InventoryStock;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class IncomingGoodsController extends Controller
{
    public function create(): Response
    {
        $items = Item::orderBy('name', 'asc')->get();
        $units = Unit::with(['rooms.racks'])->orderBy('name', 'asc')->get();

        return Inertia::render('IncomingGoods/Create', [
            'items' => $items,
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'unit_id' => 'required|exists:units,id',
            'vendor_name' => 'required|string|max:255',
            'contact_person' => 'required|string|max:255',
            'phone_number' => 'required|string|max:50',
            'invoice_number' => 'required|string|max:100',
            'attachment' => 'nullable|file|mimes:doc,docx,xls,xlsx,pdf,jpg,jpeg,png,bmp|max:8192', // Max 8MB
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_name' => 'required|string|max:50',
            'items.*.expiry_date' => 'nullable|date',
            'items.*.room_id' => 'required|exists:rooms,id',
            'items.*.rack_id' => 'required|exists:racks,id',
            'items.*.tier' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            // Handle file upload
            $attachmentPath = null;
            if ($request->hasFile('attachment')) {
                $attachmentPath = $request->file('attachment')->store('attachments', 'public');
            }

            // Create incoming goods header
            $incoming = IncomingGoods::create([
                'unit_id' => $request->unit_id,
                'vendor_name' => $request->vendor_name,
                'contact_person' => $request->contact_person,
                'phone_number' => $request->phone_number,
                'invoice_number' => $request->invoice_number,
                'attachment_path' => $attachmentPath,
                'notes' => $request->notes,
                'created_by' => Auth::id() ?? 1, // Fallback to 1 for seeding/tests
            ]);

            // Create details & update inventory
            foreach ($request->items as $itemData) {
                // Save detail
                IncomingGoodsDetail::create([
                    'incoming_goods_id' => $incoming->id,
                    'item_id' => $itemData['item_id'],
                    'price' => $itemData['price'],
                    'quantity' => $itemData['quantity'],
                    'unit_name' => $itemData['unit_name'],
                    'expiry_date' => $itemData['expiry_date'],
                    'room_id' => $itemData['room_id'],
                    'rack_id' => $itemData['rack_id'],
                    'tier' => $itemData['tier'],
                ]);

                $stock = InventoryStock::where('item_id', '=', $itemData['item_id'], 'and')
                    ->where('room_id', '=', $itemData['room_id'], 'and')
                    ->where('rack_id', '=', $itemData['rack_id'], 'and')
                    ->where('tier', '=', $itemData['tier'], 'and')
                    ->where('price', '=', $itemData['price'], 'and')
                    ->where('expiry_date', '=', $itemData['expiry_date'], 'and')
                    ->first();

                if ($stock) {
                    $stock->quantity += $itemData['quantity'];
                    $stock->save();
                } else {
                    InventoryStock::create([
                        'item_id' => $itemData['item_id'],
                        'room_id' => $itemData['room_id'],
                        'rack_id' => $itemData['rack_id'],
                        'tier' => $itemData['tier'],
                        'price' => $itemData['price'],
                        'expiry_date' => $itemData['expiry_date'],
                        'quantity' => $itemData['quantity'],
                    ]);
                }
            }
        });

        return redirect()->route('items.index')->with('success', 'Incoming goods recorded and allocated successfully.');
    }
}
