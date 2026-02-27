<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailSyncLog extends Model
{
    protected $fillable = [
        'user_id','message_id','conversation_id','subject','from_address',
        'from_name','to_addresses','body_preview','is_read','direction',
        'customer_id','activity_id','received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'is_read' => 'boolean',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
    public function customer() {
        return $this->belongsTo(Customer::class);
    }
    public function activity() {
        return $this->belongsTo(Activity::class);
    }
}
