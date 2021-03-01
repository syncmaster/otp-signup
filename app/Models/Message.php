<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';

    public function user() {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
