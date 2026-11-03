<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'biografia', 'foto_url', 'especialidad', 'linkedin_url'])]
class Ponente extends Model
{
    protected $table = 'ponentes';

    public function charlas(): HasMany
    {
        return $this->hasMany(Charla::class, 'ponente_id');
    }
}