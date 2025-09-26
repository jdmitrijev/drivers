<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_members',
    ];

    public function drivers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teams_drivers', 'team_id', 'user_id')
            ->withTimestamps();
    }

    public function addDriver(User $user): void
    {
        if ($this->drivers()->count() >= $this->max_members) {
            abort(422, 'Team is at maximum capacity');
        }
        $this->drivers()->syncWithoutDetaching([$user->id]);
    }

    public function removeDriver(User $user): void
    {
        $this->drivers()->detach($user->id);
    }
}


