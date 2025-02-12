<?php

namespace unit\app\Validation;

use App\Validation\CustomRules;
use CodeIgniter\Test\CIUnitTestCase;

final class CustomRulesTest extends CIUnitTestCase
{
    public function testValidTimeReturnsTrueForValidTime()
    {
        $customRules = new CustomRules();
        $this->assertTrue($customRules->valid_time('23:59'));
    }

    public function testValidTimeReturnsFalseForInvalidHour()
    {
        $customRules = new CustomRules();
        $this->assertFalse($customRules->valid_time('24:00'));
    }

    public function testValidTimeReturnsFalseForInvalidMinute()
    {
        $customRules = new CustomRules();
        $this->assertFalse($customRules->valid_time('23:60'));
    }

    public function testValidTimeReturnsFalseForNonTimeString()
    {
        $customRules = new CustomRules();
        $this->assertFalse($customRules->valid_time('invalid'));
    }

    public function testValidTimeReturnsFalseForEmptyString()
    {
        $customRules = new CustomRules();
        $this->assertFalse($customRules->valid_time(''));
    }
}