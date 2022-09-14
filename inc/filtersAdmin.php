<?php
/**
 * filtersAdmin.php
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

$billingFields = include_once \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/wc/billingFields.php');
$generalFields = include_once \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/wc/generalInvoiceFields.php');

$resources         = new \WcElectronInvoiceFree\Resources();
$optionPage        = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();
$invoiceFields     = new \WcElectronInvoiceFree\WooCommerce\Fields\InvoiceFields($billingFields, $optionPage);
$generalShopFields = new \WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields($generalFields);
//
//  array(
//      'filter'        => ''
//      'callback'      => ''
//      'priority'      => ''
//      'accepted_args' => ''
//  )
//
$filtersAdmin = array(
    'admin' => array(
        'action' => array(
            /**
             * Options
             *
             * - addPluginPage   @since 1.0.0
             * - pageOptionsInit @since 1.0.0
             */
            array(
                'filter'   => 'admin_menu',
                'callback' => array($optionPage, 'addPluginPage'),
                'priority' => 10,
            ),
            array(
                'filter'   => 'admin_init',
                'callback' => array($optionPage, 'pageOptionsInit'),
                'priority' => 10,
            ),

            /**
             * WooCommerce
             *
             * - set invoice number on order completed @since 1.0.0
             * - set invoice number on order refund    @since 1.0.0
             * - set initial invoice number            @since 1.0.0
             * - extra billing fields                  @since 1.0.0
             * - edit invoice meta                     @since 1.0.0
             * - save invoice meta                     @since 1.0.0
             * - remove sent invoice attachment        @since 1.0.0
             */
            array(
                'filter'        => 'woocommerce_order_status_changed',
                'callback'      => 'WcElectronInvoiceFree\\Functions\\setInvoiceNumberOnOrderCompleted',
                'priority'      => 20,
                'accepted_args' => 3,
            ),
            array(
                'filter'   => 'woocommerce_refund_created',
                'callback' => 'WcElectronInvoiceFree\\Functions\\setInvoiceNumberOnOrderRefund',
                'priority' => 20,
            ),
            array(
                'filter'   => 'init',
                'callback' => 'WcElectronInvoiceFree\\Functions\\setInitInvoiceNumber',
                'priority' => 20,
            ),
            array(
                'filter'   => 'woocommerce_admin_order_data_after_billing_address',
                'callback' => array($invoiceFields, 'viewBillingFieldsFilter'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'woocommerce_admin_order_data_after_order_details',
                'callback' => array($invoiceFields, 'editGeneralFieldsFilter'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'woocommerce_admin_order_items_after_refunds',
                'callback' => array($invoiceFields, 'editInvoiceDataOrderRefund'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'woocommerce_admin_order_totals_after_refunded',
                'callback' => array($invoiceFields, 'refundedPaymentMethod'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'woocommerce_process_shop_order_meta',
                'callback' => array($invoiceFields, 'saveOrderMetaBox'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'woocommerce_process_shop_order_meta',
                'callback' => array($invoiceFields, 'saveRefundMetaBox'),
                'priority' => 20,
            ),

            /**
             * Cache Object @since 1.0.0
             */
            array(
                'filter'   => array(
                    'save_post_shop_order',
                    'before_delete_post',
                ),
                'callback' => function ($postID) {
                    $cacher = new \WcElectronInvoiceFree\Cache\CacheTransient();
                    $type   = get_post_type($postID);
                    switch ($type) {
                        case 'shop_order':
                            $cacher->delete('shop_order');
                            $cacher->delete("shop_order-{$postID}");
                            break;
                        default:
                            break;
                    }
                },
                'priority' => 20,
            ),

            /**
             * Enqueue @since 1.0.0
             */
            array(
                'filter'   => 'admin_enqueue_scripts',
                'callback' => array($resources, 'register'),
                'priority' => 10,
            ),
            array(
                'filter'   => 'admin_enqueue_scripts',
                'callback' => array($resources, 'enqueue'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'admin_enqueue_scripts',
                'callback' => array($resources, 'localizeScript'),
                'priority' => 30,
            ),

            /**
             * Ajax mark "sent/no_sent" invoice
             */
            array(
                'filter'   => 'wp_ajax_markInvoice',
                'callback' => array($invoiceFields, 'markInvoice'),
                'priority' => 10,
            ),

            /**
             * Premium banner
             */
            array(
                'filter'   => 'wc_el_inv-before_settings_wrapper',
                'callback' => 'WcElectronInvoiceFree\\Functions\\premiumBanner',
                'priority' => 10,
            ),
        ),
        'filter' => array(
            /**
             * WooCommerce
             *
             * - customer fields                  @since 1.0.0
             * - billing fields                   @since 1.0.0
             * - ajax customer fields             @since 1.0.0
             * - general setting invoice fields   @since 1.0.0
             * - general setting invoice sanitize @since 1.0.0
             * - wc general notice                @since 1.0.0
             */
            array(
                'filter'   => 'woocommerce_customer_meta_fields',
                'callback' => array($invoiceFields, 'customerFieldsFilter'),
                'priority' => 20,
            ),
            array(
                'filter'   => 'woocommerce_admin_billing_fields',
                'callback' => array($invoiceFields, 'editBillingFieldsFilter'),
                'priority' => 20,
            ),
            array(
                'filter'        => 'woocommerce_ajax_get_customer_details',
                'callback'      => array($invoiceFields, 'foundCustomerMeta'),
                'priority'      => 20,
                'accepted_args' => 3,
            ),
            array(
                'filter'   => 'woocommerce_general_settings',
                'callback' => array($generalShopFields, 'generalInvoiceFields'),
                'priority' => 20,
            ),
            array(
                'filter'        => 'woocommerce_admin_settings_sanitize_option',
                'callback'      => array($generalShopFields, 'sanitize'),
                'priority'      => 20,
                'accepted_args' => 3,
            ),
            array(
                'filter'   => 'admin_notices',
                'callback' => array($generalShopFields, 'notice'),
                'priority' => 20,
            ),
        ),
    ),
);

return apply_filters('wc_el_inv-filters_admin', $filtersAdmin);