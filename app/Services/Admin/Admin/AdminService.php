<?php

namespace App\Services\Admin\Admin;


use App\Models\User;
use App\Constants\Constants;
use App\Http\Resources\AdminResource;
use App\Services\Admin\User\UserService;
use Illuminate\Pagination\AbstractPaginator;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class AdminService extends UserService
{
    public function getAll($role, $trash = false, $paginate = false, int $limit = null, $inRandomOrder = false): AnonymousResourceCollection|AbstractPaginator
    {
        $admins = User::whereHas('roles', function ($q) {
            $q->where('name', Constants::ADMIN_ROLE);
        })
        ->with(['roles'])
        //->whereNot('id' , auth('sanctum')->id())
        ;
        
        if ($limit) {
            $admins->limit($limit);
        }
        if ($inRandomOrder) {
            $admins->inRandomOrder();
        }
        if ($trash) {
            $admins->onlyTrashed();
        }
        $admins = ($paginate) ? $admins->paginate(config('app.pagination_limit')) : $admins->get();
        return AdminResource::collection($admins);
    }
}
