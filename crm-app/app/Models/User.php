<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $fillable = [
        'name','email','password','phone','avatar','position','is_active',
        'microsoft_token','microsoft_refresh_token','microsoft_token_expires_at','microsoft_user_id',
    ];

    protected $hidden = ['password','remember_token','microsoft_token','microsoft_refresh_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'microsoft_token_expires_at' => 'datetime',
        'is_active' => 'boolean',
        'password' => 'hashed',
    ];

    public function customers() {
        return $this->hasMany(Customer::class, 'assigned_to');
    }
    public function activities() {
        return $this->hasMany(Activity::class);
    }
    public function tasks() {
        return $this->hasMany(Task::class);
    }
    public function isMicrosoftConnected(): bool {
        return !empty($this->microsoft_token) && $this->microsoft_token_expires_at?->isFuture();
    }
    public function getAvatarUrlAttribute(): string {
        if ($this->avatar) return asset('storage/'.$this->avatar);
        return 'https://ui-avatars.com/api/?name='.urlencode($this->name).'&background=6366f1&color=fff&size=128';
    }
}
