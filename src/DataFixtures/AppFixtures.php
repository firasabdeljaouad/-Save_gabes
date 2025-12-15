<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use  App\Factory\UserFactory;
use App\Factory\ProjectFactory;
use App\Factory\ResetPasswordRequestFactory;
use App\Factory\DonationFactory;
use App\Factory\DonaterFactory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $admin = UserFactory::createOne();
        DonaterFactory::createMany(10);
        DonationFactory::createMany(10);
        ResetPasswordRequestFactory::createMany(10, [
            'user' => $admin]);
        ProjectFactory::createMany(10);


        $manager->flush();
    }
}
