<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */

namespace Affiliation\Controller;

use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Financial;
use Affiliation\Form\AdminAffiliation;
use Affiliation\Form\EditAssociate;
use Affiliation\Form\MissingAffiliationParentFilter;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as PaginatorAdapter;
use Organisation\Entity\Name;
use Organisation\Entity\Parent\Organisation;
use Project\Acl\Assertion\Project as ProjectAssertion;
use Search\Form\SearchResult;
use Search\Paginator\Adapter\SolariumPaginator;
use Solarium\QueryType\Select\Query\Query as SolariumQuery;
use Zend\Http\Request;
use Zend\Http\Response;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Paginator\Paginator;
use Zend\View\Model\ViewModel;
use Search\Service\AbstractSearchService;

/**
 * Class AffiliationManagerController
 *
 * @package Affiliation\Controller
 * @method Response csvExport(AbstractSearchService $searchService, array $fields, bool $header = true)
 *
 */
class AffiliationManagerController extends AffiliationAbstractController
{
    /**
     * @return ViewModel|Response
     */
    public function listAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $requestQuery = $request->getQuery()->toArray();
        $searchService = $this->getAffiliationSearchService();
        $data = array_merge(
            [
            'order'     => '',
            'direction' => '',
            'query'     => '',
            'facet'     => [],
            'fields'    => []
            ],
            $requestQuery
        );
        $searchFieldValues = [
            'description'          => $this->translate('txt-affiliation-description'),
            'main_contribution'    => $this->translate('txt-main-contribution'),
            'market_access'        => $this->translate('txt-market-access'),
            'value_chain'          => $this->translate('txt-value-chain'),
            'strategic_importance' => $this->translate('txt-strategic-importance'),
            'project'              => $this->translate('txt-project'),
            'organisation'         => $this->translate('txt-organisation'),
        ];
        // Set all fields enabled by default
        if (empty($requestQuery)) {
            $data['fields'] = array_keys($searchFieldValues);
        }

        if ($request->isGet()) {
            $searchService->setSearch($data['query'], $data['fields'], $data['order'], $data['direction']);
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
        }

        switch ($this->params('format', 'html')) {
            // Csv export
            case 'csv':
                return $this->csvExport(
                    $searchService,
                    [
                        'organisation_country',
                        'organisation_type',
                        'organisation',
                        'project_number',
                        'project',
                        'project_program',
                        'project_call',
                        'contact',
                    ]
                );

            // Default paginated html view
            default:
                $form = new SearchResult($searchFieldValues);

                // Set facet data in the form
                if ($request->isGet()) {
                    $form->setFacetLabels([
                        'is_active'                  => $this->translate('txt-active'),
                        'organisation_country_group' => $this->translate('txt-country'),
                    ]);
                    $form->addSearchResults(
                        $searchService->getQuery()->getFacetSet(),
                        $searchService->getResultSet()->getFacetSet()
                    );
                    $form->setData($data);
                }

                $viewParams = [
                    'fullArguments' => http_build_query($data),
                    'form'          => $form,
                    'order'         => $data['order'],
                    'direction'     => $data['direction'],
                    'query'         => $data['query'],
                ];

                // Remove order and direction from the GET params to prevent duplication
                $filteredData = array_filter(
                    $data,
                    function ($key) {
                        return !in_array($key, ['order', 'direction'], true);
                    },
                    ARRAY_FILTER_USE_KEY
                );
                $viewParams['arguments'] = http_build_query($filteredData);

                $page = $this->params('page', 1);
                $paginator = new Paginator(
                    new SolariumPaginator($searchService->getSolrClient(), $searchService->getQuery())
                );
                $paginator::setDefaultItemCountPerPage(($page === 'all') ? 1000 : 20);
                $paginator->setCurrentPageNumber($page);
                $paginator->setPageRange(
                    ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage())
                );
                $viewParams['paginator'] = $paginator;
                $viewParams['highlighting'] = $paginator->getCurrentItems()->getHighlighting();
                $viewParams['highlightingFields'] = [
                    'description'          => $this->translate('txt-description'),
                    'main_contribution'    => $this->translate('txt-main-contribution'),
                    'market_access'        => $this->translate('txt-market-access'),
                    'value_chain'          => $this->translate('txt-value-chain'),
                    'strategic_importance' => $this->translate('txt-strategic-importance')
                ];

