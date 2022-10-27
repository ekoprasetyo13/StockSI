<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    //
    protected $table = 'activity_log';
    protected $fillable = ['id_activity', 'user_id', 'activity_status', 'product_id'];
}
