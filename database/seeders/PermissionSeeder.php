<?php

namespace Database\Seeders;

use App\Models\User;
use App\Constants\Constants;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
//       Permission::truncate() ;

       $models = [
            'user' ,
            'contact_messages' ,
            'course_student' ,
            'info' ,
            'notification' ,
            'offer' ,
            'purchase_code' ,
            'purchase_code_section' ,
            'section' ,
       ];

       $permissionsTypes = [
        'view' ,
        'create' ,
        'update' ,
        'delete' ,
        'force_delete' ,
       ];

       $permissions = [] ;

       foreach($models as $model)
       {
            foreach($permissionsTypes as $type)
            {
                $permissions[] = [
                    'name' => $type . '_' . $model ,
                    'guard_name' => 'api' ,
                ];
            }
       }

       Permission::insert($permissions) ;

       //will be extra data but it dose not matter if we didnt use them
    }
}
