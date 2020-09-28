<?php

namespace Airam\ORM;

use Doctrine\Common\Cache\{
    Cache,
    FlushableCache,
    ClearableCache,
    MultiOperationCache
};

/**
 * Facade of Cache implementation for production
 */
interface CacheProduction extends Cache, FlushableCache, ClearableCache, MultiOperationCache
{
}
