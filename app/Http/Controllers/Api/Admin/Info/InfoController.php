<?php

namespace App\Http\Controllers\Api\Admin\Info;

use App\Models\Info;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\UpdateInfoRequest;
use App\Services\Admin\Info\InfoService as AdminInfoService;


class InfoController extends Controller
{
    public function __construct(protected AdminInfoService $infoService)
    {
    }

  // the swagger annotation in the Info File of the app name .
    public function update(UpdateInfoRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $this->infoService->update($request);
            DB::commit();
            return success($data);

        } catch (\Throwable $th) {
            DB::rollBack();
            
            return error($th->getMessage(), [$th->getMessage()], $th->getCode());
        }
    }


}
