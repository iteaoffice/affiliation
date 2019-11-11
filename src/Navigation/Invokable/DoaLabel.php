<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Navigation\Invokable;

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use Project\Entity\Project;
use Zend\Navigation\Page\Mvc;

/**
 * Class DoaLabel
 *
 * @package Doa\Navigation\Invokable
 */
final class DoaLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translator->translate('txt-nav-view');

        if ($this->getEntities()->containsKey(Doa::class)) {
            /** @var Doa $doa */
            $doa = $this->getEntities()->get(Doa::class);
            $this->getEntities()->set(Affiliation::class, $doa->getAffiliation());
            $this->getEntities()->set(Project::class, $doa->getAffiliation()->getProject());
            $page->setParams(
                array_merge(
                    $page->getParams(),
                    [
                        'id' => $doa->getId(),
                    ]
                )
            );
            $label = $this->translator->translate('txt-doa');
        }
        $page->set('label', $label);
    }
}
