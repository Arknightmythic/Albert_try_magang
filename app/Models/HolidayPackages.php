<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HolidayPackages extends Model
{
    use HasFactory, SoftDeletes;


    protected $fillable=[
       "category_id",
       "slug",
       "destinations_name",
       "destinations_location",
       "destinations_itenary",
       "about",
       "contact",
       "images",
       "price_per_trip",
       "hotel",
       "travel",
       "plane"

    ];

    protected $casts = [
        'images'=>'array'
    ];

    public function getRouteKeyName() {
        return 'slug';
    }

    public function setTitleAttribute($value) {
        $this->attributes['destinations_name'] =$value;
        $this->attributes['slug'] = Str::slug($value);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
