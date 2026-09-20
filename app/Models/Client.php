<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contact_name',
        'contact_email',
        'country',
    ];

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
