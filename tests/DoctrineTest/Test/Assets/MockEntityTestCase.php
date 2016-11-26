<?php

namespace DoctrineTest\Test\Assets;


use DoctrineTest\ServiceLocatorAwareTestTrait;
use DoctrineTest\TestCase\EntityTestCase;
use Zend\Stdlib\ArrayUtils;

class MockEntityTestCase extends EntityTestCase {
    use ServiceLocatorAwareTestTrait;


    /**
     * MockEntityTestCase constructor.
     */
    public function __construct()
    {
        parent::__construct();

        //Normally this method block would be in a setUp() method, but since we aren't
        //actually running the test we have to put it here

        $configOverrides = [];

        $this->setApplicationConfig(ArrayUtils::merge(
            include __DIR__ . '/config/application.config.php',
            $configOverrides
        ));

    }
}