<?php

namespace DoctrineTest\Test\Assets\Fixture\Simple;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\Doctrine;
use Doctrine\Common\Persistence\ObjectManager;
use DoctrineTest\Test\Assets\Model\Simple\Car as CarEntity;

class Car extends AbstractFixture
{

    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param Doctrine\Common\Persistence\ObjectManager $manager
     */
    function load(ObjectManager $manager)
    {
        $car = new CarEntity();
        $car->setMake("Ford");
        $car->setModel("Model T");
        $car->setYear(1910);

        $manager->persist($car);
        $manager->flush();
    }
}