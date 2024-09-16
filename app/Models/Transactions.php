<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transactions extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable=[
        "user_id",
        "package_id",
        "start_date",
        "end_date",
        "total_days",
        "price_per_trip",
        "fee",
        "total_price", 
        "status"
    ]; 

    public function setListingIdAttribute($value) {
        $listing = HolidayPackages::find($value);
        $totalDays = Carbon::createFromDate($this->attributes['start_date'])->diffInDays($this->attributes['end_date'])+1;
        $totalPrice = $listing->price_per_day * $totalDays;
        $fee = $totalPrice *0.11;
 
        $this->attributes['package_id']=$value;
        $this->attributes['price_per_trip"']=$listing->price_per_day;
        $this->attributes['total_days']=$totalDays;
        $this->attributes['fee']=$fee;
        $this->attributes['total_price']=$totalPrice+$fee;
     }



    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class,'user_id')->where('role', 'customer');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(HolidayPackages::class,"package_id");
    }

}
