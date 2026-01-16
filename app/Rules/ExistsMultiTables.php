<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\ValidationRule;

class ExistsMultiTables implements ValidationRule
{
    public function __construct(public array $targetTables , public string $targetColumn , public bool $and = false){}
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach($this->targetTables as $table)
        {
            $model = DB::table($table)->where($this->targetColumn , $value) ;

            if($this->and and $model->doesntExist())
            {
                $fail(__("messages.{$table}_id_not_found"));
            }
            if( ! $this->and and $model->exists())
            {
                return ;
            }
        }
        $fail("messages.id_not_found") ;
    }
}
