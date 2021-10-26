<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Provider;

use Affiliation\Entity;
use Contact\Entity\Contact;
use Contact\Provider\ContactProvider;
use Project\Entity\Version\Version;
use Project\Service\VersionService;

/**
 * Class AffiliationProvider
 * @package Affiliation\Provider
 */
class AffiliationProvider
{
    private VersionService $versionService;
    private ContactProvider $contactProvider;

    public function __construct(VersionService $versionService, ContactProvider $contactProvider)
    {
        $this->versionService  = $versionService;
        $this->contactProvider = $contactProvider;
    }

    public function generateArray(Entity\Affiliation $affiliation, Version $version): array
    {
        $affiliationData = [];

        $affiliationData['id']           = $affiliation->getId();
        $affiliationData['partner']      = $affiliation->parseBranchedName();
        $affiliationData['country']      = $affiliation->getOrganisation()->getCountry()->getCd();
        $affiliationData['partner_type'] = $affiliation->getOrganisation()->getType()->getStandardType();
        $affiliationData['active']       = $affiliation->isActive();
        $affiliationData['coordinator']  = $affiliation->getContact() === $affiliation->getProject()->getContact();
        $affiliationData['self_funded']  = $affiliation->isSelfFunded();

        $costsPerYear  = $this->versionService->findTotalCostVersionByAffiliationAndVersionPerYear($affiliation, $version); //@todo contracts?
        $effortPerYear = $this->versionService->findTotalEffortVersionByAffiliationAndVersionPerYear($affiliation, $version);

        $costsAndEffort = [];
        foreach ($costsPerYear as $year => $costs) {
            if (! array_key_exists($year, $costsAndEffort)) {
                $costsAndEffort[$year] = [
                    'costs'  => 0,
                    'effort' => 0
                ];
            }

            $costsAndEffort[$year]['costs'] += round($costs, 2);
        }

        foreach ($effortPerYear as $year => $effort) {
            if (! array_key_exists($year, $costsAndEffort)) {
                $costsAndEffort[$year] = [
                    'costs'  => 0,
                    'effort' => 0
                ];
            }

            $costsAndEffort[$year]['effort'] += round($effort, 2);
        }

        $affiliationData['costs_and_effort'] = $costsAndEffort;

        //Find the technical contact

        /** @var Contact $technicalContact */
        $technicalContact = $affiliation->getContact();
        foreach ($affiliation->getVersion() as $affiliationVersion) {
            if ($affiliationVersion->getVersion() === $version && null !== $affiliationVersion->getContact()) {
                $technicalContact = $affiliationVersion->getContact();
            }
        }

        $affiliationData['technical_contact'] = $this->contactProvider->generateArray($technicalContact);

        return $affiliationData;
    }
}
