<?php

namespace App\Models;

use App\Models\Category;
use App\Models\Comment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $hidden = ['pivot'];

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, "have_assigned");
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
