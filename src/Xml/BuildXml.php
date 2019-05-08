<?php
/**
 * BuildXml.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree\Xml
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

namespace WcElectronInvoiceFree\Xml;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Plugin;

/**
 * Class BuildXml
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class BuildXml extends BuildQuery
{
    /**
     * Xml Data.
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $xmlData = array();

    /**
     * Send Xml
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function send()
    {
        // Get query
        $query = $this->xmlQuery();

        // Return if instance of the query don't in condition.
        if (! $this->typeXmlCondition($query)) {
            return false;
        }

        switch ($query) {
            // All orders
            case $query instanceof \WC_Order_Query:
                try {
                    $this->ordersLoop($query);
                } catch (\Exception $e) {
                    return false;
                };
                break;
            // Single order
            case $query instanceof \WC_Order:
                $this->singleOrder($query);
                break;
            // Single order refund
            case $query instanceof \WC_Order_Refund:
                $this->singleOrderRefund($query);
                break;
            default:
                // No Xml
                break;
        }

        // Get json data.
        $xmlData = $this->getXmlData();

        /**
         * Filter json data
         *
         * @since 1.0.0
         */
        $xmlData = apply_filters('wc_el_inv-xml_data_filter', $xmlData);

        if (empty($xmlData)) {
            wp_safe_redirect(wp_get_referer() . "&found_order=no");
        }
    }

    /**
     * Get Xml Data
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function getXmlData()
    {
        return $this->xmlData;
    }

    /**
     * Set Xml Data
     *
     * @since 1.0.0
     *
     * @param \stdClass $data
     */
    public function setXmlData(\stdClass $data)
    {
        if (! $data instanceof \stdClass) {
            $this->xmlData = array();
        }

        if (! empty($data)) {
            $this->xmlData[] = $data;
        }
    }

    /**
     * Orders Loop
     *
     * @since 1.0.0
     *
     * @param \WC_Order_Query $query
     *
     * @return array
     * @throws \Exception
     */
    private function ordersLoop(\WC_Order_Query $query)
    {
        if (! $query instanceof \WC_Order_Query) {
            return array();
        }

        $orders = $query->get_orders();
        foreach ($orders as $order) {
            if (method_exists($order, 'get_refunds')) {
                $refunded = $order->get_refunds();
                if (! empty($refunded)) {
                    $orders = array_merge($orders, $refunded);
                }
            }
        }

        if (! empty($orders)) {
            foreach ($orders as $order) {
                switch ($order) {
                    // Shop Order
                    case $order instanceof \WC_Order:
                        $this->getDataOrder($order);
                        break;
                    // Order Refunded
                    case $order instanceof \WC_Order_Refund:
                        $this->getDataRefundOrder($order);
                        break;
                    default:
                        break;
                }
            }
        }
    }

    /**
     * Data Order
     *
     * @since 1.0.0
     *
     * @param $order
     *
     * @return array
     */
    public function getDataOrder($order)
    {
        // Customer ID
        $customerID = $order->get_user_id();

        // Initialize Orders data and type.
        $orderType   = $order->get_type();
        $orderData   = $order->get_data();
        $invoiceMeta = array(
            'vat_number'   => $order->get_meta('_billing_vat_number'),
            'tax_code'     => $order->get_meta('_billing_tax_code'),
            'invoice_type' => $order->get_meta('_billing_invoice_type'),
            'sdi_type'     => $order->get_meta('_billing_sdi_type'),
            'choice_type'  => $order->get_meta('_billing_choice_type'),
        );

        $refundedData = array(
            'remaining_amount'        => $order->get_remaining_refund_amount(),
            'remaining_items'         => $order->get_remaining_refund_items(),
            'total_qty_refunded'      => abs($order->get_total_qty_refunded()),
            'total_refunded'          => $order->get_total_refunded(),
            'refunded_payment_method' => $order->get_meta('refund_payment_method'),
        );

        // Initialize Order Items
        $orderItems      = $order->get_items();
        $orderItemsTaxes = $order->get_items('tax');
        $orderItemsShip  = $order->get_items('shipping');
        $itemsDataTax    = array();
        $itemsDataShip   = array();
        $itemsData       = array();
        $refundedItem    = array();

        foreach ($orderItems as $item) {
            if ($item instanceof \WC_Order_Item_Product) {
                $varID   = $item->get_variation_id();
                $id      = isset($varID) && 0 !== $varID ? $varID : $item->get_product_id();
                $product = wc_get_product($id);
                $sku     = null;
                if ($product instanceof \WC_Product) {
                    $sku = $product->get_sku();
                }
                $itemsData[] = array_merge(
                    $item->get_data(),
                    isset($sku) ? array('sku' => $sku) : array()
                );

                if (0 !== $order->get_qty_refunded_for_item($item->get_id())) {
                    $refundedItem[] = array(
                        'product_id'            => $item->get_product_id(),
                        'name'                  => $item->get_name(),
                        'total_price'           => $item->get_total(),
                        'total_tax'             => $item->get_total_tax(),
                        'qty_refunded_for_item' => abs($order->get_qty_refunded_for_item($item->get_id())),
                    );
                }
            }
        }

        foreach ($orderItemsTaxes as $itemID => $itemTax) {
            $itemsDataTax[] = $itemTax->get_data();
            $itemsDataTax   = array_filter($itemsDataTax);
        }

        foreach ($orderItemsShip as $itemID => $itemShip) {
            $itemsDataShip[] = $itemShip->get_data();
            $itemsDataShip   = array_filter($itemsDataShip);
        }

        $filePath = '/inc/ordersJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Data Refund Order
     *
     * @since 1.0.0
     *
     * @param $order
     *
     * @return array
     */
    public function getDataRefundOrder($order)
    {
        $parentOrder = wc_get_order($order->get_parent_id());
        $parentOrder->get_user_id();

        // Customer ID
        $customerID = $order->get_user_id();

        // Initialize Orders data and type.
        $orderType   = $order->get_type();
        $orderData   = $order->get_data();
        $invoiceMeta = array(
            'vat_number'   => $parentOrder->get_meta('_billing_vat_number'),
            'tax_code'     => $parentOrder->get_meta('_billing_tax_code'),
            'invoice_type' => $parentOrder->get_meta('_billing_invoice_type'),
            'sdi_type'     => $parentOrder->get_meta('_billing_sdi_type'),
            'choice_type'  => $parentOrder->get_meta('_billing_choice_type'),
        );
        // Parent billing data.
        $billingParentData = array(
            'first_name' => $parentOrder->get_billing_first_name(),
            'last_name'  => $parentOrder->get_billing_last_name(),
            'company'    => $parentOrder->get_billing_company(),
            'address_1'  => $parentOrder->get_billing_address_1(),
            'address_2'  => $parentOrder->get_billing_address_2(),
            'city'       => $parentOrder->get_billing_city(),
            'state'      => $parentOrder->get_billing_state(),
            'postcode'   => $parentOrder->get_billing_postcode(),
            'country'    => $parentOrder->get_billing_country(),
            'email'      => $parentOrder->get_billing_email(),
            'phone'      => $parentOrder->get_billing_phone(),
        );

        $refundedData = array(
            'remaining_amount'        => $parentOrder->get_remaining_refund_amount(),
            'remaining_items'         => $parentOrder->get_remaining_refund_items(),
            'total_qty_refunded'      => $parentOrder->get_total_qty_refunded(),
            'total_refunded'          => $parentOrder->get_total_refunded(),
            'refunded_payment_method' => $parentOrder->get_meta('refund_payment_method'),
        );

        // Initialize Order Items
        $orderItems           = $parentOrder->get_items();
        $orderItemsTaxes      = $parentOrder->get_items('tax');
        $orderItemsShipping   = $parentOrder->get_items('shipping');
        $itemsRefundedDataTax = array();
        $itemsRefundedData    = array();
        $refundedItem         = array();

        // Current order refund item data
        // Product line
        $refundOrder      = wc_get_order($order->get_id());
        $refundOrderItems = $refundOrder->get_items();
        $currentRefund    = array();
        if (! empty($refundOrderItems)) {
            foreach ($refundOrderItems as $item) {
                $data            = $item->get_data();
                $currentRefund[] = array(
                    'order_id'     => $order->get_parent_id(),
                    'refund_id'    => $data['order_id'],
                    'name'         => $data['name'],
                    'product_id'   => $data['product_id'],
                    'variation_id' => $data['variation_id'],
                    'quantity'     => $data['quantity'],
                    'subtotal'     => $data['subtotal'],
                    'subtotal_tax' => $data['subtotal_tax'],
                    'total'        => $data['total'],
                    'total_tax'    => $data['total_tax'],
                );
            }
        }
        // Shipping
        if (! empty($orderItemsShipping) && false !== strpos($order->get_shipping_total(), '-')) {
            foreach ($orderItemsShipping as $item) {
                $data            = $item->get_data();
                $currentRefund[] = array(
                    'order_id'     => $order->get_parent_id(),
                    'refund_id'    => $order->get_id(),
                    'name'         => $data['name'],
                    'method_title' => $data['method_title'],
                    'method_id'    => $data['method_id'],
                    'instance_id'  => $data['instance_id'],
                    'total'        => $data['total'],
                    'total_tax'    => $data['total_tax'],
                );
            }
        }

        foreach ($orderItems as $item) {
            if ($item instanceof \WC_Order_Item_Product) {
                $varID   = $item->get_variation_id();
                $id      = isset($varID) && 0 !== $varID ? $varID : $item->get_product_id();
                $product = wc_get_product($id);
                $sku     = null;
                if ($product instanceof \WC_Product) {
                    $sku = $product->get_sku();
                }
                $itemsRefundedData[] = array_merge(
                    $item->get_data(),
                    isset($sku) ? array('sku' => $sku) : array()
                );

                if (0 !== $parentOrder->get_qty_refunded_for_item($item->get_id())) {
                    $refundedItem[] = array(
                        'product_id'            => $item->get_product_id(),
                        'name'                  => $item->get_name(),
                        'total_price'           => $item->get_total(),
                        'total_tax'             => $item->get_total_tax(),
                        'qty_refunded_for_item' => abs($parentOrder->get_qty_refunded_for_item($item->get_id())),
                    );
                }
            }
        }

        foreach ($orderItemsTaxes as $itemID => $itemTax) {
            $itemsRefundedDataTax[] = $itemTax->get_data();
            $itemsRefundedDataTax   = array_filter($itemsRefundedDataTax);
        }

        $filePath = '/inc/ordersRefundedJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Single Order
     *
     * @since 1.0.0
     *
     * @param \WC_Order $query
     *
     * @return array
     */
    private function singleOrder(\WC_Order $query)
    {
        if (! $query instanceof \WC_Order) {
            return array();
        }

        // Customer ID
        $customerID = $query->get_user_id();

        // Initialize Orders data and type.
        $orderType   = $query->get_type();
        $orderData   = $query->get_data();
        $invoiceMeta = array(
            'vat_number'   => $query->get_meta('_billing_vat_number'),
            'tax_code'     => $query->get_meta('_billing_tax_code'),
            'invoice_type' => $query->get_meta('_billing_invoice_type'),
            'sdi_type'     => $query->get_meta('_billing_sdi_type'),
            'choice_type'  => $query->get_meta('_billing_choice_type'),
        );

        $refundedData = array(
            'remaining_amount'        => $query->get_remaining_refund_amount(),
            'remaining_items'         => $query->get_remaining_refund_items(),
            'total_qty_refunded'      => $query->get_total_qty_refunded(),
            'total_refunded'          => $query->get_total_refunded(),
            'refunded_payment_method' => $query->get_meta('refund_payment_method'),
        );

        // Initialize Order Items
        $orderItems         = $query->get_items();
        $orderItemsTaxes    = $query->get_items('tax');
        $orderItemsShipping = $query->get_items('shipping');
        $itemsDataTax       = array();
        $itemsDataShip      = array();
        $itemsData          = array();
        $refundedItem       = array();

        foreach ($orderItems as $item) {
            if ($item instanceof \WC_Order_Item_Product) {
                $varID   = $item->get_variation_id();
                $id      = isset($varID) && 0 !== $varID ? $varID : $item->get_product_id();
                $product = wc_get_product($id);
                $sku     = null;
                if ($product instanceof \WC_Product) {
                    $sku = $product->get_sku();
                }
                $itemsData[] = array_merge(
                    $item->get_data(),
                    isset($sku) ? array('sku' => $sku) : array()
                );

                if (0 !== $query->get_qty_refunded_for_item($item->get_id())) {
                    $refundedItem[] = array(
                        'product_id'            => $item->get_product_id(),
                        'name'                  => $item->get_name(),
                        'total_price'           => $item->get_total(),
                        'total_tax'             => $item->get_total_tax(),
                        'qty_refunded_for_item' => $query->get_qty_refunded_for_item($item->get_id()),
                    );
                }
            }
        }

        foreach ($orderItemsTaxes as $itemID => $itemTax) {
            $itemsDataTax[] = $itemTax->get_data();
            $itemsDataTax   = array_filter($itemsDataTax);
        }

        foreach ($orderItemsShipping as $itemID => $itemShip) {
            $itemsDataShip[] = $itemShip->get_data();
            $itemsDataShip   = array_filter($itemsDataShip);
        }

        $filePath = '/inc/ordersJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Single Order
     *
     * @since 1.0.0
     *
     * @param \WC_Order_Refund $query
     *
     * @return array
     */
    private function singleOrderRefund(\WC_Order_Refund $query)
    {
        if (! $query instanceof \WC_Order_Refund) {
            return array();
        }

        $parentOrder = wc_get_order($query->get_parent_id());
        $parentOrder->get_user_id();

        // Customer ID
        $customerID = $query->get_user_id();

        // Initialize Orders data and type.
        $orderType   = $query->get_type();
        $orderData   = $query->get_data();
        $invoiceMeta = array(
            'vat_number'   => $parentOrder->get_meta('_billing_vat_number'),
            'tax_code'     => $parentOrder->get_meta('_billing_tax_code'),
            'invoice_type' => $parentOrder->get_meta('_billing_invoice_type'),
            'sdi_type'     => $parentOrder->get_meta('_billing_sdi_type'),
            'choice_type'  => $parentOrder->get_meta('_billing_choice_type'),
        );
        // Parent billing data.
        $billingParentData = array(
            'first_name' => $parentOrder->get_billing_first_name(),
            'last_name'  => $parentOrder->get_billing_last_name(),
            'company'    => $parentOrder->get_billing_company(),
            'address_1'  => $parentOrder->get_billing_address_1(),
            'address_2'  => $parentOrder->get_billing_address_2(),
            'city'       => $parentOrder->get_billing_city(),
            'state'      => $parentOrder->get_billing_state(),
            'postcode'   => $parentOrder->get_billing_postcode(),
            'country'    => $parentOrder->get_billing_country(),
            'email'      => $parentOrder->get_billing_email(),
            'phone'      => $parentOrder->get_billing_phone(),
        );

        $refundedData = array(
            'remaining_amount'        => $parentOrder->get_remaining_refund_amount(),
            'remaining_items'         => $parentOrder->get_remaining_refund_items(),
            'total_qty_refunded'      => abs($parentOrder->get_total_qty_refunded()),
            'total_refunded'          => $parentOrder->get_total_refunded(),
            'refunded_payment_method' => $parentOrder->get_meta('refund_payment_method'),
        );

        // Initialize Order Items
        $orderItems           = $parentOrder->get_items();
        $orderItemsTaxes      = $parentOrder->get_items('tax');
        $orderItemsShipping   = $parentOrder->get_items('shipping');
        $itemsRefundedDataTax = array();
        $itemsRefundedData    = array();
        $refundedItem         = array();

        // Current order refund item data
        // Product line
        $refundOrder      = wc_get_order($query->get_id());
        $refundOrderItems = $refundOrder->get_items();
        $currentRefund    = array();
        if (! empty($refundOrderItems)) {
            foreach ($refundOrderItems as $item) {
                $data            = $item->get_data();
                $currentRefund[] = array(
                    'order_id'     => $query->get_parent_id(),
                    'refund_id'    => $data['order_id'],
                    'name'         => $data['name'],
                    'product_id'   => $data['product_id'],
                    'variation_id' => $data['variation_id'],
                    'quantity'     => $data['quantity'],
                    'subtotal'     => $data['subtotal'],
                    'subtotal_tax' => $data['subtotal_tax'],
                    'total'        => $data['total'],
                    'total_tax'    => $data['total_tax'],
                );
            }
        }
        // Shipping
        if (! empty($orderItemsShipping) && false !== strpos($query->get_shipping_total(), '-')) {
            foreach ($orderItemsShipping as $item) {
                $data            = $item->get_data();
                $currentRefund[] = array(
                    'order_id'     => $query->get_parent_id(),
                    'refund_id'    => $query->get_id(),
                    'name'         => $data['name'],
                    'method_title' => $data['method_title'],
                    'method_id'    => $data['method_id'],
                    'instance_id'  => $data['instance_id'],
                    'total'        => $data['total'],
                    'total_tax'    => $data['total_tax'],
                );
            }
        }

        foreach ($orderItems as $item) {
            if ($item instanceof \WC_Order_Item_Product) {
                $varID   = $item->get_variation_id();
                $id      = isset($varID) && 0 !== $varID ? $varID : $item->get_product_id();
                $product = wc_get_product($id);
                $sku     = null;
                if ($product instanceof \WC_Product) {
                    $sku = $product->get_sku();
                }
                $itemsRefundedData[] = array_merge(
                    $item->get_data(),
                    isset($sku) ? array('sku' => $sku) : array()
                );

                if (0 !== $parentOrder->get_qty_refunded_for_item($item->get_id())) {
                    $refundedItem[] = array(
                        'product_id'            => $item->get_product_id(),
                        'name'                  => $item->get_name(),
                        'total_price'           => $item->get_total(),
                        'total_tax'             => $item->get_total_tax(),
                        'qty_refunded_for_item' => $parentOrder->get_qty_refunded_for_item($item->get_id()),
                    );
                }
            }
        }

        foreach ($orderItemsTaxes as $itemID => $itemTax) {
            $itemsRefundedDataTax[] = $itemTax->get_data();
            $itemsRefundedDataTax   = array_filter($itemsRefundedDataTax);
        }

        $filePath = '/inc/ordersRefundedJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Global condition
     *
     * @since 1.0.0
     *
     * @param $query
     *
     * @return bool
     */
    private function typeXmlCondition($query)
    {
        return $query instanceof \WC_Order ||
               $query instanceof \WC_Order_Refund ||
               $query instanceof \WC_Order_Query;
    }
}
