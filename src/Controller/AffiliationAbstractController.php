<?php
/**
 * ITEA Office all rights reserved
 *
 * @category    Affiliation
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2004-2017 ITEA Office (https://itea3.org)
 */
declare(strict_types=1);

namespace Affiliation\Controller;

use Affiliation\Controller\Plugin;
use Affiliation\Entity\Affiliation;
use Contact\Entity\Contact;
use Search\Service\AbstractSearchService;
use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Zend\Mvc\Plugin\Identity\Identity;
use ZfcUser\Controller\Plugin\ZfcUserAuthentication;

/**
 * @category    Affiliation
 *
 * @method      ZfcUserAuthentication zfcUserAuthentication()
 * @method      Identity|Contact identity()
 * @method      FlashMessenger flashMessenger()
 * @method      bool isAllowed($resource, $action)
 * @method      Plugin\RenderPaymentSheet renderPaymentSheet(Affiliation $affiliation, int $year, int $period, bool $useContractData)
 * @method      Plugin\RenderDoa renderDoa()
 * @method      Plugin\RenderLoi renderLoi()
 * @method      Plugin\GetFilter getAffiliationFilter()
 * @method      Plugin\MergeAffiliation mergeAffiliation($mainAffiliation, $affiliation)
 * @method      Response csvExport(AbstractSearchService $searchService, array $fields, bool $header = true)
 *
 */
abstract class AffiliationAbstractController extends AbstractActionController
{
}
