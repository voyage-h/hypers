<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatUser extends Model
{
    use HasFactory;

    public $timestamps = false;
    public function note()
    {
        return $this->hasOne(Note::class, 'target_uid', 'uid');
    }
}
