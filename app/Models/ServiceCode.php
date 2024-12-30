<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceCode extends Model
{
    use HasFactory;

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    public $table = 'service_codes';

    public $fillable = [
        'service_id',
        'code',
    ];

    public function service(){
        return $this->belongsTo(Service::class);
    }
}
