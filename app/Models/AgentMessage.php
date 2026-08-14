<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgentMessage extends Model
{
    protected $fillable = ['agent_conversation_id', 'rol', 'contenido'];

    public function conversation()
    {
        return $this->belongsTo(AgentConversation::class, 'agent_conversation_id');
    }
}