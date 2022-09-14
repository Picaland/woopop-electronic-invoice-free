<?php
/**
 * ordersJsonArgs.php
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

if (! isset($orderType) &&
    ! isset($customerID) &&
    empty($orderData) &&
    empty($itemsData) &&
    empty($invoiceMeta)
) {
    return (object)array();
}

// Set meta data
if (! empty($itemsData)) {
    foreach ($itemsData as $index => $data) {
        if (! empty($data['meta_data'])) {
            $itemsData[$index]['meta_data'] = array();
            foreach ($data['meta_data'] as $meta) {
                if ($meta instanceof WC_Meta_Data) {
                    $itemsData[$index]['meta_data'][] = $meta->get_data();
                }
            }
        }
    }
}

// Initialized data array
$data = array(
    'order_type'           => $orderType,
    'id'                   => $orderData['id'],
    'status'               => $orderData['status'],
    'currency'             => $orderData['currency'],
    'date_created'         => $orderData['date_created'],
    'date_modified'        => $orderData['date_modified'],
    'discount_total'       => $orderData['discount_total'],
    'discount_tax'         => $orderData['discount_tax'],
    'shipping_total'       => $orderData['shipping_total'],
    'shipping_tax'         => $orderData['shipping_tax'],
    'cart_tax'             => $orderData['cart_tax'],
    'total'                => $orderData['total'],
    'total_tax'            => $orderData['total_tax'],
    'customer_id'          => $customerID,
    'billing'              => array_map('\\WcElectronInvoiceFree\\Functions\\stripTags', $orderData['billing']),
    'tax_code'             => $invoiceMeta['tax_code'],
    'vat_number'           => $invoiceMeta['vat_number'],
    'invoice_type'         => $invoiceMeta['invoice_type'],
    'sdi_type'             => $invoiceMeta['sdi_type'],
    'choice_type'          => $invoiceMeta['choice_type'],
    'shipping_check'       => isset($shippingCheck) ? $shippingCheck : '',
    'shipping'             => array_map('\\WcElectronInvoiceFree\\Functions\\stripTags', $orderData['shipping']),
    'payment_method'       => $orderData['payment_method'],
    'payment_method_title' => $orderData['payment_method_title'],
    'customer_user_agent'  => $orderData['customer_user_agent'],
    'created_via'          => $orderData['created_via'],
    'customer_note'        => $orderData['customer_note'],
    'date_completed'       => $orderData['date_completed'],
    'date_paid'            => $orderData['date_paid'],
    'items'                => $itemsData,
);

if (! empty($itemsDataTax)) {
    $data['items_tax'] = $itemsDataTax;
}

if (! empty($itemsDataShip)) {
    $data['items_shipping'] = $itemsDataShip;
}

if (! empty($itemsDataFee)) {
    $data['items_fee'] = $itemsDataFee;
}

if (isset($invoiceNumber) && '' !== $invoiceNumber) {
    $data['invoice_number'] = $invoiceNumber;
}

if (isset($invoiceSent) && '' !== $invoiceSent) {
    $data['invoice_sent'] = $invoiceSent;
}

if (! empty($refundedData)) {
    $data['refunded'] = $refundedData;
}

if (! empty($refundedItem)) {
    $data['items_refunded'] = $refundedItem;
}

// Sanitize
$args = array(
    'order_type'           => FILTER_UNSAFE_RAW,
    'id'                   => FILTER_VALIDATE_INT,
    'status'               => FILTER_UNSAFE_RAW,
    'currency'             => FILTER_UNSAFE_RAW,
    'date_created'         => array(
        'data'          => FILTER_UNSAFE_RAW,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_UNSAFE_RAW,
    ),
    'date_modified'        => array(
        'data'          => FILTER_UNSAFE_RAW,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_UNSAFE_RAW,
    ),
    'discount_total'       => FILTER_UNSAFE_RAW,
    'discount_tax'         => FILTER_UNSAFE_RAW,
    'shipping_total'       => FILTER_UNSAFE_RAW,
    'shipping_tax'         => FILTER_UNSAFE_RAW,
    'cart_tax'             => FILTER_UNSAFE_RAW,
    'total'                => FILTER_UNSAFE_RAW,
    'total_tax'            => FILTER_UNSAFE_RAW,
    'customer_id'          => FILTER_VALIDATE_INT,
    'billing'              => array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    ),
    'tax_code'             => FILTER_UNSAFE_RAW,
    'vat_number'           => FILTER_UNSAFE_RAW,
    'invoice_type'         => FILTER_UNSAFE_RAW,
    'sdi_type'             => FILTER_UNSAFE_RAW,
    'choice_type'          => FILTER_UNSAFE_RAW,
    'shipping_check'       => FILTER_UNSAFE_RAW,
    'shipping'             => array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    ),
    'payment_method'       => FILTER_UNSAFE_RAW,
    'payment_method_title' => FILTER_UNSAFE_RAW,
    'customer_user_agent'  => FILTER_UNSAFE_RAW,
    'created_via'          => FILTER_UNSAFE_RAW,
    'customer_note'        => FILTER_UNSAFE_RAW,
    'date_completed'       => array(
        'data'          => FILTER_UNSAFE_RAW,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_UNSAFE_RAW,
    ),
    'date_paid'            => array(
        'data'          => FILTER_UNSAFE_RAW,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_UNSAFE_RAW,
    ),
    'items'                => array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    ),
);

if (! empty($itemsDataTax)) {
    $args['items_tax'] = array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    );
}

if (! empty($itemsDataShip)) {
    $args['items_shipping'] = array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    );
}

if (! empty($itemsDataFee)) {
    $args['items_fee'] = array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    );
}

if (isset($invoiceNumber) && '' !== $invoiceNumber) {
    $args['invoice_number'] = FILTER_VALIDATE_INT;
}

if (isset($invoiceSent) && '' !== $invoiceSent) {
    $args['invoice_sent'] = FILTER_UNSAFE_RAW;
}

if (! empty($refundedData)) {
    $args['refunded'] = array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    );
}

if (! empty($refundedItem)) {
    $args['items_refunded'] = array(
        'filter' => array(FILTER_UNSAFE_RAW),
        'flags'  => FILTER_FORCE_ARRAY,
    );
}

/**
 * Filter data and filter var
 *
 * @since 1.0.0
 */
$data = apply_filters('wc_el_inv-orders_json_data', $data);
$args = apply_filters('wc_el_inv-orders_json_args_filter_var', $args);

$data = filter_var_array($data, $args);

return (object)$data;
