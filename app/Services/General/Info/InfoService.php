<?php

namespace App\Services\General\Info;

use App\Models\Info;
use App\Models\User;
use App\Models\Section;
use App\Constants\Constants;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Http\Resources\Section\SectionResource;

class InfoService
{
    public function getAll()
    {
        $local = App::getLocale();
        $isAdmin = Auth::user()?->hasRole(Constants::ADMIN_ROLE);
        $cacheKey = ($isAdmin ? 'admin' : 'app') . 'info.' . ((request()->header('locale') ?? 'all'));
        return Cache::rememberForever($cacheKey, function () use ($local, $isAdmin) {
            $data = Info::get();
            $formattedResponse = [];
            foreach ($data as $d) {
                $value = $d->value;
                if (in_array($d->super_key . '-' . $d->key, Info::$translatableKeys)) {
                    if ($isAdmin) {
                        $value = $d->value;
                    } else {
                        $value = $d->value[$local];
                    }
                }
                if ($d->super_key) {
                    $formattedResponse[$d->super_key][$d->key] = $value;
                } else {
                    $formattedResponse[$d->key] = $value;
                }
            }
            return $formattedResponse;
        });
    }
}
