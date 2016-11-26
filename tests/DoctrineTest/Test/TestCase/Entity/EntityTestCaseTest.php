<?php
namespace DoctrineTest\Test\TestCase;


use DoctrineTest\Test\Assets\MockEntityTestCase;

class EntityTestCaseTest extends \PHPUnit_Framework_TestCase
{
    function testGetEntityManager()
    {
        $test = new MockEntityTestCase();
        $em = $test->getEntityManager();

        $this->assertInstanceOf('Doctrine\ORM\EntityManager', $em);

        //Ensure getEntityManager is Idempotent
        $this->assertSame($em, $test->getEntityManager());

        $connection = $em->getConnection();

        //Ensure its a SQLite database
        $this->assertInstanceOf('Doctrine\DBAL\Driver\PDOSqlite\Driver', $connection->getDriver());

        //Attempt to connect
        $connection->connect();
    }

    function testLoadSchema()
    {
        $test = new MockEntityTestCase();
        $test->loadSchemas(array('\DoctrineTest\Test\Assets\Model\Simple\Car'));

        $car = new \DoctrineTest\Test\Assets\Model\Simple\Car();
        $car->setMake("Ford");
        $car->setModel("F150");
        $car->setYear(1992);

        $em = $test->getEntityManager();
        $em->persist($car);
        $em->flush($car);

        $this->assertTrue($em->contains($car));

        $car2 = $em->find('\DoctrineTest\Test\Assets\Model\Simple\Car', $car->getId());

        $this->assertEquals($car, $car2);
    }

    function testLoadFixture()
    {
        new \DoctrineTest\Test\Assets\Fixture\Simple\Car();

        $test = new MockEntityTestCase();
        $test->loadSchemas(array('\DoctrineTest\Test\Assets\Model\Simple\Car'));
        $test->loadFixture('\DoctrineTest\Test\Assets\Fixture\Simple\Car');

        $em = $test->getEntityManager();

        $results = $em->getRepository('\DoctrineTest\Test\Assets\Model\Simple\Car')->findBy(array('model' => 'Model T'));

        $this->assertCount(1, $results);

        $this->assertEquals('Model T', $results[0]->getModel());
    }
}
