<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'path',
        'caption',
        'is_cover',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
    ];

    /**
     * Una foto pertenece a un proyecto.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}