<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;
    protected $table = 'categories';
    public $timestamps = false;
    protected $hidden = ['pivot'];

    public function assignedTo(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, "have_assigned");
    }
}
