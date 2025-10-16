<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'industry',
        'funding_goal',
        'min_investment',
        'business_model',
        'market_potential',
    ];

    protected $casts = [
        'funding_goal' => 'decimal:2',
        'min_investment' => 'decimal:2',
    ];

    /**
     * Un proyecto pertenece a un usuario (emprendedor).
     */
    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Un proyecto tiene muchas fotos.
     */
    public function photos(): HasMany
    {
        return $this->hasMany(ProjectPhoto::class);
    }

    /**
     * Un proyecto tiene muchas inversiones (intereses de inversores).
     */
    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }

    /**
     * Un proyecto puede tener muchos 'likes' de usuarios.
     */
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_likes')->withTimestamps();
    }
}