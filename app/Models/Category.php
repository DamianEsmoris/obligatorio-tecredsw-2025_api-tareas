<?php

namespace App\Models;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'categories';
    public $timestamps = false;
    protected $hidden = ['pivot'];

    public function assignedTo(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, "have_assigned");
    }
}
