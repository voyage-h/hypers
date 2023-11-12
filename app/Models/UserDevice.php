<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserDevice extends Model
{
    use HasFactory;

    protected $table = 'user_device';

    public function user()
    {
        return $this->belongsTo(ChatUser::class, 'uid', 'uid');
    }

    public function chatUser()
    {
        return $this->belongsTo(ChatUser::class, 'uid', 'uid');
    }

    public function others()
    {
        return $this->hasMany(UserDevice::class, 'dev_id', 'dev_id');
    }
}
