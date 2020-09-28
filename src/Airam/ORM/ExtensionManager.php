<?php

namespace Airam\ORM;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\EventManager;

use Gedmo\Mapping\MappedEventSubscriber;

class ExtensionManager
{
    private $eventManager;
    private $adapter;

    public function __construct(EventManager $eventManager, Adapter $adapter)
    {
        $this->eventManager = $eventManager;
        $this->adapter = $adapter;
    }

    /**
     * @return $this
     */
    public function addEventSubscriber(MappedEventSubscriber $listener)
    {
        $listener->setAnnotationReader($this->reader);
        $this->eventManager->addEventSubscriber($listener);
        return $this;
    }

    /**
     * @param string[] $paths
     * @return $this
     */
    public function registerAnnotations(array $paths)
    {
        $this->adapter->getAnnotationDriver()->addPaths($paths);
        return $this;
    }

    public function getEventManaget(): EventManager
    {
        return $this->eventManager;
    }

    public static function register(string $path)
    {
        AnnotationRegistry::registerFile($path);
    }
}
