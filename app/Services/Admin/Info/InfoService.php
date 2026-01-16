<?php

namespace App\Services\Admin\Info;

use App\Models\Info;
use App\Models\SliderImage;
use App\Constants\Constants;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateInfoRequest;
use App\Http\Resources\SliderImageResource;
use App\Services\General\Info\InfoService as GeneralInfoService;

class InfoService
{
    public function __construct(protected GeneralInfoService $generalInfoService)
    {
    }

    public function insertOrUpdateData($data, $update = false): void
    {
        if ($update) {
            foreach ($data as $superKey => $datum) {
                foreach ($datum as $key => $item) {
                    Info::where('super_key', $superKey)
                        ->where('key', $key)
                        ->update(
                            [
                                'value' => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item
                            ]
                        );
                }
            }
        } else {
            $dataToSeed = [];
            foreach ($data as $superKey => $datum) {
                foreach ($datum as $key => $item) {
                    $dataToSeed[] = [
                        'super_key' => $superKey,
                        'key' => $key,
                        'value' => is_array($item) ? json_encode($item, JSON_UNESCAPED_UNICODE) : $item,
                    ];
                }
            }
            Info::insert($dataToSeed);
            Cache::flush();
        }
    }

    public function update(UpdateInfoRequest $request)
    {        
        $validated = $request->validated();
        $infoData = Info::all()->groupBy('super_key');
        $dataToUpdated = [];
        foreach ($validated as $key => $value) {
            $explodedItem = explode('-', $key);
            if (count($explodedItem) === 3) {//this mean we have translation.
                if (!isset($dataToUpdated[$explodedItem[0]][$explodedItem[1]])) {
                    $decodedOldValue = $infoData->get($explodedItem[0])
                        ->where('key', $explodedItem[1])
                        ->first()->value;
                } else {
                    $decodedOldValue = $dataToUpdated[$explodedItem[0]][$explodedItem[1]];
                }

                $decodedOldValue[$explodedItem[2]] = $value;
                $dataToUpdated[$explodedItem[0]][$explodedItem[1]] = $decodedOldValue;
            } elseif (count($explodedItem) === 2) {//this mean we have translation.
                if (in_array($key, Info::$imageKeys)) {
                    $value = $request->file($key)->storePublicly('SiteFiles/images', 'public');
                } elseif (in_array($key, Info::$videoKeys)) {
                    $value = $request->file($key)->storePublicly('SiteFiles/videos', 'public');
                }elseif (in_array($key, Info::$fileKeys)) {
                    $value = $request->file($key)->storePublicly('SiteFiles/files', 'public');
                }
                $dataToUpdated[$explodedItem[0]][$explodedItem[1]] = $value;
            }
        }
        
        $this->insertOrUpdateData($dataToUpdated, true);

        Cache::flush();

        $info = $this->generalInfoService->getAll();

        return $info;
    } 
}
