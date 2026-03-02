<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name','last_name','email','phone','mobile','company','position',
        'website','linkedin','address','city','country','postal_code',
        'status','priority','source','avatar','notes','annual_value',
        'assigned_to','created_by','last_contact_at',
    ];

    protected $casts = [
        'last_contact_at' => 'datetime',
        'annual_value' => 'decimal:2',
        'assigned_to' => 'integer',
        'created_by' => 'integer',
    ];

    protected $appends = ['full_name','avatar_url'];

    public function getFullNameAttribute(): string {
        return "{$this->first_name} {$this->last_name}";
    }

    public function getAvatarUrlAttribute(): string {
        if ($this->avatar) return asset('storage/'.$this->avatar);
        return 'https://ui-avatars.com/api/?name='.urlencode($this->full_name).'&background=6366f1&color=fff&size=128';
    }

    public function assignedUser() {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function createdBy() {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function activities() {
        return $this->hasMany(Activity::class)->orderByDesc('occurred_at');
    }
    public function tasks() {
        return $this->hasMany(Task::class)->orderBy('due_date');
    }
    public function tags() {
        return $this->belongsToMany(Tag::class, 'customer_tag');
    }
    public function pipelines() {
        return $this->hasMany(Pipeline::class);
    }
    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }
    public function latestActivity() {
        return $this->hasOne(Activity::class)->latestOfMany('occurred_at');
    }
    public function pendingTasks() {
        return $this->hasMany(Task::class)->where('status', 'pending')->orderBy('due_date');
    }

    public function scopeSearch($query, $term) {
        return $query->where(function($q) use ($term) {
            $q->where('first_name','like',"%{$term}%")
              ->orWhere('last_name','like',"%{$term}%")
              ->orWhere('email','like',"%{$term}%")
              ->orWhere('company','like',"%{$term}%")
              ->orWhere('phone','like',"%{$term}%");
        });
    }

    public static function getStatusColors(): array {
        return [
            'lead' => 'blue','prospect' => 'yellow','active' => 'green',
            'inactive' => 'gray','churned' => 'red',
        ];
    }
}
