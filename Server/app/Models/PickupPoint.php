<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\PreventDemoModeChanges;
use App;

class PickupPoint extends Model
{
    use PreventDemoModeChanges;

    protected $with = ['pickup_point_translations'];

    public function holdDays(): int
    {
        return max(1, (int) ($this->pickup_hold_days ?? 5));
    }

    public function workingHoursLabel(): string
    {
        $opening = trim((string) ($this->opening_time ?? ''));
        $closing = trim((string) ($this->closing_time ?? ''));

        if ($opening === '' && $closing === '') {
            return translate('Hours not set');
        }

        if ($opening !== '' && $closing !== '') {
            return "{$opening} - {$closing}";
        }

        return $opening !== '' ? $opening : $closing;
    }

    public function supportsReturn(): bool
    {
        return (int) ($this->supports_return ?? 1) === 1;
    }

    public function supportsCod(): bool
    {
        return (int) ($this->supports_cod ?? 1) === 1;
    }

    public function getTranslation($field = '', $lang = false){
        $lang = $lang == false ? App::getLocale() : $lang;
        $pickup_point_translation = $this->pickup_point_translations->where('lang', $lang)->first();
        return $pickup_point_translation != null ? $pickup_point_translation->$field : $this->$field;
    }

    public function pickup_point_translations(){
      return $this->hasMany(PickupPointTranslation::class);
    }

    public function staff(){
    	return $this->belongsTo(Staff::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PickupPointPayoutRequest::class);
    }

    public function scopeIsActive($query)
    {
        return $query->where('pick_up_status', '1');
    }
}
