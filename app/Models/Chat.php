<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Chat extends Model
{
    use HasFactory;
    protected $table="chats";
    protected $guarded=["id"];
    public function participants():HasMany{
       return $this->hasMany(ChatParticipant::class,'chat_id');
    }

    public function messages():HasMany{
          return $this->hasMany(ChatMessage::class,'chat_id');     
    }
    public function lastMessage():HasOne{
            return $this->hasOne(ChatMessage::class,'chat_id')->latest('updated_at');

    }

    public function scopeHasParticpants($query,int $user_id){
     return $query->whereHas('participants',function($q) use ($user_id){
      $q->where('user_id',$user_id);
     });
    }
}
