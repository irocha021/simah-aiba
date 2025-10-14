<?php

namespace App\Enums;

enum FileType: int
{
    case SHAPEFILE_ZIP = 1;
    case GEOJSON = 2;
}