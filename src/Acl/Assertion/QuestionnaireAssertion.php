<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Affiliation as AffiliationEntity;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\QuestionnaireService;
use Contact\Entity\Contact;
use Interop\Container\ContainerInterface;
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\ResourceInterface;
use Laminas\Permissions\Acl\Role\RoleInterface;

/**
 * Class QuestionnaireAssertion
 * @package Affiliation\Acl\Assertion
 */
final class QuestionnaireAssertion extends AbstractAssertion
{
    private QuestionnaireService $questionnaireService;
    private AffiliationService $affiliationService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->questionnaireService = $container->get(QuestionnaireService::class);
        $this->affiliationService   = $container->get(AffiliationService::class);
    }

    public function assert(
        Acl $acl,
        RoleInterface $role = null,
        ResourceInterface $questionnaire = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);
        $id = $this->getId();

        // Office always has access
        if ($this->rolesHaveAccess(Access::ACCESS_OFFICE)) {
            return true;
        }

        if (! ($questionnaire instanceof Questionnaire) && (null !== $id)) {
            $questionnaire = $this->questionnaireService->find(Questionnaire::class, $id);
        }

        $affiliationId = $this->getRouteMatch()->getParam('affiliationId');

        $affiliation = null;
        if ($affiliationId !== null) {
            $affiliation = $this->affiliationService->findAffiliationById((int)$affiliationId);
        }

        $isTechContact = (
            ($affiliation instanceof AffiliationEntity)
            && ($affiliation->getContact() instanceof Contact)
            && ($this->contact->getId() === $affiliation->getContact()->getId())
        );

        switch ($this->getPrivilege()) {
            case 'overview':
            case 'view-community':
                return $this->hasContact();
            case 'edit-community':
                // User should be tech contact and questionnaire should be open
                return $isTechContact && $this->questionnaireService->isOpen($questionnaire, $affiliation);
        }

        return true;
    }
}
