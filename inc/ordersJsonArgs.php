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
    'billing'              => $orderData['billing'],
    'tax_code'             => $invoiceMeta['tax_code'],
    'vat_number'           => $invoiceMeta['vat_number'],
    'invoice_type'         => $invoiceMeta['invoice_type'],
    'sdi_type'             => $invoiceMeta['sdi_type'],
    'choice_type'          => $invoiceMeta['choice_type'],
    'shipping'             => $orderData['shipping'],
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
    'order_type'           => FILTER_SANITIZE_STRING,
    'id'                   => FILTER_VALIDATE_INT,
    'status'               => FILTER_SANITIZE_STRING,
    'currency'             => FILTER_SANITIZE_STRING,
    'date_created'         => array(
        'data'          => FILTER_SANITIZE_STRING,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_SANITIZE_STRING,
    ),
    'date_modified'        => array(
        'data'          => FILTER_SANITIZE_STRING,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_SANITIZE_STRING,
    ),
    'discount_total'       => FILTER_SANITIZE_STRING,
    'discount_tax'         => FILTER_SANITIZE_STRING,
    'shipping_total'       => FILTER_SANITIZE_STRING,
    'shipping_tax'         => FILTER_SANITIZE_STRING,
    'cart_tax'             => FILTER_SANITIZE_STRING,
    'total'                => FILTER_SANITIZE_STRING,
    'total_tax'            => FILTER_SANITIZE_STRING,
    'customer_id'          => FILTER_VALIDATE_INT,
    'billing'              => array(
        'filter' => array(FILTER_SANITIZE_STRING),
        'flags'  => FILTER_FORCE_ARRAY,
    ),
    'tax_code'             => FILTER_SANITIZE_STRING,
    'vat_number'           => FILTER_SANITIZE_STRING,
    'invoice_type'         => FILTER_SANITIZE_STRING,
    'sdi_type'             => FILTER_SANITIZE_STRING,
    'choice_type'          => FILTER_SANITIZE_STRING,
    'shipping'             => array(
        'filter' => array(FILTER_SANITIZE_STRING),
        'flags'  => FILTER_FORCE_ARRAY,
    ),
    'payment_method'       => FILTER_SANITIZE_STRING,
    'payment_method_title' => FILTER_SANITIZE_STRING,
    'customer_user_agent'  => FILTER_SANITIZE_STRING,
    'created_via'          => FILTER_SANITIZE_STRING,
    'customer_note'        => FILTER_SANITIZE_STRING,
    'date_completed'       => array(
        'data'          => FILTER_SANITIZE_STRING,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_SANITIZE_STRING,
    ),
    'date_paid'            => array(
        'data'          => FILTER_SANITIZE_STRING,
        'timezone_type' => FILTER_VALIDATE_INT,
        'timezone'      => FILTER_SANITIZE_STRING,
    ),
    'items'                => array(
        'filter' => array(FILTER_SANITIZE_STRING),
        'flags'  => FILTER_FORCE_ARRAY,
    ),
);

if (! empty($itemsDataTax)) {
    $args['items_tax'] = array(
        'filter' => array(FILTER_SANITIZE_STRING),
        'flags'  => FILTER_FORCE_ARRAY,
    );
}

if (isset($invoiceNumber) && '' !== $invoiceNumber) {
    $args['invoice_number'] = FILTER_VALIDATE_INT;
}

if (isset($invoiceSent) && '' !== $invoiceSent) {
    $args['invoice_sent'] = FILTER_SANITIZE_STRING;
}

if (! empty($refundedData)) {
    $args['refunded'] = array(
        'filter' => array(FILTER_SANITIZE_STRING),
        'flags'  => FILTER_FORCE_ARRAY,
    );
}

if (! empty($refundedItem)) {
    $args['items_refunded'] = array(
        'filter' => array(FILTER_SANITIZE_STRING),
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
