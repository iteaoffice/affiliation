<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Controller\Json;

use Affiliation\Controller\AffiliationAbstractController;
use Affiliation\Entity\Affiliation;
use Affiliation\Entity\Doa;
use Affiliation\Service\AffiliationService;
use Affiliation\Service\DoaService;
use Contact\Service\ContactService;
use DateTime;
use General\Service\EmailService;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\JsonModel;

/**
 * Class ManagerController
 * @package Affiliation\Controller\Doa
 */
final class DoaController extends AffiliationAbstractController
{
    private DoaService $doaService;
    private AffiliationService $affiliationService;
    private ContactService $contactService;
    private EmailService $emailService;
    private TranslatorInterface $translator;

    public function __construct(
        DoaService $doaService,
        AffiliationService $affiliationService,
        ContactService $contactService,
        EmailService $emailService,
        TranslatorInterface $translator
    ) {
        $this->doaService         = $doaService;
        $this->affiliationService = $affiliationService;
        $this->contactService     = $contactService;
        $this->emailService       = $emailService;
        $this->translator         = $translator;
    }

    public function approveAction(): JsonModel
    {
        $doa       = $this->params()->fromPost('doa');
        $sendEmail = $this->params()->fromPost('sendEmail', 0);

        /**
         * @var $doa Doa
         */
        $doa = $this->affiliationService->find(Doa::class, (int)$doa);

        if ($doa->hasObject()) {
            $contact    = $this->params()->fromPost('contact');
            $dateSigned = $this->params()->fromPost('dateSigned');
            if (empty($contact) || empty($dateSigned)) {
                return new JsonModel(
                    [
                        'result' => 'error',
                        'error'  => $this->translator->translate('txt-contact-or-date-signed-is-empty'),
                    ]
                );
            }

            if (!DateTime::createFromFormat('Y-m-d', $dateSigned)) {
                return new JsonModel(
                    [
                        'result' => 'error',
                        'error'  => $this->translator->translate('txt-incorrect-date-format-should-be-yyyy-mm-dd'),
                    ]
                );
            }
            $doa->setContact($this->contactService->findContactById((int)$contact));
            $doa->setDateSigned(DateTime::createFromFormat('Y-m-d', $dateSigned));
        }

        $doa->setDateApproved(new DateTime());
        $doa->setApprover($this->identity());
        $this->doaService->save($doa);

        /**
         * Send the email to the user
         */
        if ($sendEmail === 'true') {
            $email = $this->emailService->createNewWebInfoEmailBuilder('/affiliation/doa/approved');
            $email->addContactTo($doa->getContact());

            /** @var Affiliation $affiliation */
            $affiliation = $doa->getAffiliation();

            $templateVariables = [
                'organisation'  => $affiliation->parseBranchedName(),
                'project'       => $affiliation->getProject()->parseFullName(),
                'date_signed'   => $doa->getDateSigned(),
                'date_approved' => $doa->getDateApproved()
            ];

            $email->setTemplateVariables($templateVariables);

            $this->emailService->sendBuilder($email);
        }

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }

    public function declineAction(): JsonModel
    {
        $doa = $this->params()->fromPost('doa');
        $doa = $this->affiliationService->find(Doa::class, (int)$doa);
        $this->doaService->delete($doa);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
