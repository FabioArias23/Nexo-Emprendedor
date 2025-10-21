<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */


    // Añade constantes para los roles
    public const ROLE_INVESTOR = 'investor';
    public const ROLE_ENTREPRENEUR = 'entrepreneur';


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
        'role' => 'string',
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

    /**
     * Devuelve las iniciales del nombre del usuario.
     *
     * @return string
     */
    public function initials(): string
    {
        $words = explode(' ', trim($this->name));
        $initials = '';

        if (count($words) >= 2) {
            // Para nombres como "John Doe", devuelve "JD"
            $initials = strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
        } elseif (count($words) === 1 && strlen($words[0]) > 0) {
            // Para un solo nombre como "John", devuelve "JO"
            $initials = strtoupper(substr($words[0], 0, 2));
        } else {
            // Caso por defecto si el nombre está vacío
            $initials = '??';
        }

        return $initials;
    }

    /**
     * Un usuario (emprendedor) recibe muchas propuestas de inversión a través de sus proyectos.
     */
    public function proposals(): HasManyThrough
    {
        // "Para este Usuario, encuentra las Inversiones (Investment) a través de sus Proyectos (Project)."
        return $this->hasManyThrough(Investment::class, Project::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * Un usuario tiene muchas notificaciones.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class)->orderBy('created_at', 'desc');
    }
}
