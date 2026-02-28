<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParticipantEmailTemplate extends Model
{
    protected $table = 'participant_email_templates';

    protected $fillable = [
        'name',
        'subject',
        'body',
        'sort_order',
    ];
}
