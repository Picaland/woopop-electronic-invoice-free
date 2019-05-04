<?php
/**
 * filtersFront.php
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

$fields = include_once \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/wc/billingFields.php');

$endPointApi   = new \WcElectronInvoiceFree\EndPoint\Endpoints();
$resources     = new \WcElectronInvoiceFree\Resources();
$optionPage    = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$invoiceFields = new \WcElectronInvoiceFree\WooCommerce\Fields\InvoiceFields($fields, $optionPage);
$pdf           = new \WcElectronInvoiceFree\Pdf\CreatePdf(new \Mpdf\Mpdf());

return apply_filters('wc_el_inv-filters_front', array(
    'front' => array(
        'action' => array(
            /**
             * WooCommerce
             *
             * - checkout process @since 1.0.0
             * - address process @since 1.0.0
             */
            array(
                'filter'   => array(
                    'woocommerce_checkout_process',
                    'woocommerce_after_save_address_validation',
                ),
                'callback' => array($invoiceFields, 'process'),
                'priority' => 20,
            ),

            /**
             * Enqueue @since 1.0.0
             */
            array(
                'filter'   => 'wp_enqueue_scripts',
                'callback' => array($resources, 'enqueue'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'wp_enqueue_scripts',
                'callback' => array($resources, 'localizeScript'),
                'priority' => 30,
            ),
            array(
                'filter'   => 'wc_el_inv-add_json_header',
                'callback' => function () {
                    /**
                     * The origin parameter specifies a URI that may access the resource.
                     * The browser must enforce this. For requests
                     * without credentials, the server may specify "*" as a wildcard,
                     * thereby allowing any origin to access the resource.
                     */
                    header('Access-Control-Allow-Origin: *');

                    /**
                     * The Access-Control-Allow-Methods header specifies the method or methods allowed when accessing
                     * the resource. This is used in response to a preflight request. The conditions under
                     * which a request is preflighted are discussed above.
                     */
                    header('Access-Control-Allow-Methods: POST, GET');

                    /**
                     * The Access-Control-Allow-Credentials response header tells browsers whether to expose the
                     * response to frontend JavaScript code when the request's
                     * credentials mode (Request.credentials) is "include".
                     */
                    header('Access-Control-Allow-Credentials: true');
                },
                'priority' => 10,
            ),

            /**
             * Send xml @since 1.0.0
             */
            array(
                'filter'   => 'template_redirect',
                'callback' => array(
                    new \WcElectronInvoiceFree\Xml\BuildXml(),
                    'send',
                ),
                'priority' => 10,
            ),

            /**
             * Add Endpoint @since 1.0.0
             */
            array(
                'filter'   => 'wp',
                'callback' => array($endPointApi, 'setQueryArgs'),
                'priority' => 10,
            ),
            array(
                'filter'   => 'init',
                'callback' => array($endPointApi, 'addEndPoint'),
                'priority' => 10,
            ),
            array(
                'filter'   => 'init',
                'callback' => array($endPointApi, 'addRewriteEndpoint'),
                'priority' => 10,
            ),

            /**
             * - WC auto completed and processing order @since 1.0.0
             */
            array(
                'filter'        => array(
                    'woocommerce_order_status_completed',
                    'woocommerce_order_status_processing',
                ),
                'callback'      => 'WcElectronInvoiceFree\\Functions\\setInvoiceNumberOnOrderAutoCompleted',
                'priority'      => 10,
                'accepted_args' => 2,
            ),
        ),
        'filter' => array(
            /**
             * PDF Filter xml data for create PDF @since 1.0.0
             */
            array(
                'filter'   => 'wc_el_inv-xml_data_filter',
                'callback' => 'WcElectronInvoiceFree\\Pdf\\CreatePdf::create',
                'priority' => 30,
            ),

            /**
             * Filter Order Json Data
             */
            array(
                'filter'   => array(
                    'wc_el_inv-orders_json_data',
                    'wc_el_inv-orders_refund_json_data',
                ),
                'callback' => 'WcElectronInvoiceFree\\Xml\\CreateXml::filterData',
                'priority' => 10,
            ),

            /**
             * WooCommerce
             *
             * - my account formatted address      @since 1.0.0
             * - billig fields                     @since 1.0.0
             * - formatted address replacements    @since 1.0.0
             * - localization address replacements @since 1.0.0
             * - my account order action           @since 1.0.0
             */
            array(
                'filter'        => 'woocommerce_my_account_my_address_formatted_address',
                'callback'      => array($invoiceFields, 'myAccountFormattedAddress'),
                'priority'      => 20,
                'accepted_args' => 3,
            ),
            array(
                'filter'        => 'woocommerce_order_formatted_billing_address',
                'callback'      => array($invoiceFields, 'orderFormattedBillingAddress'),
                'priority'      => 20,
                'accepted_args' => 2,
            ),
            array(
                'filter'        => 'woocommerce_billing_fields',
                'callback'      => array($invoiceFields, 'billingAddressFields'),
                'priority'      => 20,
                'accepted_args' => 3,
            ),
            array(
                'filter'        => 'woocommerce_formatted_address_replacements',
                'callback'      => array($invoiceFields, 'formattedAddressReplacements'),
                'priority'      => 20,
                'accepted_args' => 2,
            ),
            array(
                'filter'        => 'woocommerce_my_account_my_orders_actions',
                'callback'      => array($invoiceFields, 'actionsFront'),
                'priority'      => 20,
                'accepted_args' => 2,
            ),

            /**
             * Add Query Vars @since 1.0.0
             */
            array(
                'filter'   => 'query_vars',
                'callback' => array($endPointApi, 'addQueryVars'),
                'priority' => 10,
            ),
        ),
    ),
));
