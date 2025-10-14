<?php

namespace App\Enums;

enum JobStatus: int
{
    case PENDING = 1;
    case OK = 2;
    case REEXECUTE = 3;
    case ERROR = 4;
}