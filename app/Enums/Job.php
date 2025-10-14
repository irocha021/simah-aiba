<?php

namespace App\Enums;

enum Job: int
{
    case HW_STATION_READING = 1;
    case HW_STATION_READING_QA = 2;
}