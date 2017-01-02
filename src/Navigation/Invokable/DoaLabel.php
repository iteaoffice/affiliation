<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Doa
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

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
class DoaLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void
     */
    public function __invoke(Mvc $page)
    {
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
            $label = $this->translate('txt-doa');
        } else {
            $label = $this->translate('txt-nav-view');
        }
        $page->set('label', $label);
    }
}
