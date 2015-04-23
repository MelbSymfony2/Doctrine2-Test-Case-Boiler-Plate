<?php

namespace DoctrineTest\TestCase;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\ORM\ORMException;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

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
     * The doctrine configuration key to use.
     *
     * @var string
     */
    public $configurationKey = 'orm_default';

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
     * Fixture persistence to use when loading fixtures
     * @var \Doctrine\Common\DataFixtures\ReferenceRepository
     */
    private $fixtureReferenceRepo;

    /**
     * Optional database path to use for operations.
     * If not set, will use an in-memory database
     * @var string
     */
    protected $dbPath;


    /**
     * {@inheritdoc}
     *
     * In addition, automatically loads the schemas.
     */
    protected function setUp()
    {
        $this->autoLoadSchemas();
    }

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
     * @return $this
     */
    public function loadFixture($fixtureClass)
    {
        $this->loadFixtures(array($fixtureClass), true);
    }

    /**
     * Loads an array of fixtures.
     *
     * @param string[] $fixtureClasses List of fixture class names
     * @param bool $append
     * @return $this
     * @throws \Exception
     */
    public function loadFixtures(array $fixtureClasses, $append = false)
    {
        $loader = new \Doctrine\Common\DataFixtures\Loader();

        foreach($fixtureClasses as $fixtureClass) {
            if(!class_Exists($fixtureClass))
                throw new \Exception('Could not locate the fixture class ' . $fixtureClass . '. Ensure it is autoloadable');

            $fixture = new $fixtureClass();

            $loader->addFixture($fixture);
        }

        $purger = new \Doctrine\Common\DataFixtures\Purger\ORMPurger();
        $executor = new \Doctrine\Common\DataFixtures\Executor\ORMExecutor($this->getEntityManager(), $purger);
        $executor->setReferenceRepository($this->getFixtureReferenceRepo());

        $executor->execute($loader->getFixtures(), $append);

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
     * Automatically loads all entity classes into the database.
     *
     * @return $this
     */
    public function autoLoadSchemas()
    {
        $classes = $this->getEntityManager()
            ->getConfiguration()
            ->getMetadataDriverImpl()
            ->getAllClassNames();

        return $this->loadSchemas($classes);
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

        $sl = $this->getServiceLocator();

        // Register the event manager
        $this->eventManager = $sl->get('doctrine.eventmanager.' . $this->configurationKey);

        $globalConfig = $sl->get('config');

        $options = new \DoctrineORMModule\Options\EntityManager($globalConfig['doctrine']['entitymanager'][$this->configurationKey]);

        /** @var \Doctrine\Orm\Configuration $config */
        $config = $sl->get($options->getConfiguration());

        //setup some sane debugging defaults.
        $config->setAutoGenerateProxyClasses(true);
        $config->setHydrationCacheImpl(new ArrayCache());
        $config->setMetadataCacheImpl(new ArrayCache());
        $config->setQueryCacheImpl(new ArrayCache());
        $config->setResultCacheImpl(new ArrayCache());

        $conn = array(
            'driver' => 'pdo_sqlite',
            'path' => $this->dbPath,
            'memory' => true
        );

        // Setup use of SQL Logger
        if(empty(self::$sqlLogger)) {
            self::$sqlLogger = new \Doctrine\DBAL\Logging\DebugStack();
        }

        $config->setSQLLogger(self::$sqlLogger);
        $conn = \Doctrine\DBAL\DriverManager::getConnection($conn, $config, $this->eventManager);

        // initializing the resolver
        // @todo should actually attach it to a fetched event manager here, and not
        //       rely on its factory code
        $sl->get($options->getEntityResolver());

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
            $this->entityManager->close();
        }

        unset($this->entityManager);
        unset($this->fixtureReferenceRepo);
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

    /**
     * @return \Doctrine\Common\DataFixtures\ReferenceRepository
     */
    public function getFixtureReferenceRepo()
    {
        if (!isset($this->fixtureReferenceRepo))
            $this->fixtureReferenceRepo = new \Doctrine\Common\DataFixtures\ReferenceRepository($this->getEntityManager());
        return $this->fixtureReferenceRepo;
    }

    /**
     * Returns the module configuration.
     * This should return the same as calling {@link ServiceManager#get('config')}
     *
     * @return ServiceLocatorInterface
     * @throws \Exception
     */
    protected function getServiceLocator()
    {
        return \Application\Test\Bootstrap::getServiceManager();
    }
}