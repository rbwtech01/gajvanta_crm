<?php

namespace App\Models;

use App\Models\Category;
use App\Models\MachineModelAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class MachineModel extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'status',
    ];


    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function attachments()
    {
        return $this->belongsToMany(CategoryAttachment::class,'category_attachment_machine_model');
    }
}
