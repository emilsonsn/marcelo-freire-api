<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'client_id',
        'title',
        'status',
        'description',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
    
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_services', 'service_id', 'user_id');
    }
    

    public function midias()
    {
        return $this->hasMany(Midea::class);
    }
}
