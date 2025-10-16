<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Investment extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'investor_id',
        'status',
        'proposed_amount',
        'message',
        'final_amount',
        'agreement_details',
        'closed_at',
    ];

    protected $casts = [
        'proposed_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'closed_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Una inversión está asociada a un proyecto.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Una inversión pertenece a un usuario (inversor).
     */
    public function investor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'investor_id');
    }
}