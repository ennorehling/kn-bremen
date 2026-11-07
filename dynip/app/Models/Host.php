<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
    /** @use HasFactory<\Database\Factories\HostFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'password',
    ];

    public function mappings(): HasMany
    {
        return $this->hasMany(Mapping::class);
    }
}
