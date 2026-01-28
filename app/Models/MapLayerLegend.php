<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MapLayerLegend extends Model
{
    protected $table = 'map_layer_legends';

    protected $fillable = [
        'map_layer_id',
        'label',
        'color_hex',
        'display_order',
    ];

    public function layer(): BelongsTo
    {
        return $this->belongsTo(MapLayer::class, 'map_layer_id');
    }
}
