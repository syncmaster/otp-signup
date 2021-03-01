<?php

namespace App\Models;

use App\Models\Message;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $table = 'users';

    protected $fillable = [
        'email',
        'password',
        'phone',
        'code',
        'attempt',
        'status'
    ];

    public function messages() {
        return $this->hasMany(Message::class, 'user_id', 'id');
    }

}
