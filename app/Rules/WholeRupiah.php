<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class WholeRupiah implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ((float) $value != floor((float) $value)) {
            $fail('The :attribute must be a whole Rupiah amount without decimals.');
        }
    }
}
