<?php

namespace DoctrineTest\TestCase;
use Doctrine\ORM\ORMException;

/**
 * Class EntityTestCase
 * @package DoctrineTest\TestCase
 *
 * Doctrine entity test case that creates a temporary Doctrine database.
 */
class EntityTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\ORM\Tools\SchemaTool
     */
    static $sqlLogger;

    /**
     * @var int
     */
    private $queryCount = 0;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Doctrine\Common\EventManager
     */
    private $eventManager;

    /**
     * Default Doctrine Annotated entities to load.
     * @var string[]
     */
    protected $paths = array();

    /**
     * Optional database path to use for operations.
     * If not set, will use an in-memory database
     * @var string
     */
    protected $dbPath;

    /**
     * Tear down process run after tests
     * @return void
     */
    protected function tearDown()
    {
        parent::tearDown();

        if ($this->entityManager != null) {
            $this->entityManager->getConnection()->close();
        }
    }

    /**
     * Resets the query counter index
     * @return void
     */
    public function resetQueryCount()
    {
        if(!empty(self::$sqlLogger)) {
            $this->queryCount = count(self::$sqlLogger->queries);
        }
    }

    /**
     * Returns with the number of queries since last reset of counter
     * @return int
     */
    public function getQueryCount()
    {
        if(!empty(self::$sqlLogger)) {
            return count(self::$sqlLogger->queries) - $this->queryCount;
        }
    }

    /**
     * Loads a fixture
     * @throws \Exception
     * @param $fixtureClass
     * @return void
     */
    public function loadFixture($fixtureClass)
    {
        if(!class_Exists($fixtureClass)) throw new \Exception('Could not locate the fixture class ' . $fixtureClass . '. Ensure it is autoloadable');
        $fixture = new $fixtureClass();
        if(!($fixture instanceof \Doctrine\Common\DataFixtures\FixtureInterface))
            throw new \Exception('Class ' . $fixtureClass . ' does not implement the FixtureInterface.');

        $fixture->load($this->getEntityManager());

        return $this;
    }

    /**
     * Load a database schema into the database.
     * This will drop the current database.
     *
     * @param $entityClasses array of FQCN
     * @return $this
     */
    public function loadSchemas($entityClasses)
    {
        $this->dropDatabase();
        $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($this->getEntityManager());

        $classes = array();
        foreach($entityClasses as $className) {
            $classes[] = $this->getEntityManager()->getClassMetadata($className);
        }
        if(!empty($classes)) {
            $schemaTool->createSchema($classes);
        }

        return $this;
    }

    /**
     * Add doctrine event manager lifecycle listener
     * @param $events Array of Event constants to listen to
     * @param object $listener The listener object.
     * @throws \Exception
     * @return $this
     */
    public function addLifecycleEventListener($events = array(), $listener)
    {
        if(empty($this->entityManager))
            throw new \Exception('Please establish the entity manager connection using getEntityManager prior to adding event listeners');
        $this->eventManager->addEventListener($events, $listener);

        return $this;
    }

    /**
     * Add doctrine event manager lifecycle event subscriber
     * @throws \Exception
     * @param $subscriber
     * @return $this
     */
    public function addLifecycleEventSubscriber($subscriber)
    {
        if(empty($this->entityManager))
            throw new \Exception('Please establish the entity manager connection using getEntityManager prior to adding event subscribers');
        $this->eventManager->addEventSubscriber($subscriber);

        return $this;
    }

    /**
     * Returns with the initialised entity manager
     * @throws \Doctrine\ORM\ORMException
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEntityManager()
    {
        // If we have an entity manager return it
        if(!empty($this->entityManager)) return $this->entityManager;

        // Register a new entity
        $this->eventManager = new \Doctrine\Common\EventManager();

        // TODO: Register Listeners
        $conn = array(
            'driver' => 'pdo_sqlite',
            'path' => $this->dbPath,
            'memory' => true
        );
        $config = \Doctrine\ORM\Tools\Setup::createAnnotationMetadataConfiguration($this->paths, true);
        if (!$config->getMetadataDriverImpl()) {
            throw ORMException::missingMappingDriverImpl();
        }

        // Setup use of SQL Logger
        if(empty(self::$sqlLogger)) {
            self::$sqlLogger = new \Doctrine\DBAL\Logging\DebugStack();
        }

        $config->setResultCacheImpl(new \Doctrine\Common\Cache\MemcacheCache('localhost', '11211'));

        $config->setSQLLogger(self::$sqlLogger);
        $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, $this->eventManager);
        $this->entityManager = \Doctrine\ORM\EntityManager::create($conn, $config, $conn->getEventManager());
        return $this->entityManager;
    }

    /**
     * Drop the entity manager
     * @return void
     */
    public function dropEntityManager()
    {
        if ($this->entityManager != null) {
            $this->entityManager->getConnection()->close();
        }

        $this->entityManager = null;
    }

    /**
     * Drop the database file
     * @return void
     */
    public function dropDatabase()
    {
        $dbPath = trim($this->dbPath);

        if(empty($dbPath))
            return;

        if(trim($dbPath) && preg_match('/\.db$', $dbPath)) {
            @unlink($dbPath);
        }

        $this->dropEntityManager();
    }
}