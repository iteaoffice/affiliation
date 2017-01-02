<?php
/**
 * ITEA Office all rights reserved
 *
 * PHP Version 7
 *
 * @category    Project
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

namespace Affiliation\Navigation\Invokable;

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Loi;
use Project\Entity\Project;
use Zend\Navigation\Page\Mvc;

/**
 * Class LoiLabel
 *
 * @package Loi\Navigation\Invokable
 */
class LoiLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void
     */
    public function __invoke(Mvc $page)
    {
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
            $label = $this->translate('txt-loi');
        } else {
            $label = $this->translate('txt-nav-view');
        }
        $page->set('label', $label);
    }
}
