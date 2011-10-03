This is a basic Doctrine2 Test Case project to provide some boilerplate code and get you up and running with PHPUnit and developing Doctrine2 models.

This package was used for teach people how to develop models using Doctrine2, and test them as you go using PHPUnit. You can see the result [here](https://github.com/MelbSymfony2/Code-Jam-Session-2).

Contributors:
- [Cameron Manderson](https://github.com/cammanderson)

# Installation

Download the boilerplate code using the download link on the github repository page.

Extract to your project

Run `php -f vendors.php` to download the basic Doctrine ORM dependencies. It also downloads Symfony and Gedmo's DoctrineExtensions.

Run `phpunit` in the root directory to run a basic environment check.

By default, your model will be tested using a SQLLite RDBMS, requiring zero configuration. You can modify your EntityTestCase to change these details.

# Writing your model

Create a folder called `src` and create your packages and Entities as appropriate. See the Doctrine2 Documentation for more information about writing Entities.

# Writing tests

Create your tests under the folder `tests\CodeJamTestSuite`. You can rename this package, but you must update your `tests/bootstrap.php` autoload and namespaces accordingly.

    namespace CodeJamTestSuite\Entity;

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