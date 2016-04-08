<?php
/**
 * ITEA Office copyright message placeholder.
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2015 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Financial;
use Affiliation\Form\AdminAffiliation;
use Affiliation\Form\EditAssociate;
use Project\Acl\Assertion\Project as ProjectAssertion;
use Zend\View\Model\ViewModel;
use Search\Form\SearchResult;
use Search\Paginator\Adapter\SolariumPaginator;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use Zend\Paginator\Paginator;

/**
 *
 */
class AffiliationManagerController extends AffiliationAbstractController
{

    /**
     * @return ViewModel
     */
    public function listAction()
    {
        $searchService = $this->getAffiliationSearchService();
        $page = $this->params('page', 1);
        $form = new SearchResult();
        $data = array_merge([
            'order'     => '',
            'direction' => '',
            'query'     => '*',
            'facet'     => [],
        ], $this->getRequest()->getQuery()->toArray());

        if ($this->getRequest()->isGet()) {
            $searchService->setSearch($data['query'], $data['order'], $data['direction']);
            if (isset($data['facet'])) {
                foreach ($data['facet'] as $facetField => $values) {
                    $quotedValues = [];
                    foreach ($values as $value) {
                        $quotedValues[] = sprintf("\"%s\"", $value);
                    }

                    $searchService->addFilterQuery(
                        $facetField,
                        implode(' ' . SolariumQuery::QUERY_OPERATOR_OR . ' ', $quotedValues)
                    );
                }
            }

            $form->addSearchResults(
                $searchService->getQuery()->getFacetSet(),
                $searchService->getResultSet()->getFacetSet()
            );
            $form->setData($data);
        }

        $paginator = new Paginator(new SolariumPaginator($searchService->getSolrClient(), $searchService->getQuery()));
        $paginator->setDefaultItemCountPerPage(($page === 'all') ? 1000 : 25);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator->getDefaultItemCountPerPage()));

        // Remove order and direction from the GET params to prevent duplication
        $filteredData = array_filter($data, function ($key) {
            return !in_array($key, ['order', 'direction']);
        }, ARRAY_FILTER_USE_KEY);

        return new ViewModel([
            'form'                  => $form,
            'order'                 => $data['order'],
            'direction'             => $data['direction'],
            'query'                 => $data['query'],
            'arguments'             => http_build_query($filteredData),
            'paginator'             => $paginator,
            'organisationService'   => $this->getOrganisationService()
        ]);
    }
    
    /**
     * @return ViewModel
     */
    public function viewAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));
        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $this->getProjectService()->addResource($affiliation->getProject(), ProjectAssertion::class);

        return new ViewModel([
            'affiliationService'    => $this->getAffiliationService(),
            'affiliation'           => $affiliation,
            'memberService'         => $this->getMemberService(),
            'contactsInAffiliation' => $this->getContactService()->findContactsInAffiliation($affiliation),
            'projectService'        => $this->getProjectService(),
            'workpackageService'    => $this->getWorkpackageService(),
            'latestVersion'         => $this->getProjectService()->getLatestProjectVersion($affiliation->getProject()),
            'versionType'           => $this->getProjectService()->getNextMode($affiliation->getProject())->versionType,
            'reportService'         => $this->getReportService(),
            'versionService'        => $this->getVersionService(),
            'invoiceService'        => $this->getInvoiceService(),
            'organisationService'   => $this->getOrganisationService(),
        ]);
    }

    /**
     * @return ViewModel
     */
    public function mergeAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));
        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $data = array_merge($this->getRequest()->getPost()->toArray());

        if ($this->getRequest()->isPost()) {
            if (isset($data['merge']) && isset($data['submit'])) {
                //Find the second affiliation
                $affiliation = $this->getAffiliationService()->findAffiliationById($data['merge']);

                $this->mergeAffiliation($affiliation, $affiliation);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-merge-of-affiliation-%s-and-%s-in-project-%s-was-successful"),
                        $affiliation->getOrganisation(),
                        $affiliation->getOrganisation(),
                        $affiliation->getProject()
                    ));

                return $this->redirect()->toRoute('zfcadmin/affiliation/view', [
                    'id' => $affiliation->getId(),
                ]);
            }
        }


        return new ViewModel([
            'affiliationService'  => $this->getAffiliationService(),
            'affiliation'         => $affiliation,
            'merge'               => isset($data['merge']) ? $data['merge'] : null,
            'projectService'      => $this->getProjectService(),
            'organisationService' => $this->getOrganisationService(),
        ]);
    }

    /**
     * Edit a affiliation.
     *
     * @return ViewModel
     */
    public function editAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));
        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $formData = [];
        $formData['affiliation'] = sprintf(
            "%s|%s",
            $affiliation->getOrganisation()->getId(),
            $affiliation->getBranch()
        );
        $formData['contact'] = $affiliation->getContact()->getId();
        $formData['branch'] = $affiliation->getBranch();
        $formData['valueChain'] = $affiliation->getValueChain();
        $formData['marketAccess'] = $affiliation->getMarketAccess();
        $formData['mainContribution'] = $affiliation->getMainContribution();

        if (!is_null($affiliation->getDateEnd())) {
            $formData['dateEnd'] = $affiliation->getDateEnd()->format('Y-m-d');
        }
        if (!is_null($affiliation->getDateSelfFunded())
            || $affiliation->getSelfFunded() == Affiliation::SELF_FUNDED
        ) {
            if (is_null($affiliation->getDateSelfFunded())) {
                $formData['dateSelfFunded'] = date('Y-m-d');
            } else {
                $formData['dateSelfFunded'] = $affiliation->getDateSelfFunded()->format('Y-m-d');
            }
        }

        /**
         * Only fill the formData of the finanicalOrganisation when this is known
         */
        if (!is_null($financial = $affiliation->getFinancial())) {
            $formData['financialOrganisation'] = $financial->getOrganisation()->getId();
            $formData['financialBranch'] = $financial->getBranch();
            $formData['financialContact'] = $financial->getContact()->getId();
            $formData['emailCC'] = $financial->getEmailCC();
        }


        $form = new AdminAffiliation($affiliation, $this->getOrganisationService());
        $form->setData($formData);

        $form->get('contact')->setDisableInArrayValidator(true);
        $form->get('organisation')->setDisableInArrayValidator(true);
        $form->get('financialOrganisation')->setDisableInArrayValidator(true);
        $form->get('financialContact')->setDisableInArrayValidator(true);

        if ($this->getRequest()->isPost() && $form->setData($_POST) && $form->isValid()) {
            $formData = $form->getData();

            /**
             * Update the affiliation based on the form information
             */
            $affiliation->setContact($this->getContactService()->findContactById($formData['contact']));
            $affiliation->setOrganisation($this->getOrganisationService()
                ->findOrganisationById($formData['organisation']));
            $affiliation->setBranch($formData['branch']);
            if (empty($formData['dateSelfFunded'])) {
                $affiliation->setSelfFunded(Affiliation::NOT_SELF_FUNDED);
                $affiliation->setDateSelfFunded(null);
            } else {
                $affiliation->setSelfFunded(Affiliation::SELF_FUNDED);
                $affiliation->setDateSelfFunded(\DateTime::createFromFormat('Y-m-d', $formData['dateSelfFunded']));
            }
            if (empty($formData['dateEnd'])) {
                $affiliation->setDateEnd(null);
            } else {
                $affiliation->setDateEnd(\DateTime::createFromFormat('Y-m-d', $formData['dateEnd']));
            }
            $affiliation->setValueChain($formData['valueChain']);
            $affiliation->setMainContribution($formData['mainContribution']);
            $affiliation->setMarketAccess($formData['marketAccess']);

            $this->getAffiliationService()->updateEntity($affiliation);

            //Only update the financial when an financial organisation is chosen

            if (!empty($formData['financialOrganisation'])) {
                if (is_null($financial = $affiliation->getFinancial())) {
                    $financial = new Financial();
                    $financial->setAffiliation($affiliation);
                }

                $financial->setOrganisation($this->getOrganisationService()
                    ->findOrganisationById($formData['financialOrganisation']));
                $financial->setContact($this->getContactService()->findContactById($formData['financialContact']));
                $financial->setBranch($formData['financialBranch']);
                if (!empty($formData['emailCC'])) {
                    $financial->setEmailCC($formData['emailCC']);
                }


                $this->getAffiliationService()->updateEntity($financial);
            }

            $this->flashMessenger()->setNamespace('success')
                ->addMessage(sprintf(
                    $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                    $affiliation
                ));

            return $this->redirect()->toRoute('zfcadmin/affiliation/view', [
                'id' => $affiliation->getId(),
            ]);
        }

        return new ViewModel([
            'affiliationService' => $this->getAffiliationService(),
            'projectService'     => $this->getProjectService(),
            'form'               => $form,
        ]);
    }


    /**
     * @return array|ViewModel
     */
    public function editAssociateAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));
        
        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $contact = $this->getContactService()->findContactById($this->params('contact'));
        if (is_null($contact)) {
            return $this->notFoundAction();
        }


        //Find the associate

        $data = array_merge([
            'affiliation' => $affiliation->getId(),
        ], $this->getRequest()->getPost()->toArray());

        $form = new EditAssociate($affiliation);
        $form->setData($data);

        if ($this->getRequest()->isPost()) {
            if (!empty($data['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'associates']
                );
            }

            if (!empty($data['delete'])) {
                $affiliation->removeAssociate($contact);
                $this->getAffiliationService()->updateEntity($affiliation);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-associate-%s-has-successfully-been-removed"),
                        $contact->getDisplayName()
                    ));

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'associates']
                );
            }


            if ($form->isValid()) {
                $formData = $form->getData();

                $affiliation->removeAssociate($contact);
                $this->getAffiliationService()->updateEntity($affiliation);

                //Define the new affiliation
                $affiliation = $this->getAffiliationService()->findAffiliationById($formData['affiliation'])
                    ->getAffiliation();
                $affiliation->addAssociate($contact);

                $this->getAffiliationService()->updateEntity($affiliation);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(sprintf(
                        $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                        $affiliation
                    ));

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'associates']
                );
            }
        }

        return new ViewModel([
            'affiliationService'     => $this->getAffiliationService(),
            'projectService'     => $this->getProjectService(),
            'contact'            => $contact,
            'form'               => $form,
        ]);
    }
}
