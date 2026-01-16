<?php

namespace App\Services\General\Storage\File;
use App\Models\File;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FileService
{
    public function bulkInsert($files , $folder = 'files' ,$storage_disk = 'public' , ?string $modelType = null, ?int $modelId = null)
    {            
        if($files == null)return null ;
        
        try {
            
            $filesData = [];

            foreach ($files as $file) {
            
                $name =  Str::random(40) . '.' . $file->getClientOriginalExtension();
                $name = str_replace('/' , '' , $name);
                
                $publicPath = "/{$folder}/{$name}" ;
                $path = "{$storage_disk}/{$folder}/{$name}" ;
                
                $path = str_replace('//' , '/' , $path);
                $publicPath = str_replace('//' , '/' , $publicPath);
                
                Storage::put($path, file_get_contents($file->getRealPath()));

                // Get the URL of the file
                $url = Storage::url($path);

                $filePath = storage_path("app/{$path}");
                
                $filesData[] = [
                    'model_type' => $modelType ,
                    'model_id' => $modelId ,
                    'name' => $file->getClientOriginalName() ,
                    'path' => $publicPath,
                    'url' => request()->getSchemeAndHttpHost() . $url,
                    'type' => $file->getMimeType() ,
                    'extension' => $file->getClientOriginalExtension() ,
                    'size' => filesize($filePath) ,
                ];
            }

            File::insert($filesData) ;

            return ;
            
        } catch (\Throwable $th) {
            
            foreach($filesData as $data)
            {
                if(Storage::disk($storage_disk)->exists($data['path']))
                {
                    Storage::disk($storage_disk)->delete($data['path']) ;
                }
            }
            throw $th;
        }
    }

    public function bulkInsertTransaction($files , $folder = 'files' ,$storage_disk = 'public' , ?string $modelType = null, ?int $modelId = null)
    {
        return DB::transaction(function()use($files ,$folder ,$storage_disk ,$modelType ,$modelId){
            return $this->bulkInsert($files ,$folder ,$storage_disk ,$modelType ,$modelId) ;
        });
    }

    public function bulkDelete(array $fileIds)
    {
        return File::whereIn('id' , $fileIds)->delete() ;
    }

    public function massForceDelete(array $fileIds)
    {
        $paths = File::whereIn('id' , $fileIds)->pluck('path') ;
        
        Storage::disk('public')->delete($paths) ;

        return File::whereIn('id' , $fileIds)->forceDelete() ;
    }
}
