<?php

namespace App\Http\Controllers;

use App\Models\Rack;
use App\Models\Room;
use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;

class RackController extends Controller
{
    public function index(): Response
    {
        $racks = Rack::with(['room.unit'])->latest()->get();
        $units = Unit::with('rooms')->orderBy('name', 'asc')->get();
        
        return Inertia::render('Racks/Index', [
            'racks' => $racks,
            'units' => $units,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'max_tiers' => 'required|integer|min:1|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        Rack::create($request->only(['room_id', 'code', 'name', 'max_tiers', 'description']));

        return redirect()->back()->with('success', 'Rack created successfully.');
    }

    public function update(Request $request, Rack $rack): RedirectResponse
    {
        $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'max_tiers' => 'required|integer|min:1|max:20',
            'description' => 'nullable|string|max:500',
        ]);

        $rack->update($request->only(['room_id', 'code', 'name', 'max_tiers', 'description']));

        return redirect()->back()->with('success', 'Rack updated successfully.');
    }

    public function destroy(Rack $rack): RedirectResponse
    {
        Rack::destroy($rack->id);
        return redirect()->back()->with('success', 'Rack deleted successfully.');
    }

    // API endpoints
    public function getRooms(Unit $unit): JsonResponse
    {
        return response()->json($unit->rooms()->orderBy('name', 'asc')->get());
    }

    public function getRacks(Room $room): JsonResponse
    {
        return response()->json($room->racks()->orderBy('name', 'asc')->get());
    }
}
