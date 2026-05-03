<?php

namespace App\DTOs\Audit;

enum Severity: string
{
    case Critical = 'critical'; // Security/data loss risk
    case High = 'high';         // Broken functionality
    case Medium = 'medium';     // Incomplete features
    case Low = 'low';           // UI polish/optimization
}
