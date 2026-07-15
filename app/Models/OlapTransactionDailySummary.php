<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OlapTransactionDailySummary extends Model
{
    protected $table = 'olap_transaction_daily_summaries';

    protected $guarded = [];

    public function flight()
    {
        return $this->belongsTo(Flight::class);
    }

    public function airline()
    {
        return $this->belongsTo(Airline::class);
    }
}
