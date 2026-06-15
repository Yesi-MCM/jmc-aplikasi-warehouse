<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use App\Models\Room;
use App\Models\Rack;
use App\Models\InventoryStock;
use App\Models\StockOpname;
use App\Models\StockOpnameDetail;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Users
        $admin = User::create([
            'name' => 'Warehouse Admin',
            'username' => 'admin',
            'email' => 'admin@warehouse.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'jabatan' => 'Warehouse Manager',
            'departemen' => 'Logistics',
        ]);

        $operator = User::create([
            'name' => 'Warehouse Operator',
            'username' => 'operator',
            'email' => 'operator@warehouse.com',
            'password' => Hash::make('password'),
            'role' => 'operator',
            'jabatan' => 'Warehouse Staff',
            'departemen' => 'Logistics',
        ]);

        // 2. Create Categories
        $catAC = Category::create(['name' => 'AC']);
        $catElectronics = Category::create(['name' => 'Electronics']);
        $catFurniture = Category::create(['name' => 'Furniture']);
        $catATK = Category::create(['name' => 'ATK']);

        // 3. Create Items
        $itemSharpSplit = Item::create([
            'category_id' => $catAC->id,
            'code' => 'AC250041',
            'name' => 'Sharp AC Split 3/4 Pk',
            'unit_name' => 'Unit',
        ]);

        $itemSamsung = Item::create([
            'category_id' => $catAC->id,
            'code' => 'AC250039',
            'name' => 'Samsung AC R32 1/2 PK',
            'unit_name' => 'Unit',
        ]);

        $itemDaikin = Item::create([
            'category_id' => $catAC->id,
            'code' => 'AC250036',
            'name' => 'AC DAIKIN Multi Spilt 3/4PK + 3/4PK',
            'unit_name' => 'Unit',
        ]);

        $itemSharpA = Item::create([
            'category_id' => $catAC->id,
            'code' => 'AC250033',
            'name' => 'AC SHARP 1 PK AH-A 9 NCY',
            'unit_name' => 'Unit',
        ]);

        // Stock Opname Items
        $itemTvSharp = Item::create([
            'category_id' => $catElectronics->id,
            'code' => 'TV250068',
            'name' => 'Televisi Sharp Smart TV 50 inch',
            'unit_name' => 'Buah',
        ]);

        $itemTvVizio = Item::create([
            'category_id' => $catElectronics->id,
            'code' => 'TV250071',
            'name' => 'Televisi Vizio Smart TV 43 inch',
            'unit_name' => 'Buah',
        ]);

        $itemMejaIkea = Item::create([
            'category_id' => $catFurniture->id,
            'code' => 'MJ250027',
            'name' => 'Meja Belajar Ikea',
            'unit_name' => 'Buah',
        ]);

        $itemMejaTeras = Item::create([
            'category_id' => $catFurniture->id,
            'code' => 'MJ250037',
            'name' => 'Meja Teras Outdoor',
            'unit_name' => 'Buah',
        ]);

        $itemLemGlue = Item::create([
            'category_id' => $catATK->id,
            'code' => 'GL250003',
            'name' => 'Lem Kertas Glue Stick',
            'unit_name' => 'Buah',
        ]);

        $itemLemTembak = Item::create([
            'category_id' => $catATK->id,
            'code' => 'AC250013',
            'name' => 'Lem Tembak stik Joyko',
            'unit_name' => 'Buah',
        ]);

        $itemLemariWood = Item::create([
            'category_id' => $catFurniture->id,
            'code' => 'LM250016',
            'name' => 'Lemari Kayu Informa',
            'unit_name' => 'Buah',
        ]);

        // 4. Create Units (Gudang)
        $unitUtama = Unit::create(['name' => 'Gudang Utama']);
        $unitSamping = Unit::create(['name' => 'Gudang Samping']);

        // 5. Create Rooms
        $roomA = Room::create(['unit_id' => $unitUtama->id, 'name' => 'Ruang A']);
        $roomB = Room::create(['unit_id' => $unitUtama->id, 'name' => 'Ruang B']);
        $roomC = Room::create(['unit_id' => $unitSamping->id, 'name' => 'Ruang C']);

        // 6. Create Racks
        $rackRA01 = Rack::create([
            'room_id' => $roomA->id,
            'code' => 'RA01',
            'name' => 'Rak RA01',
            'max_tiers' => 6,
            'description' => 'Rak utama Ruang A',
        ]);

        $rackRA02 = Rack::create([
            'room_id' => $roomA->id,
            'code' => 'RA02',
            'name' => 'Rak RA02',
            'max_tiers' => 6,
            'description' => 'Rak cadangan Ruang A',
        ]);

        $rackRB01 = Rack::create([
            'room_id' => $roomB->id,
            'code' => 'RB01',
            'name' => 'Rak RB01',
            'max_tiers' => 4,
            'description' => 'Rak Ruang B',
        ]);

        $rackRB02 = Rack::create([
            'room_id' => $roomB->id,
            'code' => 'RB02',
            'name' => 'Rak RB02',
            'max_tiers' => 4,
            'description' => 'Rak tambahan Ruang B',
        ]);

        $rackRC03 = Rack::create([
            'room_id' => $roomC->id,
            'code' => 'RC03',
            'name' => 'Rak RC03',
            'max_tiers' => 5,
            'description' => 'Rak Ruang C Gudang Samping',
        ]);

        // 7. Create Inventory Stocks (Matching PDF data exactly)
        // AC250041: 16 in Ruang C, Rak RC03, Tingkat 1, price 3,600,000
        InventoryStock::create([
            'item_id' => $itemSharpSplit->id,
            'room_id' => $roomC->id,
            'rack_id' => $rackRC03->id,
            'tier' => 1,
            'price' => 3600000.00,
            'expiry_date' => null,
            'quantity' => 16,
        ]);

        // AC250039: 16 in Ruang C, Rak RC03, Tingkat 1, price 3,200,000
        InventoryStock::create([
            'item_id' => $itemSamsung->id,
            'room_id' => $roomC->id,
            'rack_id' => $rackRC03->id,
            'tier' => 1,
            'price' => 3200000.00,
            'expiry_date' => null,
            'quantity' => 16,
        ]);

        // AC250036:
        // - 10 in Ruang B, Rak RB02, Tingkat 1, price 15,800,000
        // - 2 in null location (wait, let's make it Ruang B Rak RB02 Tingkat 2), price 6,000,000, expiry 2026-03-27
        // - 1 in null location (wait, let's make it Ruang B Rak RB02 Tingkat 3), price 800,000, expiry 2026-03-27
        InventoryStock::create([
            'item_id' => $itemDaikin->id,
            'room_id' => $roomB->id,
            'rack_id' => $rackRB02->id,
            'tier' => 1,
            'price' => 15800000.00,
            'expiry_date' => null,
            'quantity' => 10,
        ]);

        InventoryStock::create([
            'item_id' => $itemDaikin->id,
            'room_id' => $roomB->id,
            'rack_id' => $rackRB02->id,
            'tier' => 2,
            'price' => 6000000.00,
            'expiry_date' => '2026-03-27',
            'quantity' => 2,
        ]);

        InventoryStock::create([
            'item_id' => $itemDaikin->id,
            'room_id' => $roomB->id,
            'rack_id' => $rackRB02->id,
            'tier' => 3,
            'price' => 8000000.00, // wait, the PDF says "800.000 - 15.800.000" and nested says "800.000", let's use 800000
            'expiry_date' => '2026-03-27',
            'quantity' => 1,
        ]);

        // AC250033:
        // - 16 in Ruang A, Rak RA01, Tingkat 1, price 2,800,000
        // - 10 in Ruang A, Rak RA01, Tingkat 1 (let's use Tingkat 2 to keep records distinct), price 2,500,000
        InventoryStock::create([
            'item_id' => $itemSharpA->id,
            'room_id' => $roomA->id,
            'rack_id' => $rackRA01->id,
            'tier' => 1,
            'price' => 2800000.00,
            'expiry_date' => null,
            'quantity' => 16,
        ]);

        InventoryStock::create([
            'item_id' => $itemSharpA->id,
            'room_id' => $roomA->id,
            'rack_id' => $rackRA01->id,
            'tier' => 2,
            'price' => 2500000.00,
            'expiry_date' => null,
            'quantity' => 10,
        ]);

        // Stock Opname Items
        // TV250068 - Televisi Sharp Smart TV 50 inch, Ruang A, Rak RA02, 10
        InventoryStock::create([
            'item_id' => $itemTvSharp->id,
            'room_id' => $roomA->id,
            'rack_id' => $rackRA02->id,
            'tier' => 1,
            'price' => 5000000.00,
            'expiry_date' => null,
            'quantity' => 10,
        ]);

        // TV250071 - Televisi Vizio Smart TV 43 inch, Ruang A, Rak RA02, 15
        InventoryStock::create([
            'item_id' => $itemTvVizio->id,
            'room_id' => $roomA->id,
            'rack_id' => $rackRA02->id,
            'tier' => 1,
            'price' => 4000000.00,
            'expiry_date' => null,
            'quantity' => 15,
        ]);

        // MJ250027 - Meja Belajar Ikea, Ruang B, Rak RB01, 10
        InventoryStock::create([
            'item_id' => $itemMejaIkea->id,
            'room_id' => $roomB->id,
            'rack_id' => $rackRB01->id,
            'tier' => 1,
            'price' => 1200000.00,
            'expiry_date' => null,
            'quantity' => 10,
        ]);

        // MJ250037 - Meja Teras Outdoor, Ruang A, Rak RA01, 20
        InventoryStock::create([
            'item_id' => $itemMejaTeras->id,
            'room_id' => $roomA->id,
            'rack_id' => $rackRA01->id,
            'tier' => 3,
            'price' => 850000.00,
            'expiry_date' => null,
            'quantity' => 20,
        ]);

        // GL250003 - Lem Kertas Glue Stick, Ruang A, Rak RA01, 50
        InventoryStock::create([
            'item_id' => $itemLemGlue->id,
            'room_id' => $roomA->id,
            'rack_id' => $rackRA01->id,
            'tier' => 4,
            'price' => 15000.00,
            'expiry_date' => null,
            'quantity' => 50,
        ]);

        // AC250013 - Lem Tembak stik Joyko, Ruang C, Rak RC03, 100
        InventoryStock::create([
            'item_id' => $itemLemTembak->id,
            'room_id' => $roomC->id,
            'rack_id' => $rackRC03->id,
            'tier' => 2,
            'price' => 25000.00,
            'expiry_date' => null,
            'quantity' => 100,
        ]);

        // LM250016 - Lemari Kayu Informa, Ruang C, Rak RC03, 25
        InventoryStock::create([
            'item_id' => $itemLemariWood->id,
            'room_id' => $roomC->id,
            'rack_id' => $rackRC03->id,
            'tier' => 3,
            'price' => 2450000.00,
            'expiry_date' => null,
            'quantity' => 25,
        ]);

        // 8. Create a sample Stock Opname agenda
        $so = StockOpname::create([
            'date' => '2026-06-16',
            'title' => 'Stock Opname Juni 2026',
            'unit_id' => $unitUtama->id,
            'status' => 'draft',
            'checked_by' => 'John Doe',
            'notes' => 'Pengecekan rutin tengah tahun.',
        ]);

        // Detail checklist for Gudang Utama items
        $utamaStocks = InventoryStock::whereIn('room_id', [$roomA->id, $roomB->id], 'and', false)->get();
        foreach ($utamaStocks as $stock) {
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
                'physical_quantity' => 0, // initially unchecked
                'notes' => null,
            ]);
        }
    }
}
