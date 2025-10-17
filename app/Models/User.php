<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str; // <-- Asegúrate de que esta línea esté presente

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */

    
    // Añade constantes para los roles
    public const ROLE_INVERSOR = 'inversor';
    public const ROLE_EMPRENDEDOR = 'emprendedor';


    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'role' => 'string', // Laravel 11+ maneja enums automáticamente, pero es bueno ser explícito
    ];

    /**
     * Un usuario tiene un perfil.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Un usuario (emprendedor) tiene muchos proyectos.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    /**
     * Un usuario (inversor) realiza muchas inversiones.
     */
    public function investments(): HasMany
    {
        // Especificamos la clave foránea porque no sigue la convención 'user_id'
        return $this->hasMany(Investment::class, 'investor_id');
    }

    /**
     * Un usuario puede dar 'like' a muchos proyectos.
     */
    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_likes')->withTimestamps();
    }
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }
}