<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['outgoing_goods_id', 'item_id', 'room_id', 'rack_id', 'tier', 'price', 'quantity', 'unit_name'])]
class OutgoingGoodsDetail extends Model
{
    use HasFactory;

    public function outgoingGoods(): BelongsTo
    {
        return $this->belongsTo(OutgoingGoods::class);
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
