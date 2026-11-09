<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['provincia', 'municipio', 'calle', 'codigo_postal', 'numero', 'puerta', 'nombre'])]
class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicaciones';

    public function charlas(): HasMany
    {
        return $this->hasMany(Charla::class, 'ubicacion_id');
    }
}
