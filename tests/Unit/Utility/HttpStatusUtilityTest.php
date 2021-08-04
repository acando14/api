<?php

namespace App\Tests\Unit\Utility;

use App\Utility\HttpStatusUtility;
use PHPUnit\Framework\TestCase;

class HttpStatusUtilityTest extends TestCase
{
    public function test_successStatus()
    {
        $util = new HttpStatusUtility();
        $this->assertTrue(
            $util->isSuccessfulHttpStatus(200)
        );
    }


    public function test_failureStatus()
    {
        $util = new HttpStatusUtility();
        $this->assertFalse(
            $util->isSuccessfulHttpStatus(400)
        );
    }
}
