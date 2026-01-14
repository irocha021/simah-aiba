<?php

namespace App\Enums;

enum Job: int
{
    case HW_STATION_READING = 1;
    case HW_STATION_READING_QA = 2;
    case HW_FLOW_FORECAST = 3;
}