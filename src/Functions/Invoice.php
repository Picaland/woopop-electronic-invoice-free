<?php
/**
 * Invoice.php
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

namespace WcElectronInvoiceFree\Functions;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Admin\Settings\OptionPage;
use WcElectronInvoiceFree\Utils\TimeZone;

/**
 * Set initial invoice number
 *
 * @since 1.0.0
 */
//function setInitInvoiceNumber()
//{
//    $control = get_option('invoice_number_initial_setting');
//
//    if (1 === intval($control)) {
//        remove_action('init', 'WcElectronInvoiceFree\\Functions\\setInitInvoiceNumber', 20);
//
//        return;
//    }
//
//    $args  = array(
//        'status'  => array('processing', 'completed', 'refunded'),
//        'limit'   => -1,
//        'orderby' => 'date',
//        'order'   => 'ASC',
//    );
//    $query = new \WC_Order_Query($args);
//
//    try {
//        $orders = $query->get_orders();
//
//        $options            = OptionPage::init();
//        $invoiceNumber      = $options->getOptions('number_next_invoice');
//        $invoiceNumber      = false !== $invoiceNumber && '' !== $invoiceNumber ? intval($invoiceNumber) : 1;
//        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
//        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');
//
//        if (! empty($orders)) {
//            foreach ($orders as $order) {
//                if ($order instanceof $wcOrderClass || $order instanceof $wcOrderRefundClass) {
//
//                    // Disable invoice order whit total zero
//                    if (disableInvoiceOnOrderTotalZero($order)) {
//                        continue;
//                    }
//
//                    $invoiceNumberOrder = $order->get_meta('order_number_invoice');
//                    $invoiceNumber      = isset($invoiceNumberOrder) && '' !== $invoiceNumberOrder ? intval($invoiceNumberOrder) : $invoiceNumber;
//                    /**
//                     * Filter invoice number for order.
//                     *
//                     * @param int    $invoiceNumber The progressive invoice number
//                     * @param object $order         The |WC_Order or |WC_Order_Refund object
//                     *
//                     * @since 1.0.0
//                     */
//                    $invoiceNumber = apply_filters(
//                        'wc_el_inv-order_number_invoice_filter',
//                        intval($invoiceNumber),
//                        $order
//                    );
//
//                    if (is_int($invoiceNumber)) {
//                        update_post_meta($order->get_id(), 'order_number_invoice', intval($invoiceNumber));
//
//                        try {
//                            $timeZone = new TimeZone();
//                            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
//                            $date     = new \DateTime('now');
//                            $date->setTimezone($timeZone);
//
//                            // Set invoice sent data
//                            update_post_meta($order->get_id(), '_invoice_sent', 'no_sent');
//                            update_post_meta($order->get_id(), '_invoice_sent_timestamp', $date->getTimestamp());
//                        } catch (\Exception $e) {
//
//                        }
//                    }
//
//                    if (is_int($invoiceNumber)) {
//                        $options->setOption('number_next_invoice', intval($invoiceNumber));
//                    }
//
//                    $invoiceNumber++;
//                }
//            }
//        }
//    } catch (\Exception $e) {
//        echo 'dev' === WC_EL_INV_ENV ? esc_html("No Order for set invoice number {$e->getMessage()}") : '';
//    }
//
//    update_option('invoice_number_initial_setting', 1);
//}

/**
 * Set invoice number on order completed
 *
 * @param $orderID
 * @param $from
 * @param $to
 *
 * @since 1.0.0
 *
 */
function setInvoiceNumberOnOrderCompleted($orderID, $from, $to)
{
    // Check if order exist.
    $order        = wc_get_order($orderID);
    $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');
    if (! $order instanceof $wcOrderClass) {
        return;
    }

    if ('completed' === $from || 'completed' !== $to && 'processing' !== $to) {
        return;
    }

    // Disable invoice order whit total zero
    if (disableInvoiceOnOrderTotalZero($order)) {
        return;
    }

    $checkSent = get_post_meta($orderID, '_invoice_sent', true);

    if ($from !== $to && 'completed' === $to || 'processing' === $to) {
        // Get next invoice number option.
        $options       = OptionPage::init();
        $invoiceNumber = (int)$options->getOptions('number_next_invoice');

        // Order invoice number
        $orderInvoiceNumber = $order->get_meta('order_number_invoice');

        if ((! $checkSent || 'no_sent' === $checkSent) &&
            (! $orderInvoiceNumber)
        ) {
            $next = $invoiceNumber + 1;
            $options->setOption('number_next_invoice', $next);
            // Set invoice number for order.
            update_post_meta($orderID, 'order_number_invoice', $invoiceNumber);
        }

        // Check for fix
        $invoiceNumber      = (int)$options->getOptions('number_next_invoice');
        $orderInvoiceNumber = (int)$order->get_meta('order_number_invoice');
        if ($orderInvoiceNumber === $invoiceNumber) {
            $next = $invoiceNumber + 1;
            $options->setOption('number_next_invoice', $next);
        }

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime('now');
            $date->setTimezone($timeZone);

            if (isset($checkSent) && '' === $checkSent) {
                // Set invoice sent data
                update_post_meta($orderID, '_invoice_sent', 'no_sent');
                update_post_meta($orderID, '_invoice_sent_timestamp', $date->getTimestamp());
            }
        } catch (\Exception $data_Exception) {
            echo 'setInvoiceNumberOnOrderCompleted: ', $data_Exception->getMessage(), "\n";
            die();
        };
    }
}

