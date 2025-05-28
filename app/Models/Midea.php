<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Midea extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'user_id',
        'service_id',
        'parent_id',
        'description',
        'path',
        'type',
        'media_type',
        'size',
    ];

    public function getPathAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('id', 'desc');
    }

    public function parent()
    {
        return $this->belongsTo(Midea::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Midea::class, 'parent_id')->orderBy('type')->orderBy('description');
    }
}
