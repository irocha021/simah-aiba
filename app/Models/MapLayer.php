<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MapLayer extends Model
{
    protected $table = 'map_layers';

    protected $fillable = [
        'slug',
        'name',
        'type',
        'source_type',
        'is_active',
        'has_legend',
        'display_order',
        'attribution',
        'min_zoom',
        'max_zoom',
        'opacity',
        'tms',
        'path_zip',
        'url_pattern',
        'marker_color',
        'marker_radius',
        'field_name',
        'single_color',
        'borders_only',
        'draw_borders',
        'border_color',
        'border_buffer',
        'palette_text',
        'sql_mapping_json',
        'label_field',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'has_legend' => 'boolean',
        'tms' => 'boolean',
        'single_color' => 'boolean',
        'borders_only' => 'boolean',
        'draw_borders' => 'boolean',
        'opacity' => 'float',
        'sql_mapping_json' => 'array',
    ];

    public function legends(): HasMany
    {
        return $this->hasMany(MapLayerLegend::class)->orderBy('display_order');
    }
}