/**
 * Set invoice number on order auto completed
 *
 * @param $id
 *
 * @since 1.0.0
 *
 */
function setInvoiceNumberOnOrderAutoCompleted($id)
{
    $order        = wc_get_order($id);
    $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');
    // Check if order exist.
    if (! $order instanceof $wcOrderClass) {
        return;
    }

    // Disable invoice order whit total zero
    if (disableInvoiceOnOrderTotalZero($order)) {
        return;
    }

    // Check status.
    if ('completed' === $order->get_status() || 'processing' === $order->get_status()) {
        $checkSent = get_post_meta($order->get_id(), '_invoice_sent', true);

        // Get next invoice number option.
        $options       = OptionPage::init();
        $invoiceNumber = (int)$options->getOptions('number_next_invoice');
        // Order invoice number
        $orderInvoiceNumber = $order->get_meta('order_number_invoice');

        if ((! $checkSent || 'no_sent' === $checkSent) &&
            (! $orderInvoiceNumber)
        ) {
            $next = $invoiceNumber + 1;
            $options->setOption('number_next_invoice', $next);
            // Set invoice number for order.
            update_post_meta($order->get_id(), 'order_number_invoice', $invoiceNumber);
        }

        // Check for fix
        $invoiceNumber      = (int)$options->getOptions('number_next_invoice');
        $orderInvoiceNumber = (int)$order->get_meta('order_number_invoice');
        if ($orderInvoiceNumber === $invoiceNumber) {
            $next = $invoiceNumber + 1;
            $options->setOption('number_next_invoice', $next);
        }

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime('now');
            $date->setTimezone($timeZone);

            if (isset($checkSent) && '' === $checkSent) {
                // Set invoice sent data
                update_post_meta($order->get_id(), '_invoice_sent', 'no_sent');
                update_post_meta($order->get_id(), '_invoice_sent_timestamp', $date->getTimestamp());
            }

            // Save date completed
            $order->set_date_completed($date->getTimestamp());
            $order->save();
        } catch (\Exception $data_Exception) {

        };
    }
}

/**
 * Set invoice number on order refund
 *
 * @param $refundID
 *
 * @since 1.0.0
 *
 */
function setInvoiceNumberOnOrderRefund($refundID)
{
    $refund = wc_get_order($refundID);
    $order  = wc_get_order($refund->get_parent_id());

    // Disable invoice order whit total zero
    if (disableInvoiceOnOrderTotalZero($order)) {
        return;
    }

    $checkOrderSent     = get_post_meta($order->get_id(), '_invoice_sent', true);
    $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($refund, '\WC_Order_Refund');

    if ($refund instanceof $wcOrderRefundClass) {
        // Get next invoice number option.
        $options       = OptionPage::init();
        $invoiceNumber = $options->getOptions('number_next_invoice');
        $invoiceNumber = intval($invoiceNumber);

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime('now');
            $date->setTimezone($timeZone);

            // Increment invoice number only not total refund
            if (('' === $checkOrderSent || 'sent' === $checkOrderSent) &&
                floatval(0) <= (abs($order->get_total()) - abs($refund->get_total()))) {
                // Set next invoice number.
                $options->setOption('number_next_invoice', $invoiceNumber + 1);
            }

            // Set invoice number for order.
            update_post_meta($refundID, "refund_number_invoice-{$refundID}", $invoiceNumber);
            update_post_meta($refundID, '_invoice_sent', 'no_sent');
            update_post_meta($refundID, '_invoice_sent_timestamp', $date->getTimestamp());
        } catch (\Exception $e) {

        }
    }
}

/**
 * Disable invoice on order total zero
 *
 * @param $order
 *
 * @return bool
 */
function disableInvoiceOnOrderTotalZero($order)
{
    $options              = OptionPage::init();
    $disableInvoiceNumber = $options->getOptions('disable_invoice_number_order_zero');
    $wcOrderClass         = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');

    if ('on' === $disableInvoiceNumber && $order instanceof $wcOrderClass) {
        $total = (int)$order->get_total();
        if (0 === $total) {
            return true;
        }
    }

    return false;
}