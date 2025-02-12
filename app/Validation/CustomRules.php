<?php

namespace App\Validation;

class CustomRules
{
    public function valid_time(string $str): bool
    {
        return preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d$/', $str);
    }
}
