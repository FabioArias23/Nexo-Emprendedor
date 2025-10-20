<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * 
 * @property int $id
 * @property int $user_id
 * @property string $title
 * @property string $description
 * @property int $category_id
 * @property float $funding_goal
 * @property float $min_investment
 * @property string $business_model
 * @property string $market_potential
 * @property string $status
 * @property Carbon|null $deadline
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property mixed|null $embedding
 * 
 * --- Relaciones ---
 * @property-read \App\Models\User $entrepreneur
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ProjectPhoto[] $photos
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Investment[] $investments
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\User[] $likes
 * 
 */

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'funding_goal',
        'min_investment',
        'business_model',
        'market_potential',
        'status',
        'deadline',
    ];

    protected $casts = [
        'funding_goal' => 'decimal:2',
        'min_investment' => 'decimal:2',
        'deadline' => 'datetime',
    ];

    /**
     * Un proyecto pertenece a un usuario (emprendedor).
     */
    public function entrepreneur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Un proyecto pertenece a una categoría.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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

    /**
     * MÉTODOS NUEVOS PARA ANÁLISIS MEJORADO
     */

    /**
     * Calcula cuántos inversores mínimos se necesitan para alcanzar la meta
     */
    public function getRequiredInvestorsCount(): int
    {
        if ($this->min_investment <= 0) return 0;
        return (int) ceil($this->funding_goal / $this->min_investment);
    }

    /**
     * Obtiene el ratio de inversión (meta/inversión mínima)
     */
    public function getInvestmentRatio(): float
    {
        if ($this->min_investment <= 0) return 0;
        return $this->funding_goal / $this->min_investment;
    }

    /**
     * Determina si es un proyecto de alto riesgo (alta inversión mínima)
     */
    public function isHighRisk(): bool
    {
        return $this->min_investment > 50000; // Más de 50,000
    }

    /**
     * Determina si es un proyecto accesible (baja inversión mínima)
     */
    public function isAccessible(): bool
    {
        return $this->min_investment <= 10000; // Hasta 10,000
    }

    /**
     * Obtiene la meta de financiación formateada para TTS
     */
    public function getFundingGoalForTTS(): string
    {
        return $this->formatCurrencyForTTS($this->funding_goal);
    }

    /**
     * Obtiene la inversión mínima formateada para TTS
     */
    public function getMinInvestmentForTTS(): string
    {
        return $this->formatCurrencyForTTS($this->min_investment);
    }

    /**
     * Formatea montos de dinero para TTS
     */
    private function formatCurrencyForTTS(float $amount): string
    {
        if ($amount >= 1000000) {
            $millions = $amount / 1000000;
            if ($millions == intval($millions)) {
                return number_format($millions, 0) . ' millones de pesos';
            } else {
                return number_format($millions, 1) . ' millones de pesos';
            }
        } elseif ($amount >= 1000) {
            $thousands = $amount / 1000;
            if ($thousands == intval($thousands)) {
                return number_format($thousands, 0) . ' mil pesos';
            } else {
                return number_format($thousands, 1) . ' mil pesos';
            }
        } else {
            if ($amount == intval($amount)) {
                return number_format($amount, 0) . ' pesos';
            } else {
                $entero = intval($amount);
                $decimal = round(($amount - $entero) * 100);
                return number_format($entero, 0) . ' pesos con ' . $decimal . ' centavos';
            }
        }
    }
}
