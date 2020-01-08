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

namespace Affiliation\Controller;

use Affiliation\Controller\Plugin;
use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;
use Project\Controller\Plugin\Checklist\ProjectChecklist;
use Project\Entity\Project;
use Project\Entity\Version\Type;
use Search\Service\AbstractSearchService;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Plugin\Identity\Identity;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * @method      ZfcUserAuthentication zfcUserAuthentication()
 * @method      Identity|Contact identity()
 * @method      FlashMessenger flashMessenger()
 * @method      bool isAllowed($resource, $action)
 * @method      ProjectChecklist projectChecklist(Project $project, Type $versionType)
 * @method      Plugin\AffiliationPdf renderPaymentSheet(Affiliation $affiliation, int $year, int $period, bool $useContractData)
 * @method      Plugin\RenderLoi renderLoi()
 * @method      Plugin\GetFilter getAffiliationFilter()
 * @method      Plugin\MergeAffiliation mergeAffiliation($mainAffiliation, $affiliation)
 * @method      Response csvExport(AbstractSearchService $searchService, array $fields, bool $header = true)
 *
 */
abstract class AffiliationAbstractController extends AbstractActionController
{
}
