<?php

namespace App\Enum;

enum UserAccountStatusEnum: string
{
    case ACTIVE = "active";
    case BLOCKED = "blocked";
}
