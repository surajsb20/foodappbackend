<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransporterDocument extends Model
{
    //
    protected $fillable = [
        'provider_id',
        'document_id',
        'url',
        'unique_id',
        'status',
    ];

    public function provider()
    {
        return $this->belongsTo('App\Transporter');
    }

    /**
     * The services that belong to the user.
     */
    public function document()
    {
        return $this->belongsTo('App\Document');
    }
}
