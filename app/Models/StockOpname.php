<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\Fillable;

#[Fillable(['date', 'title', 'unit_id', 'status', 'checked_by', 'notes', 'attachment_path'])]
class StockOpname extends Model
{
    use HasFactory;

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function details(): HasMany
    {
        return $this->hasMany(StockOpnameDetail::class);
    }
}
