<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = ["question", "answer", "user_answer"];

    protected $casts = ["created_at" => "datetime", "updated_at" => "datetime"];
}
