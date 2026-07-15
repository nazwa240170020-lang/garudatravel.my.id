<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlapRouteDailySummary extends Model
{
    protected $table = 'olap_route_daily_summaries';

    protected $guarded = [];

    public function departureAirport()
    {
        return $this->belongsTo(Airport::class, 'departure_airport_id');
    }

    public function arrivalAirport()
    {
        return $this->belongsTo(Airport::class, 'arrival_airport_id');
    }
}
