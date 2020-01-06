<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 */

declare(strict_types=1);

namespace Affiliation\Acl\Assertion;

use Admin\Entity\Access;
use Affiliation\Entity\Questionnaire\Questionnaire;
use Affiliation\Entity\Affiliation as AffiliationEntity;
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
    /**
     * @var QuestionnaireService
     */
    private $questionnaireService;

    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
        $this->questionnaireService = $container->get(QuestionnaireService::class);
    }

    public function assert(
        Acl               $acl,
        RoleInterface     $role = null,
        ResourceInterface $affiliation = null,
        $privilege = null
    ): bool {
        $this->setPrivilege($privilege);

        // Office always has access
        if ($this->rolesHaveAccess(Access::ACCESS_OFFICE)) {
            return true;
        }

        $questionnaireId = $this->getId();
        $affiliationId   = $this->getRouteMatch()->getParam('affiliationId');
        if (!($affiliation instanceof AffiliationEntity) && ($affiliationId !== null)) {
            $affiliation = $this->questionnaireService->find(AffiliationEntity::class, (int) $affiliationId);
        }
        $isTechContact   = (
            ($affiliation instanceof AffiliationEntity)
            && ($affiliation->getContact() instanceof Contact)
            && ($this->contact->getId() === $affiliation->getContact()->getId())
        );

        switch ($this->getPrivilege()) {
            case 'overview':
                return $this->hasContact();
            case 'list-community':
            case 'view-community':
                // Only technical contact can access the questionnaires
                return $isTechContact;
            case 'edit-community':
                $questionnaire = new Questionnaire();
                if ($questionnaireId !== null) {
                    $questionnaire = $this->questionnaireService->find(Questionnaire::class, (int) $questionnaireId);
                }
                // User should be tech contact and questionnaire should be open
                return ($isTechContact && $this->questionnaireService->isOpen($questionnaire, $affiliation));
        }

        return true;
    }
}
