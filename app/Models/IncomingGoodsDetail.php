<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['incoming_goods_id', 'item_id', 'price', 'quantity', 'unit_name', 'expiry_date', 'room_id', 'rack_id', 'tier'])]
class IncomingGoodsDetail extends Model
{
    use HasFactory;

    public function incomingGoods(): BelongsTo
    {
        return $this->belongsTo(IncomingGoods::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function rack(): BelongsTo
    {
        return $this->belongsTo(Rack::class);
    }
}
