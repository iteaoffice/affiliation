<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * PHP Version 5
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   2004-2016 ITEA Office
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/affiliation for the canonical source repository
 */

namespace Affiliation\Navigation\Invokable;

use Admin\Navigation\Invokable\AbstractNavigationInvokable;
use Affiliation\Entity\Affiliation;
use Project\Entity\Project;
use Zend\Navigation\Page\Mvc;

/**
 * Class AffiliationLabel
 *
 * @package Affiliation\Navigation\Invokable
 */
class AffiliationLabel extends AbstractNavigationInvokable
{
    /**
     * @param Mvc $page
     *
     * @return void
     */
    public function __invoke(Mvc $page)
    {
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
        } else {
            $label = $this->translate('txt-nav-view');
        }
        $page->set('label', $label);
    }
}
