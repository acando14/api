<?php

namespace App\StrangeService;

use App\Utility\HttpStatusUtility;

class MyStrangeService
{
    /**
     * @var HttpStatusUtility
     */
    private $httpStatusUtility;

    public function __construct(HttpStatusUtility $httpStatusUtility)
    {
        $this->httpStatusUtility = $httpStatusUtility;
    }

    public function getApiStatusCodeStatus(): bool
    {
        $httpResponseCode = rand(1, 500);

        return $this->httpStatusUtility->isSuccessfulHttpStatus($httpResponseCode);
    }
}
