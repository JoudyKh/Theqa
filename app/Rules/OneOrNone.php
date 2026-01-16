<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;

class OneOrNone implements DataAwareRule , ValidationRule 
{
    /**
     * All of the data under validation.
     *
     * @var array<string, mixed>
     */
    protected $data = [];
    
    public function __construct(protected string $otherColumn ){}
    /**
     * Set the data under validation.
     *
     * @param  array<string, mixed>  $data
     */
    public function setData(array $data): static
    {
        $this->data = $data;
 
        return $this;
    }
    
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!array_key_exists($this->otherColumn , $this->data)){
            return ;
        }
        if($this->data[$this->otherColumn] != null and $this->data[$attribute] != null) {
            $fail($attribute . ' and ' . $this->otherColumn . ' can\'t be present together') ;
        }
    }
}
