<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id','user_id','assigned_to','title','description',
        'type','priority','status','due_date','reminder_at','reminder_sent','completed_at',
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'reminder_at' => 'datetime',
        'completed_at' => 'datetime',
        'reminder_sent' => 'boolean',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function assignedUser() {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    public function isOverdue(): bool {
        return $this->status === 'pending' && $this->due_date?->isPast();
    }
    public function scopePending($query) {
        return $query->where('status', 'pending');
    }
    public function scopeOverdue($query) {
        return $query->where('status', 'pending')->where('due_date', '<', now());
    }
    public function scopeToday($query) {
        return $query->where('status', 'pending')
            ->whereDate('due_date', today());
    }
}
