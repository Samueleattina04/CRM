<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name','customer_id','user_id','stage','value','currency',
        'probability','expected_close_date','notes',
    ];

    protected $casts = [
        'expected_close_date' => 'datetime',
        'value' => 'decimal:2',
    ];

    public function customer() {
        return $this->belongsTo(Customer::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
}
