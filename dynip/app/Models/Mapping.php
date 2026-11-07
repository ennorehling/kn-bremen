<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 *
 * @property int $id
 * @property int $host_id
 * @property string $address
 * @property int $family
 */
class Mapping extends Model
{
    /** @use HasFactory<\Database\Factories\MappingFactory> */
    use HasFactory;

    protected $fillable = [
        'host_id',
        'address',
        'family',
    ];

    public function host(): BelongsTo
    {
        return $this->belongsTo(Host::class);
    }
}
