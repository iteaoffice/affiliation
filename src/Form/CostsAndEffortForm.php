<?php

/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

declare(strict_types=1);

namespace Affiliation\Form;

use Affiliation\Entity;
use Laminas\Form\Element\Csrf;
use Laminas\Form\Element\Submit;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Project\Form\Project\CostPerAffiliationFieldset;
use Project\Form\Project\EffortPerAffiliationFieldset;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;

/**
 * Class CostsAndEffortForm
 * @package Affiliation\Form
 */
final class CostsAndEffortForm extends Form implements InputFilterProviderInterface
{
    public function __construct(
        Entity\Affiliation $affiliation,
        ProjectService $projectService,
        WorkpackageService $workpackageService
    ) {
        parent::__construct();

        $this->setAttribute('method', 'post');
        $this->setAttribute('action', '');
        $this->setAttribute('class', 'form-horizontal');

        $costFieldset        = new Fieldset('costPerAffiliationAndYear');
        $affiliationFieldset = new CostPerAffiliationFieldset(
            $affiliation,
            $affiliation->getProject(),
            $projectService
        );
        $costFieldset->add($affiliationFieldset);
        $costFieldset->setUseAsBaseFieldset(true);
        $this->add($costFieldset);

        if (! $projectService->hasWorkPackages($affiliation->getProject())) {
            $effortFieldset      = new Fieldset('effortPerAffiliationAndYear');
            $affiliationFieldset = new EffortPerAffiliationFieldset(
                $affiliation,
                $affiliation->getProject(),
                $projectService
            );
            $effortFieldset->add($affiliationFieldset);
            $effortFieldset->setUseAsBaseFieldset(true);
            $this->add($effortFieldset);
        }

        if ($projectService->hasWorkPackages($affiliation->getProject())) {
            $effortFieldset = new Fieldset('effortPerAffiliationAndYear');
            foreach ($workpackageService->findWorkpackageByProjectAndWhich($affiliation->getProject()) as $workPackage) {
                $workPackageFieldset = new Fieldset($workPackage->getId());

                $affiliationFieldSet = new EffortPerAffiliationFieldset(
                    $affiliation,
                    $affiliation->getProject(),
                    $projectService,
                    $workPackage
                );

                $workPackageFieldset->add($affiliationFieldSet);
                $effortFieldset->add($workPackageFieldset);
            }
            $effortFieldset->setUseAsBaseFieldset(true);
            $this->add($effortFieldset);
        }

        $this->add(
            [
                'type' => Csrf::class,
                'name' => 'csrf',
            ]
        );

        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'submit',
                'attributes' => [
                    'class' => 'btn btn-primary',
                    'value' => _('txt-update-costs-and-effort'),
                ],
            ]
        );
        $this->add(
            [
                'type'       => Submit::class,
                'name'       => 'cancel',
                'attributes' => [
                    'class' => 'btn btn-warning',
                    'value' => _('txt-cancel'),
                ],
            ]
        );
    }

    public function getInputFilterSpecification(): array
    {
        return [];
    }
}
