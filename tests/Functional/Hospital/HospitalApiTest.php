<?php

namespace App\Tests\Functional\Hospital;

use App\Tests\Functional\CommonWebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class HospitalApiTest extends CommonWebTestCase
{
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::bootKernel();
        parent::databaseSchemaDrop();
        parent::databaseSchemaCreate();
        parent::loadFixtures();
    }

    public function test_findAllHospital()
    {
        $client = parent::createClient();
        $client->request('GET', '/api/v1/hospitals');
        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(
            1,
            $content[0]['id']
        );
    }

    public function test_postHospital()
    {
        $client = parent::createClient();
        $client->request('POST', '/api/v1/hospitals', [], [], [
            'Accept' => 'application/json',
            'Content-type' => 'application/json'
        ],
            json_encode([
                'name' => 'name test',
                'address' => 'address-test',
                'city' => 'city',
                'country_code' => 'cc'
            ]));

        $content = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals(
            'name test',
            $content['name']
        );
    }
}
