<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Loi
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity;
use Doctrine\Common\Collections\ArrayCollection;
use Organisation\Entity\Organisation;

/**
 * Class LoiService
 *
 * @package Affiliation\Service
 */
class LoiService extends AbstractService
{
    public function findLoiById(int $id): ?Entity\Loi
    {
        return $this->entityManager->getRepository(Entity\Loi::class)->find($id);
    }

    public function findNotApprovedLoi(): ArrayCollection
    {
        $repository = $this->entityManager->getRepository(Entity\Loi::class);

        return new ArrayCollection($repository->findNotApprovedLoi());
    }

    public function findLoiByOrganisation(Organisation $organisation): array
    {
        $repository = $this->entityManager->getRepository(Entity\Loi::class);

        return $repository->findLoiByOrganisation($organisation);
    }
}
