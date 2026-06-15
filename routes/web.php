<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RackController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\IncomingGoodsController;
use App\Http\Controllers\OutgoingGoodsController;
use App\Http\Controllers\StockOpnameController;
use App\Models\Item;
use App\Models\InventoryStock;
use App\Models\IncomingGoods;
use App\Models\OutgoingGoods;
use App\Models\StockOpname;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        $totalItems = Item::count('*');
        $totalStockQty = InventoryStock::sum('quantity');
        
        $totalStockValue = InventoryStock::selectRaw('SUM(quantity * price) as val', [])
            ->first()
            ->val ?? 0;

        $recentIncoming = IncomingGoods::with(['unit', 'creator'])->latest()->take(5)->get();
        $recentOutgoing = OutgoingGoods::with(['unit', 'creator'])->latest()->take(5)->get();
        $activeOpnames = StockOpname::where('status', '=', 'draft', 'and')->count('*');

        return Inertia::render('Dashboard', [
            'metrics' => [
                'total_items' => $totalItems,
                'total_stock_qty' => $totalStockQty,
                'total_stock_value' => (float)$totalStockValue,
                'active_opnames' => $activeOpnames,
            ],
            'recent_incoming' => $recentIncoming,
            'recent_outgoing' => $recentOutgoing,
        ]);
    })->name('dashboard');

    // Users (Admin only for modifications ideally, but accessible for auth users)
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);

    // Racks
    Route::resource('racks', RackController::class)->except(['create', 'show', 'edit']);
    Route::get('/api/units/{unit}/rooms', [RackController::class, 'getRooms'])->name('api.units.rooms');
    Route::get('/api/rooms/{room}/racks', [RackController::class, 'getRacks'])->name('api.rooms.racks');

    // Items (Inventory status list)
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');

    // Incoming Goods (Barang Masuk)
    Route::get('/incoming-goods/create', [IncomingGoodsController::class, 'create'])->name('incoming-goods.create');
    Route::post('/incoming-goods', [IncomingGoodsController::class, 'store'])->name('incoming-goods.store');

    // Outgoing Goods (Barang Keluar)
    Route::get('/outgoing-goods/create', [OutgoingGoodsController::class, 'create'])->name('outgoing-goods.create');
    Route::post('/outgoing-goods', [OutgoingGoodsController::class, 'store'])->name('outgoing-goods.store');
    Route::get('/api/units/{unit}/available-items', [OutgoingGoodsController::class, 'getAvailableItems'])->name('api.units.available-items');

    // Stock Opname
    Route::get('/stock-opname', [StockOpnameController::class, 'index'])->name('stock-opname.index');
    Route::post('/stock-opname', [StockOpnameController::class, 'store'])->name('stock-opname.store');
    Route::get('/stock-opname/{stockOpname}', [StockOpnameController::class, 'show'])->name('stock-opname.show');
    Route::put('/stock-opname/details/{detail}', [StockOpnameController::class, 'updateDetail'])->name('stock-opname.details.update');
    Route::post('/stock-opname/{stockOpname}/finalize', [StockOpnameController::class, 'finalize'])->name('stock-opname.finalize');

    // Profile Settings
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
