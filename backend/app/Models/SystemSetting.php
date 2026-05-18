<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $table = 'system_settings';

    protected $fillable = ['key', 'value', 'type', 'group', 'description'];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }
}