                return new ViewModel($viewParams);
        }
    }

    /**
     * @return array|ViewModel
     */
    public function viewAction()
    {
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));
        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $this->getProjectService()->addResource($affiliation->getProject(), ProjectAssertion::class);

        return new ViewModel(
            [
                'affiliationService'    => $this->getAffiliationService(),
                'affiliation'           => $affiliation,
                'contactsInAffiliation' => $this->getContactService()->findContactsInAffiliation($affiliation),
                'projectService'        => $this->getProjectService(),
                'workpackageService'    => $this->getWorkpackageService(),
                'latestVersion'         => $this->getProjectService()->getLatestProjectVersion(
                    $affiliation->getProject()
                ),
                'versionType'           => $this->getProjectService()->getNextMode(
                    $affiliation->getProject()
                )->versionType,
                'reportService'         => $this->getReportService(),
                'versionService'        => $this->getVersionService(),
                'invoiceService'        => $this->getInvoiceService(),
                'organisationService'   => $this->getOrganisationService(),
            ]
        );
    }

    /**
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function mergeAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $mainAffiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($mainAffiliation)) {
            return $this->notFoundAction();
        }

        $data = $request->getPost()->toArray();

        if (isset($data['merge'], $data['submit']) && $request->isPost()) {
            // Find the second affiliation
            $otherAffiliation = $this->getAffiliationService()->findAffiliationById($data['merge']);
            $otherOrganisation = $otherAffiliation->getOrganisation();

            $result = $this->mergeAffiliation($mainAffiliation, $otherAffiliation);

            if ($result['success'] === true) {
                $this->flashMessenger()->setNamespace(FlashMessenger::NAMESPACE_SUCCESS)
                    ->addMessage(sprintf(
                        $this->translate("txt-merge-of-affiliation-%s-and-%s-in-project-%s-was-successful"),
                        $mainAffiliation->getOrganisation(),
                        $otherOrganisation,
                        $mainAffiliation->getProject()
                    ));
            } else {
                $this->flashMessenger()->setNamespace(FlashMessenger::NAMESPACE_ERROR)
                    ->addMessage(sprintf($this->translate('txt-merge-failed:-%s'), $result['errorMessage']));
            }

            return $this->redirect()->toRoute(
                'zfcadmin/affiliation/view',
                ['id' => $mainAffiliation->getId()]
            );
        }

        return new ViewModel([
            'affiliationService'  => $this->getAffiliationService(),
            'affiliation'         => $mainAffiliation,
            'merge'               => isset($data['merge']) ? $data['merge'] : null,
            'projectService'      => $this->getProjectService(),
            'organisationService' => $this->getOrganisationService(),
        ]);
    }

    /**
     * @return array|\Zend\Http\Response|ViewModel
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

        // Try to populate the form based on the organisation known already
        if (is_null($affiliation->getParentOrganisation())) {
            $organisation = $affiliation->getOrganisation();
            if (!is_null($organisation->getParent())) {
                $formData['parent'] = $organisation->getParent()->getId();
            }
            if (!is_null($organisation->getParentOrganisation())) {
                $formData['parentOrganisation'] = $organisation->getParentOrganisation()->getId();
                $formData['parentOrganisationLike'] = $organisation->getParentOrganisation()->getId();
            }
        } else {
            $formData['parent'] = $affiliation->getParentOrganisation()->getParent()->getId();
            $formData['parentOrganisation'] = $affiliation->getParentOrganisation()->getId();
            $formData['parentOrganisationLike'] = $affiliation->getParentOrganisation()->getId();
        }

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

        // Only fill the formData of the finanicalOrganisation when this is known
        if (!is_null($financial = $affiliation->getFinancial())) {
            $formData['financialOrganisation'] = $financial->getOrganisation()->getId();
            $formData['financialBranch'] = $financial->getBranch();
            $formData['financialContact'] = $financial->getContact()->getId();
            $formData['emailCC'] = $financial->getEmailCC();
        }


        $form = new AdminAffiliation($affiliation, $this->getParentService());
        $form->setData($formData);

        $form->get('contact')->injectContact($affiliation->getContact());
        $form->get('organisation')->injectOrganisation($affiliation->getOrganisation());
        if (!is_null($affiliation->getFinancial())) {
            $form->get('financialOrganisation')->injectOrganisation($affiliation->getFinancial()->getOrganisation());
            $form->get('financialContact')->injectContact($affiliation->getFinancial()->getContact());
        }

        //Remove the delete when an affilation is active in a version
        if ($this->getAffiliationService()->isActiveInVersion($affiliation)) {
            $form->remove('delete');
        }

        if ($this->getRequest()->isPost() && $form->setData($this->getRequest()->getPost()->toArray())) {
            if (isset($formData['cancel'])) {
                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId(),]
                );
            }

            if ($form->isValid()) {
                $formData = $form->getData();

                //Find the selected organisation
                $organisation = $this->getOrganisationService()
                    ->findOrganisationById($formData['organisation']);
                $contact = $this->getContactService()->findContactById($formData['contact']);

                switch (true) {
                    case !empty($formData['parentOrganisationLike']):
                        /** @var Organisation $parentOrganisation */
                        $parentOrganisation = $this->getParentService()->findEntityById(
                            Organisation::class,
                            $formData['parentOrganisationLike']
                        );
                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($parentOrganisation->getOrganisation());
                        break;
                    case !empty($formData['parentOrganisation']):
                        /** @var Organisation $parentOrganisation */
                        $parentOrganisation = $this->getParentService()->findEntityById(
                            Organisation::class,
                            $formData['parentOrganisation']
                        );
                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($parentOrganisation->getOrganisation());
                        break;
                    case !empty($formData['parent']):
                        // When a parent is selected, use that to find the $parent
                        $parent = $this->getParentService()->findParentById($formData['parent']);
                        $parentOrganisation = $this->getParentService()->findParentOrganisationInParentByOrganisation(
                            $parent,
                            $organisation
                        );

                        if (is_null($parentOrganisation)) {
                            $parentOrganisation = new Organisation();
                            $parentOrganisation->setOrganisation($organisation);
                            $parentOrganisation->setParent($parent);
                            $parentOrganisation->setContact($this->getContactService()
                                ->findContactById($formData['contact']));
                            $this->getParentService()->newEntity($parentOrganisation);
                        }
                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($organisation);
                        break;
                    case $formData['createParentFromOrganisation'] === '1':
                        // Find first the organisation
                        $organisation = $this->getOrganisationService()
                            ->findOrganisationById($formData['organisation']);
                        $parentOrganisation = $this->getParentService()
                            ->createParentAndParentOrganisationFromOrganisation(
                                $organisation,
                                $affiliation->getContact()
                            );

                        $affiliation->setParentOrganisation($parentOrganisation);
                        $affiliation->setOrganisation($organisation);
                        break;
                    default:
                        $parentOrganisation = $affiliation->getParentOrganisation();
                        $affiliation->setOrganisation($organisation);
                        break;
                }

                // The partner has been updated now, so we need to store the name of the organiation and the project
                if (!is_null($parentOrganisation)
                    && is_null($this->getOrganisationService()
                        ->findOrganisationNameByNameAndProject(
                            $parentOrganisation->getOrganisation(),
                            $organisation->getOrganisation(),
                            $affiliation->getProject()
                        ))
                ) {
                    $name = new Name();
                    $name->setOrganisation($parentOrganisation->getOrganisation());
                    $name->setName($organisation->getOrganisation());
                    $name->setProject($affiliation->getProject());
                    $this->getOrganisationService()->newEntity($name);
                }

                // Update the affiliation based on the form information
                $affiliation->setContact($contact);

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

                // Only update the financial when an financial organisation is chosen
                if (!empty($formData['financialOrganisation'])) {
                    if (is_null($financial = $affiliation->getFinancial())) {
                        $financial = new Financial();
                        $financial->setAffiliation($affiliation);
                    }

                    $financial->setOrganisation(
                        $this->getOrganisationService()
                            ->findOrganisationById($formData['financialOrganisation'])
                    );
                    $financial->setContact($this->getContactService()->findContactById($formData['financialContact']));
                    $financial->setBranch($formData['financialBranch']);
                    if (!empty($formData['emailCC'])) {
                        $financial->setEmailCC($formData['emailCC']);
                    }


                    $this->getAffiliationService()->updateEntity($financial);
                }

                $this->flashMessenger()->setNamespace('success')->addMessage(sprintf(
                    $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                    $affiliation
                ));

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()]
                );
            }
        }

        return new ViewModel(
            [
                'affiliation'    => $affiliation,
                'projectService' => $this->getProjectService(),
                'form'           => $form,
            ]
        );
    }


    /**
     * @return array|\Zend\Http\Response|ViewModel
     */
    public function editAssociateAction()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $affiliation = $this->getAffiliationService()->findAffiliationById($this->params('id'));

        if (is_null($affiliation)) {
            return $this->notFoundAction();
        }

        $contact = $this->getContactService()->findContactById($this->params('contact'));
        if (is_null($contact)) {
            return $this->notFoundAction();
        }

        $data = array_merge(['affiliation' => $affiliation->getId()], $request->getPost()->toArray());

        $form = new EditAssociate($affiliation);
        $form->setData($data);

        if ($request->isPost()) {
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
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-associate-%s-has-successfully-been-removed"),
                            $contact->getDisplayName()
                        )
                    );

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
                $affiliation = $this->getAffiliationService()->findAffiliationById($formData['affiliation']);
                $affiliation->addAssociate($contact);

                $this->getAffiliationService()->updateEntity($affiliation);

                $this->flashMessenger()->setNamespace('success')
                    ->addMessage(
                        sprintf(
                            $this->translate("txt-affiliation-%s-has-successfully-been-updated"),
                            $affiliation
                        )
                    );

                return $this->redirect()->toRoute(
                    'zfcadmin/affiliation/view',
                    ['id' => $affiliation->getId()],
                    ['fragment' => 'associates']
                );
            }
        }

        return new ViewModel([
            'affiliation'    => $affiliation,
            'projectService' => $this->getProjectService(),
            'contact'        => $contact,
            'form'           => $form,
        ]);
    }

    /**
     * @return ViewModel
     */
    public function missingAffiliationParentAction()
    {
        $page = $this->params()->fromRoute('page', 1);
        $filterPlugin = $this->getAffiliationFilter();
        $missingAffiliationParent = $this->getAffiliationService()->findMissingAffiliationParent();

        $paginator
            = new Paginator(new PaginatorAdapter(new ORMPaginator($missingAffiliationParent, false)));
        $paginator::setDefaultItemCountPerPage(($page === 'all') ? PHP_INT_MAX : 20);
        $paginator->setCurrentPageNumber($page);
        $paginator->setPageRange(ceil($paginator->getTotalItemCount() / $paginator::getDefaultItemCountPerPage()));

        $form = new MissingAffiliationParentFilter();
        $form->setData(['filter' => $filterPlugin->getFilter()]);

        return new ViewModel([
            'paginator'     => $paginator,
            'form'          => $form,
            'encodedFilter' => urlencode($filterPlugin->getHash()),
            'order'         => $filterPlugin->getOrder(),
            'direction'     => $filterPlugin->getDirection(),
        ]);
    }
}
