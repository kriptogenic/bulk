<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use Sweet1s\MoonshineRBAC\Traits\HasMoonShineRolePermissions;

class Role extends SpatieRole
{
    use HasMoonShineRolePermissions;

    protected $with = ['permissions'];
}
