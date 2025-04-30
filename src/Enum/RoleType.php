<?php
// src/Enum/RoleType.php
namespace App\Enum;

enum RoleType: string
{
    case USER = 'USER';
    case ADMIN = 'ADMIN';

    public function toSecurityRole(): string
    {
        return 'ROLE_' . $this->value;
    }
}