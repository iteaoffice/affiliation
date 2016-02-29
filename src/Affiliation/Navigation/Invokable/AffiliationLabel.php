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
use Zend\Navigation\Page\AbstractPage;

/**
 * Class AffiliationLabel
 *
 * @package Affiliation\Navigation\Invokable
 */
class AffiliationLabel extends AbstractNavigationInvokable
{
    /**
     * @param AbstractPage $page
     *
     * @return void
     */
    public function __invoke(AbstractPage $page)
    {
        $label = $this->getEntities()->containsKey(Affiliation::class) ? sprintf(
            "%s in %s",
            $this->getEntities()->get(Affiliation::class)->getOrganisation(),
            $this->getEntities()->get(Affiliation::class)->getProject()
        ) : $this->translate('txt-nav-view');
        $page->set('label', $label);
    }
}
