<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Activity extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id','user_id','type','subject','body','direction','status',
        'duration_minutes','phone_number','email_from','email_to',
        'email_message_id','outlook_conversation_id','metadata','occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
        'metadata' => 'array',
        'customer_id' => 'integer',
        'user_id' => 'integer',
        'duration_minutes' => 'integer',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function attachments() {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public static function getTypeIcons(): array {
        return [
            'email' => 'envelope',
            'email_incoming' => 'envelope-open',
            'email_outgoing' => 'paper-airplane',
            'call' => 'phone',
            'whatsapp' => 'chat-bubble-left',
            'sms' => 'device-phone-mobile',
            'meeting' => 'user-group',
            'note' => 'document-text',
            'task' => 'check-circle',
        ];
    }

    public static function getTypeColors(): array {
        return [
            'email' => 'blue',
            'email_incoming' => 'blue',
            'email_outgoing' => 'indigo',
            'call' => 'green',
            'whatsapp' => 'emerald',
            'sms' => 'yellow',
            'meeting' => 'purple',
            'note' => 'gray',
            'task' => 'orange',
        ];
    }
}
