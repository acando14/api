<?php

namespace App\Tests\Functional\StrangeService;

use App\StrangeService\MyStrangeService;
use App\Tests\Functional\CommonWebTestCase;
use App\Utility\HttpStatusUtility;
use PHPUnit\Framework\MockObject\MockObject;

class MyStrangeServiceTest extends CommonWebTestCase
{
    public function test_getApiStatusCode()
    {
        /** @var MockObject|HttpStatusUtility $mock */
        $mock = $this->createStub(HttpStatusUtility::class);
        $mock->method('isSuccessfulHttpStatus')
            ->willReturn(true);
        $myStrangeService = new MyStrangeService($mock);
        $this->assertTrue(
            $myStrangeService->getApiStatusCodeStatus()
        );
    }
}
