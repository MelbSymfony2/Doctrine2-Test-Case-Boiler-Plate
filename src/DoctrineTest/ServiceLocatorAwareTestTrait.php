<?php

namespace DoctrineTest;


use Zend\Mvc\Application;
use Zend\Stdlib\Exception\LogicException;

trait ServiceLocatorAwareTestTrait
{
    /**
     * @var \Zend\Mvc\ApplicationInterface
     */
    protected $application;

    /**
     * @var array
     */
    protected $applicationConfig;

    /**
     * Set the application config
     * @param  array                      $applicationConfig
     * @return $this
     * @throws LogicException
     */
    public function setApplicationConfig($applicationConfig)
    {
        if (null !== $this->application && null !== $this->applicationConfig) {
            throw new LogicException(
                'Application config can not be set, the application is already built'
            );
        }

        // do not cache module config on testing environment
        if (isset($applicationConfig['module_listener_options']['config_cache_enabled'])) {
            $applicationConfig['module_listener_options']['config_cache_enabled'] = false;
        }
        $this->applicationConfig = $applicationConfig;

        return $this;
    }

    /**
     * Get the application object
     * @return \Zend\Mvc\ApplicationInterface
     */
    public function getApplication()
    {
        if ($this->application) {
            return $this->application;
        }
        $appConfig = $this->applicationConfig;
        $this->application = Application::init($appConfig);

        return $this->application;
    }

    /**
     * Get the service manager of the application object
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getApplicationServiceLocator()
    {
        return $this->getApplication()->getServiceManager();
    }

    /**
     * Returns the service manager.
     *
     * @return \Zend\ServiceManager\ServiceLocatorInterface
     */
    public function getServiceLocator()
    {
        return $this->getApplicationServiceLocator();
    }

    /**
     * Get the application config
     * @return array the application config
     */
    public function getApplicationConfig()
    {
        return $this->applicationConfig;
    }

}