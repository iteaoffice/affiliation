<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Navigation\Invokable;

use Affiliation\Entity\Affiliation;
use General\Navigation\Invokable\AbstractNavigationInvokable;
use Laminas\Navigation\Page\Mvc;
use Project\Entity\Project;

/**
 * Class AffiliationLabel
 *
 * @package Affiliation\Navigation\Invokable
 */
final class AffiliationLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translator->translate('txt-nav-view');

        if ($this->getEntities()->containsKey(Affiliation::class)) {
            /** @var Affiliation $affiliation */
            $affiliation = $this->getEntities()->get(Affiliation::class);
            $this->getEntities()->set(Project::class, $affiliation->getProject());
            $page->setParams(
                array_merge(
                    $page->getParams(),
                    [
                        'id' => $affiliation->getId(),
                    ]
                )
            );
            $label = (string)$affiliation;
        }

        if (null === $page->getLabel()) {
            $page->set('label', $label);
        }
    }
}
