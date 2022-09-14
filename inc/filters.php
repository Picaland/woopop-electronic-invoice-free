<?php
/**
 * filters.php
 *
 * @since      1.0.0
 * @package    ${NAMESPACE}
 * @author     alfiopiccione <alfio.piccione@gmail.com>
 * @copyright  Copyright (c) 2018, alfiopiccione
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2
 *
 * Copyright (C) 2018 alfiopiccione <alfio.piccione@gmail.com>
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$optionPage = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();

//
//  array(
//      'filter'        => ''
//      'callback'      => ''
//      'priority'      => ''
//      'accepted_args' => ''
//  )
//
$filters = array(
    'inc' => array(
        'action' => array(
            /**
             * Options
             *
             * - adminToolbar @since 1.0.0
             */
            array(
                'filter'   => 'admin_bar_menu',
                'callback' => array($optionPage, 'adminToolbar'),
                'priority' => 100,
            ),
        ),
        'filter' => array(),
    ),
);

if (class_exists('\Dompdf\Dompdf')) {
    /**
     * CreatePdf instance
     */
    $pdf = new \WcElectronInvoiceFree\Pdf\CreatePdf(new \Dompdf\Dompdf());

    /**
     * Remove temp Pdf files after sent completed email @since 1.0.0
     */
    $filters['inc']['action'][] = array(
        'filter'   => 'wp_loaded',
        'callback' => array($pdf, 'removeSentInvoice'),
        'priority' => 10,
    );

    /**
     * - wc mail completed attachments    @since 1.0.0
     */
    $filters['inc']['filter'][] = array(
        'filter'        => 'woocommerce_email_attachments',
        'callback'      => array($pdf, 'attachmentsPdfToEmail'),
        'priority'      => PHP_INT_MAX,
        'accepted_args' => 3,
    );
}

return apply_filters('wc_el_inv-filters', $filters);