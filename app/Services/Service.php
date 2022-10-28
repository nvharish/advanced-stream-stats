<?php

namespace App\Services;

use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\AppClient;
use DateTime;
use Illuminate\Database\Eloquent\Model;
use Stevebauman\Location\Facades\Location;

class Service {

    public static function generateUUID() {
        return Uuid::uuid4()->toString();
    }

    public static function generateHash($key, $args = array()) {
        $rounds = $args['rounds'] ?? 12;
        return Hash::make($key, [
                    'rounds' => $rounds,
        ]);
    }

    public static function verifyHash($plan_text, $hash_value) {
        return Hash::check($plan_text, $hash_value);
    }

    public static function generateRandomString($length = 40) {
        return Str::random($length);
    }

    public static function currentDateTime($format = 'Y-m-d H:i:s', $interval = '') {
        if ($interval == '') {
            $current_datetime = gmdate($format);
        } else {
            $current_datetime = gmdate($format, strtotime($interval));
        }
        return $current_datetime;
    }

    public static function secondsBetween($datetime1, $datetime2 = 'now') {
        $start_time = new DateTime($datetime1);
        $end_time = new DateTime($datetime2);
        return $start_time->getTimestamp() - $end_time->getTimestamp();
    }

    public static function getVisitorLocation($ip) {
        $visitor_info = Location::get(env('APP_ENV') === 'local' ? '' : $ip);
        //print_r($visitor_info);exit;
        if (is_null($visitor_info)) {
            return null;
        }
        return array(
            'country' => $visitor_info->countryName ?? null,
            'country_code' => $visitor_info->countryCode ?? null,
            'zip_code' => $visitor_info->zipCode ?? null,
            'timezone' => $visitor_info->timezone ?? null,
            'city' => $visitor_info->cityName ?? null,
            'region_code' => $visitor_info->regionCode ?? null,
            'region_name' => $visitor_info->regionName ?? null,
        );
    }

    public static function modelFilter(Model $model, $filters = array()) {
        foreach ($filters as $column => $value) {
            $model = $model->where(function ($q) use ($column, $value) {
                $q->where($column, $value)->orWhere($column, 'like', '%' . $value . '%');
            });
        }
        //print_r($model->toSql());exit;
        return $model->get();
    }

}
