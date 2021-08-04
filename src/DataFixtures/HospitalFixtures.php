<?php

namespace App\DataFixtures;

use App\Entity\Hospital;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class HospitalFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        // create 20 hospitals!
        for ($i = 0; $i < 20; $i++) {
            $hotel = (new Hospital())
                ->setName('Ospedale'.$i)
                ->setAddress($faker->address)
                ->setCity($faker->city)
                ->setCountryCode('IT');
            $manager->persist($hotel);
        }

        $manager->flush();
    }
}
