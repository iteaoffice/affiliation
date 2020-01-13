<?php

/**
 * Program Options
 *
 * If you have a ./config/autoload/ directory set up for your project, you can
 * drop this config file in it and change the values as you wish.
 */

$options = [
    /**
     * Indicate here if a project has versions
     */
    'doa_template'           => __DIR__ . '/../../../../styles/itea/template/pdf/doa-template.pdf',
    'loi_template'           => __DIR__ . '/../../../../styles/itea/template/pdf/nda-template.pdf',
    'payment_sheet_template' => __DIR__ . '/../../../../styles/itea/template/pdf/blank-template-firstpage.pdf',
];
/**
 * You do not need to edit below this line
 */
return [
    'affiliation_option' => $options,
];
