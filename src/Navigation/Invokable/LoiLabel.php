<?php

/**
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Navigation\Invokable;

use General\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Loi;
use Project\Entity\Project;
use Laminas\Navigation\Page\Mvc;

/**
 * Class LoiLabel
 *
 * @package Loi\Navigation\Invokable
 */
final class LoiLabel extends AbstractNavigationInvokable
{
    public function __invoke(Mvc $page): void
    {
        $label = $this->translator->translate('txt-nav-view');

        if ($this->getEntities()->containsKey(Loi::class)) {
            /** @var Loi $loi */
            $loi = $this->getEntities()->get(Loi::class);
            $this->getEntities()->set(Affiliation::class, $loi->getAffiliation());
            $this->getEntities()->set(Project::class, $loi->getAffiliation()->getProject());
            $page->setParams(
                array_merge(
                    $page->getParams(),
                    [
                        'id' => $loi->getId(),
                    ]
                )
            );
            $label = $this->translator->translate('txt-loi');
        }
        $page->set('label', $label);
    }
}
