<?php
/**
 * ITEA Office all rights reserved
 *
 * @category  Affiliation
 *
 * @author    Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Contact\Service\SelectionContactService;
use Doctrine\ORM\EntityManager;

/**
 * Class AffiliationQuestionService
 * @package Affiliation\Service
 */
class AffiliationQuestionService extends AbstractService
{

    public function __construct(
        EntityManager $entityManager
    ) {
        parent::__construct($entityManager);
    }
}
