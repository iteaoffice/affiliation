<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

namespace Affiliation\Controller\Admin;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Form\MissingAffiliationParentFilter;
use Affiliation\Service\AffiliationService;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;

/**
 * Class IndexController
 * @package Affiliation\Controller\Admin
 */
final class IndexController extends AffiliationAbstractController
{
    private AffiliationService $affiliationService;

    public function __construct(AffiliationService $affiliationService)
    {
        $this->affiliationService = $affiliationService;
    }

    public function missingAffiliationParentAction(): ViewModel
    {
        $page                     = $this->params()->fromRoute('page', 1);
        $filterPlugin             = $this->getAffiliationFilter();
        $missingAffiliationParent = $this->affiliationService->findMissingAffiliationParent();

        $paginator
            = new Paginator(new PaginatorAdapter(new ORMPaginator($missingAffiliationParent, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $form = new MissingAffiliationParentFilter();
        $form->setData(['filter' => $filterPlugin->getFilter()]);

        return new ViewModel(
            [
                'paginator'     => $paginator,
                'form'          => $form,
                'encodedFilter' => urlencode($filterPlugin->getHash()),
                'order'         => $filterPlugin->getOrder(),
                'direction'     => $filterPlugin->getDirection(),
            ]
        );
    }
}
