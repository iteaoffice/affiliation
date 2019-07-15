<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Service;

use Affiliation\Entity\Doa;
use Doctrine\Common\Collections\ArrayCollection;
use Organisation\Entity\Organisation;

/**
 * Class DoaService
 *
 * @package Affiliation\Service
 */
class DoaService extends AbstractService
{
    public function findDoaById(int $id): ?Doa
    {
        return $this->entityManager->getRepository(Doa::class)->find($id);
    }

    public function findNotApprovedUploadedDoa(): ArrayCollection
    {
        return new ArrayCollection($this->entityManager->getRepository(Doa::class)->findNotApprovedUploadedDoa());
    }

    public function findNotApprovedDigitalDoa(): ArrayCollection
    {
        return new ArrayCollection($this->entityManager->getRepository(Doa::class)->findNotApprovedDigitalDoa());
    }

    public function findDoaByOrganisation(Organisation $organisation): array
    {
        return $this->entityManager->getRepository(Doa::class)->findDoaByOrganisation($organisation);
    }
}
