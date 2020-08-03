<?php

/**
*
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2019 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 *
 * @link        http://github.com/iteaoffice/project for the canonical source repository
 */

declare(strict_types=1);

namespace Affiliation\Form;

use Affiliation\Entity;
use Project\Form\Project\CostPerAffiliationFieldset;
use Project\Form\Project\EffortPerAffiliationFieldset;
use Project\Service\ProjectService;
use Project\Service\WorkpackageService;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Laminas\InputFilter\InputFilterProviderInterface;
use Laminas\Form\Element\Submit;
use Laminas\Form\Element\Csrf;

/**
 * Class CostAndEffort
 *
 * @package Affiliation\Form
 */
final class CostAndEffort extends Form implements InputFilterProviderInterface
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

        $costFieldset = new Fieldset('costPerAffiliationAndYear');
        $affiliationFieldset = new CostPerAffiliationFieldset(
            $affiliation,
            $affiliation->getProject(),
            $projectService
        );
        $costFieldset->add($affiliationFieldset);
        $costFieldset->setUseAsBaseFieldset(true);
        $this->add($costFieldset);

        if (! $projectService->hasWorkPackages($affiliation->getProject())) {
            $effortFieldset = new Fieldset('effortPerAffiliationAndYear');
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
            foreach ($workpackageService->findWorkpackageByProjectAndWhich($affiliation->getProject()) as $workpackage) {
                $workPackageFieldset = new Fieldset($workpackage->getId());

                $affiliationFieldSet = new EffortPerAffiliationFieldset(
                    $affiliation,
                    $affiliation->getProject(),
                    $projectService,
                    $workpackage
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
