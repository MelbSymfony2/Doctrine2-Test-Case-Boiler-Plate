This is a basic Doctrine2 Test Library to be used with PHPUnit.

#Contributors:
- [Cameron Manderson](https://github.com/cammanderson)
- [Dustin Thomson](https://github.com/51systems)

# Features
- SQLite in-memory database used for tests
- Fixture Support
- Subscriber support

# Installation

Install via composer.

# Writing tests

    namespace Application\Test\Entity;

    class MyModelTest extends EntityTestCase
    {

        public function setUp()
        {
            // Load the database schemas
            $this->loadSchemas(array('Entity\MyEntity')); // Load as many needed for the tests

            // Optionally, you can load fixtures
            $this->loadFixture('DataFixtures\ORM\MyFixture');

            // You can also load subscribers, like registering sluggable, timestampable etc behaviour
            //$myListener = new Listener\MyListener();
            //$this->addLifecycleEventSubscriber($myListener);
        }

        public function testCreate()
        {
            // Get the entity manager for managing persistence etc.
            $em = $this->getEntityManager();

            // Test a create
            $myEntity = new Entity\MyEntity;
            $myEntity->setTitle('Hello World');
            $em->persist();

            // Test we issued SQL to the database
            $this->assertEquals(1, $this->getQueryCount(), 'Should have executed one query to the database');

            // Test the generation of an ID
            $this->assertNotEmpty($myEntity->getId(), 'Should have got an ID for my entity');

        }
    }