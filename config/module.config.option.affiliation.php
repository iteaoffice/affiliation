<?php
/**
 * ITEA Office all rights reserved
 *
 * @author      Johan van der Heide <johan.van.der.heide@itea3.org>
 * @copyright   Copyright (c) 2021 ITEA Office (https://itea3.org)
 * @license     https://itea3.org/license.txt proprietary
 */

$options = [
    /**
     * Indicate here if a project has versions
     */
    'doa_template'           => __DIR__ . '/../../../../styles/itea/template/pdf/doa-template.pdf',
    'loi_template'           => __DIR__ . '/../../../../styles/itea/template/pdf/nda-template.pdf',
    'payment_sheet_template' => __DIR__ . '/../../../../styles/itea/template/pdf/blank-template-firstpage.pdf',
];

return [
    'affiliation_option' => $options,
];
