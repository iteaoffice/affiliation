<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    SoloDB
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 *
 * @version     4.0
 */

declare(strict_types=1);

namespace Affiliation;

use Zend\ModuleManager\Feature;

/**
 *
 */
class Module implements Feature\ConfigProviderInterface
{

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return include __DIR__ . '/../config/module.config.php';
    }
}
