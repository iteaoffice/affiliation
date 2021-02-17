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
use Affiliation\Entity;
use Affiliation\Service;
use Contact\Service\ContactService;
use DateTime;
use Laminas\I18n\Translator\TranslatorInterface;
use Laminas\View\Model\JsonModel;

/**
 * Class LoiController
 * @package Affiliation\Controller\Json
 */
final class LoiController extends AffiliationAbstractController
{
    private Service\LoiService $loiService;
    private Service\AffiliationService $affiliationService;
    private ContactService $contactService;
    private TranslatorInterface $translator;

    public function __construct(
        Service\LoiService $loiService,
        Service\AffiliationService $affiliationService,
        ContactService $contactService,
        TranslatorInterface $translator
    ) {
        $this->loiService         = $loiService;
        $this->affiliationService = $affiliationService;
        $this->contactService     = $contactService;
        $this->translator         = $translator;
    }

    public function approveAction(): JsonModel
    {
        $loi        = $this->getEvent()->getRequest()->getPost()->get('loi');
        $contact    = $this->getEvent()->getRequest()->getPost()->get('contact');
        $dateSigned = $this->getEvent()->getRequest()->getPost()->get('dateSigned');

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

        /**
         * @var $loi Entity\Loi
         */
        $loi = $this->affiliationService->find(Entity\Loi::class, (int)$loi);
        $loi->setContact($this->contactService->findContactById((int)$contact));
        $loi->setApprover($this->identity());
        $loi->setDateSigned(DateTime::createFromFormat('Y-m-d', $dateSigned));
        $loi->setDateApproved(new DateTime());
        $this->loiService->save($loi);

        return new JsonModel(
            [
                'result' => 'success',
            ]
        );
    }
}
