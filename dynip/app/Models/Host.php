<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 *
 * @property int $id
 * @property string $name
 * @property string $password
 */
class Host extends Model
{
    protected $fillable = [
        'name',
        'password',
    ];

    public function mappings(): HasMany
    {
        return $this->hasMany(Mapping::class);
    }
}
