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

use WcElectronInvoiceFree\Cache\CacheTransient;
use WcElectronInvoiceFree\Plugin;
use WcElectronInvoiceFree\Utils\TimeZone;

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
     * @return bool
     * @since 1.0.0
     *
     */
    public function send()
    {
        $format = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'format', FILTER_UNSAFE_RAW);

        // Get query
        $query = $this->xmlQuery();

        // Return if instance of the query don't in condition.
        if (! $this->typeXmlCondition($query)) {
            return false;
        }

        $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');

        switch ($query) {
            // Single order
            case $query instanceof $wcOrderClass:
                $this->singleOrder($query, $wcOrderClass);
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

        // Initialized
        $elements = count($xmlData);

        // Zip invoices xml archive
        if (! empty($xmlData) && 1 === $elements) {
            // Single invoice xml
            $this->invoiceXml($xmlData);
        } elseif (empty($xmlData)) {
            wp_safe_redirect(wp_get_referer() . "&found_order=no");
        }
    }

    /**
     * Create invoice XML
     *
     * @param $xmlData
     *
     * @since 1.1.0
     *
     */
    private function invoiceXml($xmlData)
    {
        $getFormat = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'format', FILTER_UNSAFE_RAW);
        $getItem   = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'item', FILTER_UNSAFE_RAW) ?: null;
        $getNonce  = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'nonce', FILTER_UNSAFE_RAW);

        if (! is_user_logged_in() &&
            ! current_user_can('manage_options') &&
            ! current_user_can('manage_network')
        ) {
            return;
        }

        if ('xml' === $getFormat &&
            false === wp_verify_nonce($getNonce, 'wc_el_inv_invoice_xml')
        ) {
            wp_send_json(esc_html__('Validation ERROR', WC_EL_INV_FREE_TEXTDOMAIN), 400);
            die();
        }

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $itemDateTime = new \DateTime($xmlData[0]->date_created);
            $itemDateTime->setTimezone($timeZone);
        } catch (\Exception $e) {
            $itemDateTime = null;
        }

        // Get 10 order IDs after date
        $ordersIds = wc_get_orders(array(
            'status'       => array('processing', 'completed', 'refunded'),
            'limit'        => 6,
            'orderby'      => 'date',
            'order'        => 'ASC',
            'date_created' => '>' . strtotime($xmlData[0]->date_created),
            'return'       => 'ids',
        ));

        // Item number
        $item  = base64_decode($getItem);
        $itemN = explode('__', $item);
        $itemN = (int)$itemN[1];

        // Date condition
        $dateCondition = ($itemDateTime instanceof \DateTime && $itemDateTime->format('Ym') !== date('Ym', time()));

        if('xml' === $getFormat && ! $itemN || $itemN > 5 || count($ordersIds) >= 5 || ($dateCondition)) {
            print_r(
                esc_html__(
                    'You cannot generate this invoice, you have exceeded the limit of 5', WC_EL_INV_FREE_TEXTDOMAIN)
            );
            die();
        }

        try {
            // Initialized
            $xmlLoop = array();
            // Set xml index
            $xmlLoop[] = $xmlData[0];

            $data = new CreateXml(new \SimpleXMLElement(
                    '<?xml version="1.0" encoding="UTF-8"?><xmlns:p:FatturaElettronica/>',
                    LIBXML_NOERROR,
                    false,
                    'p',
                    true)
            );
            $data->create($xmlLoop);
            exit();
        } catch (\Exception $e) {
            print_r(
                esc_html__(
                    'Error no create invoice XML: ', WC_EL_INV_FREE_TEXTDOMAIN) . $e->getMessage()
            );
            die();
        }
    }

    /**
     * Get Xml Data
     *
     * @return array
     * @since 1.0.0
     *
     */
    public function getXmlData()
    {
        return $this->xmlData;
    }

    /**
     * Set Xml Data
     *
     * @param \stdClass $data
     * @param string    $type
     *
     * @since 1.0.0
     *
     */
    public function setXmlData(\stdClass $data, $type = '')
    {
        if (! $data instanceof \stdClass) {
            $this->xmlData = array();
        }

        if (! empty($data)) {
            $this->xmlData[] = $data;
        }

        if ('' !== $type) {
            $cacher      = new CacheTransient();
            $currentData = $cacher->get($type);
            $data        = is_array($currentData) ? array_merge($this->xmlData, $currentData) : $this->xmlData;
            $json        = maybe_serialize($data);
            $cacher->set($json, $type);
        }
    }

    /**
     * Orders Loop
     *
     * @param \WC_Order_Query $query
     *
     * @return array
     * @throws \Exception
     * @since 1.0.0
     *
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
                $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
                $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');
                switch ($order) {
                    // Shop Order
                    case $order instanceof $wcOrderClass:
                        $this->getDataOrder($order);
                        break;
                    // Order Refunded
                    case $order instanceof $wcOrderRefundClass:
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
     * @param $order
     *
     * @return array
     * @since 1.0.0
     *
     */
    public function getDataOrder($order)
    {
        // Customer ID
        $customerID = $order->get_user_id();
        \WcElectronInvoiceFree\Functions\setCustomerLocation($customerID);

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
        $orderItemsFee   = $order->get_items('fee');
        $itemsDataTax    = array();
        $itemsDataShip   = array();
        $itemsDataFee    = array();
        $itemsData       = array();
        $refundedItem    = array();

        if (! empty($orderItems)) {
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
        }

        // Tax
        if (! empty($orderItemsTaxes)) {
            foreach ($orderItemsTaxes as $itemID => $itemTax) {
                $itemsDataTax[] = $itemTax->get_data();
                $itemsDataTax   = array_filter($itemsDataTax);
            }
        }

        // Shipping
        if (! empty($orderItemsShip)) {
            foreach ($orderItemsShip as $itemID => $itemShip) {
                $dataShip   = $itemShip->get_data();
                $refundShip = $refundShipTax = 0;
                foreach ($dataShip as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($itemShip->get_tax_class());
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id            = array_keys($taxRates);
                            $refundShip    = $order->get_total_refunded_for_item($data, 'shipping');
                            $refundShipTax = $order->get_tax_refunded_for_item($data, $id[0], 'shipping');
                        }
                    }
                }

                $itemsDataShip[] = $dataShip;
                if (0 !== $refundShip || 0 !== $refundShipTax) {
                    $itemsDataShip[]['refund_shipping'] = array(
                        'total'     => $refundShip,
                        'total_tax' => $refundShipTax,
                    );
                }
                $itemsDataShip = array_filter($itemsDataShip);
            }
        }

        // Fee
        if (! empty($orderItemsFee)) {
            foreach ($orderItemsFee as $itemID => $itemFee) {
                $dataFee   = $itemFee->get_data();
                $refundFee = $refundFeeTax = 0;
                foreach ($dataFee as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id           = array_keys($taxRates);
                            $refundFee    = $order->get_total_refunded_for_item($data, 'fee');
                            $refundFeeTax = $order->get_tax_refunded_for_item($data, $id[0], 'fee');
                        }
                    }
                }
                $itemsDataFee[] = $dataFee;
                if (0 !== $refundFee || 0 !== $refundFeeTax) {
                    $itemsDataFee[]['refund_fee'] = array(
                        'total'     => $refundFee,
                        'total_tax' => $refundFeeTax,
                    );
                }
                $itemsDataFee = array_filter($itemsDataFee);
            }
        }

        $filePath = '/inc/ordersJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Data Refund Order
     *
     * @param $order
     *
     * @return array
     * @since 1.0.0
     *
     */
    public function getDataRefundOrder($order)
    {
        $parentOrder = wc_get_order($order->get_parent_id());
        $parentOrder->get_user_id();

        // Customer ID
        $customerID = $order->get_user_id();
        \WcElectronInvoiceFree\Functions\setCustomerLocation($customerID);

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
        $orderItems                = $parentOrder->get_items();
        $orderItemsTaxes           = $parentOrder->get_items('tax');
        $orderItemsShipping        = $parentOrder->get_items('shipping');
        $orderItemsFee             = $parentOrder->get_items('fee');
        $itemsRefundedDataTax      = array();
        $itemsRefundedDataFee      = array();
        $itemsRefundedDataShipping = array();
        $itemsRefundedData         = array();
        $refundedItem              = array();

        // Current order refund item data
        // Product line
        $refundOrder      = wc_get_order($order->get_id());
        $refundOrderItems = $refundOrder->get_items();
        // Items refunded
        $refundItemsShipping = $refundOrder->get_items('shipping');
        $refundItemsFee      = $refundOrder->get_items('fee');
        $currentRefund       = array();
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
        // Refund Shipping
        if (! empty($refundItemsShipping) && false !== strpos($order->get_shipping_total(), '-')) {
            foreach ($orderItemsShipping as $item) {
                $data            = $item->get_data();
                $currentRefund[] = array(
                    'order_id'     => $order->get_parent_id(),
                    'refund_id'    => $order->get_id(),
                    'refund_type'  => 'shipping',
                    'name'         => $data['name'],
                    'method_title' => $data['method_title'],
                    'method_id'    => $data['method_id'],
                    'instance_id'  => $data['instance_id'],
                    'total'        => $data['total'],
                    'total_tax'    => $data['total_tax'],
                );
            }
        }

        // Refund Fee
        if (! empty($refundItemsFee)) {
            foreach ($orderItemsFee as $itemID => $itemFee) {
                $dataFee   = $itemFee->get_data();
                $refundFee = $refundFeeTax = $rate = 0;
                foreach ($dataFee as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $taxRate      = reset($taxRates);
                            $rate         = $taxRate['rate'];
                            $id           = array_keys($taxRates);
                            $refundFee    = $parentOrder->get_total_refunded_for_item($data, 'fee');
                            $refundFeeTax = $parentOrder->get_tax_refunded_for_item($data, $id[0], 'fee');
                        }
                    }
                }
                $currentRefund[] = array(
                    'order_id'    => $order->get_parent_id(),
                    'refund_id'   => $order->get_id(),
                    'refund_type' => 'fee',
                    'name'        => trim($dataFee['name']),
                    'tax_rate'    => $rate,
                    'total'       => $refundFee,
                    'total_tax'   => $refundFeeTax,
                );
            }
        }

        // Orders
        if (! empty($orderItems)) {
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
        }

        // Tax
        if (! empty($orderItemsTaxes)) {
            foreach ($orderItemsTaxes as $itemID => $itemTax) {
                $itemsRefundedDataTax[] = $itemTax->get_data();
                $itemsRefundedDataTax   = array_filter($itemsRefundedDataTax);
            }
        }

        // Shipping
        if (! empty($orderItemsShipping)) {
            foreach ($orderItemsShipping as $itemID => $itemShip) {
                $dataShip   = $itemShip->get_data();
                $refundShip = $refundShipTax = 0;
                foreach ($dataShip as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($itemShip->get_tax_class());
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id            = array_keys($taxRates);
                            $refundShip    = $parentOrder->get_total_refunded_for_item($data, 'shipping');
                            $refundShipTax = $parentOrder->get_tax_refunded_for_item($data, $id[0], 'shipping');
                        }
                    }
                }
                $itemsRefundedDataShipping[] = $dataShip;
                if (0 !== $refundShip || 0 !== $refundShipTax) {
                    $itemsRefundedDataShipping[]['refund_shipping'] = array(
                        'total'     => $refundShip,
                        'total_tax' => $refundShipTax,
                    );
                }
                $itemsRefundedDataShipping = array_filter($itemsRefundedDataShipping);
            }
        }

        // Fee
        if (! empty($orderItemsFee)) {
            foreach ($orderItemsFee as $itemID => $itemFee) {
                $dataFee   = $itemFee->get_data();
                $refundFee = $refundFeeTax = 0;
                foreach ($dataFee as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id           = array_keys($taxRates);
                            $refundFee    = $parentOrder->get_total_refunded_for_item($data, 'fee');
                            $refundFeeTax = $parentOrder->get_tax_refunded_for_item($data, $id[0], 'fee');
                        }
                    }
                }
                $itemsRefundedDataFee[] = $dataFee;
                if (0 !== $refundFee || 0 !== $refundFeeTax) {
                    $itemsRefundedDataFee[]['refund_fee'] = array(
                        'total'     => $refundFee,
                        'total_tax' => $refundFeeTax,
                    );
                }
                $itemsRefundedDataFee = array_filter($itemsRefundedDataFee);
            }
        }

        $filePath = '/inc/ordersRefundedJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Single Order
     *
     * @param $query
     * @param $wcOrderClass
     *
     * @return array
     * @since 1.0.0
     *
     */
    private function singleOrder($query, $wcOrderClass)
    {
        if (! $query instanceof $wcOrderClass) {
            return array();
        }

        // Customer ID
        $customerID = $query->get_user_id();
        \WcElectronInvoiceFree\Functions\setCustomerLocation($customerID);

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
        $orderItemsFee      = $query->get_items('fee');
        $itemsDataTax       = array();
        $itemsDataShip      = array();
        $itemsDataFee       = array();
        $itemsData          = array();
        $refundedItem       = array();

        if (! empty($orderItems)) {
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
        }

        // Tax
        if (! empty($orderItemsTaxes)) {
            foreach ($orderItemsTaxes as $itemID => $itemTax) {
                $itemsDataTax[] = $itemTax->get_data();
                $itemsDataTax   = array_filter($itemsDataTax);
            }
        }

        // Shipping
        if (! empty($orderItemsShipping)) {
            foreach ($orderItemsShipping as $itemID => $itemShip) {
                $dataShip   = $itemShip->get_data();
                $refundShip = $refundShipTax = 0;
                foreach ($dataShip as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($itemShip->get_tax_class());
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id            = array_keys($taxRates);
                            $refundShip    = $query->get_total_refunded_for_item($data, 'shipping');
                            $refundShipTax = $query->get_tax_refunded_for_item($data, $id[0], 'shipping');
                        }
                    }
                }

                $itemsDataShip[] = $dataShip;
                if (0 !== $refundShip || 0 !== $refundShipTax) {
                    $itemsDataShip[]['refund_shipping'] = array(
                        'total'     => $refundShip,
                        'total_tax' => $refundShipTax,
                    );
                }
                $itemsDataShip = array_filter($itemsDataShip);
            }
        }

        // Fee
        if (! empty($orderItemsFee)) {
            foreach ($orderItemsFee as $itemID => $itemFee) {
                $dataFee   = $itemFee->get_data();
                $refundFee = $refundFeeTax = 0;
                foreach ($dataFee as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id           = array_keys($taxRates);
                            $refundFee    = $query->get_total_refunded_for_item($data, 'fee');
                            $refundFeeTax = $query->get_tax_refunded_for_item($data, $id[0], 'fee');
                        }
                    }
                }
                $itemsDataFee[] = $dataFee;
                if (0 !== $refundFee || 0 !== $refundFeeTax) {
                    $itemsDataFee[]['refund_fee'] = array(
                        'total'     => $refundFee,
                        'total_tax' => $refundFeeTax,
                    );
                }
                $itemsDataFee = array_filter($itemsDataFee);
            }
        }

        $filePath = '/inc/ordersJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Single Order
     *
     * @param $query
     * @param $wcOrderRefundClass
     *
     * @return array
     * @since 1.0.0
     *
     */
    private function singleOrderRefund($query, $wcOrderRefundClass)
    {
        if (! $query instanceof $wcOrderRefundClass) {
            return array();
        }

        $parentOrder = wc_get_order($query->get_parent_id());
        $parentOrder->get_user_id();

        // Customer ID
        $customerID = $query->get_user_id();
        \WcElectronInvoiceFree\Functions\setCustomerLocation($customerID);

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
        $orderItems                = $parentOrder->get_items();
        $orderItemsTaxes           = $parentOrder->get_items('tax');
        $orderItemsShipping        = $parentOrder->get_items('shipping');
        $orderItemsFee             = $parentOrder->get_items('fee');
        $itemsRefundedDataTax      = array();
        $itemsRefundedDataFee      = array();
        $itemsRefundedDataShipping = array();
        $itemsRefundedData         = array();
        $refundedItem              = array();

        // Current order refund item data
        // Product line
        $refundOrder      = wc_get_order($query->get_id());
        $refundOrderItems = $refundOrder->get_items();
        // Items refunded
        $refundItemsShipping = $refundOrder->get_items('shipping');
        $refundItemsFee      = $refundOrder->get_items('fee');
        $currentRefund       = array();
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
        if (! empty($refundItemsShipping) && false !== strpos($query->get_shipping_total(), '-')) {
            foreach ($orderItemsShipping as $item) {
                $data            = $item->get_data();
                $currentRefund[] = array(
                    'order_id'     => $query->get_parent_id(),
                    'refund_id'    => $query->get_id(),
                    'refund_type'  => 'shipping',
                    'name'         => $data['name'],
                    'method_title' => $data['method_title'],
                    'method_id'    => $data['method_id'],
                    'instance_id'  => $data['instance_id'],
                    'total'        => $data['total'],
                    'total_tax'    => $data['total_tax'],
                );
            }
        }
        // Fee
        if (! empty($refundItemsFee)) {
            foreach ($orderItemsFee as $itemID => $itemFee) {
                $dataFee   = $itemFee->get_data();
                $refundFee = $refundFeeTax = $rate = 0;
                foreach ($dataFee as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $taxRate      = reset($taxRates);
                            $rate         = $taxRate['rate'];
                            $id           = array_keys($taxRates);
                            $refundFee    = $parentOrder->get_total_refunded_for_item($data, 'fee');
                            $refundFeeTax = $parentOrder->get_tax_refunded_for_item($data, $id[0], 'fee');
                        }
                    }
                }
                $currentRefund[] = array(
                    'order_id'    => $query->get_parent_id(),
                    'refund_id'   => $query->get_id(),
                    'refund_type' => 'fee',
                    'name'        => trim($dataFee['name']),
                    'tax_rate'    => $rate,
                    'total'       => $refundFee,
                    'total_tax'   => $refundFeeTax,
                );
            }
        }

        if (! empty($orderItems)) {
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
        }

        // Tax
        if (! empty($orderItemsTaxes)) {
            foreach ($orderItemsTaxes as $itemID => $itemTax) {
                $itemsRefundedDataTax[] = $itemTax->get_data();
                $itemsRefundedDataTax   = array_filter($itemsRefundedDataTax);
            }
        }

        // Shipping
        if (! empty($orderItemsShipping)) {
            foreach ($orderItemsShipping as $itemID => $itemShip) {
                $dataShip   = $itemShip->get_data();
                $refundShip = $refundShipTax = 0;
                foreach ($dataShip as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($itemShip->get_tax_class());
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id            = array_keys($taxRates);
                            $refundShip    = $parentOrder->get_total_refunded_for_item($data, 'shipping');
                            $refundShipTax = $parentOrder->get_tax_refunded_for_item($data, $id[0], 'shipping');
                        }
                    }
                }
                $itemsRefundedDataShipping[] = $dataShip;
                if (0 !== $refundShip || 0 !== $refundShipTax) {
                    $itemsRefundedDataShipping[]['refund_shipping'] = array(
                        'total'     => $refundShip,
                        'total_tax' => $refundShipTax,
                    );
                }
                $itemsRefundedDataShipping = array_filter($itemsRefundedDataShipping);
            }
        }

        // Fee
        if (! empty($orderItemsFee)) {
            foreach ($orderItemsFee as $itemID => $itemFee) {
                $dataFee   = $itemFee->get_data();
                $refundFee = $refundFeeTax = 0;
                foreach ($dataFee as $key => $data) {
                    if ('id' === $key) {
                        $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        }
                        if (empty($taxRates)) {
                            $taxRates = array(0);
                        }

                        if (! empty($taxRates)) {
                            $id           = array_keys($taxRates);
                            $refundFee    = $parentOrder->get_total_refunded_for_item($data, 'fee');
                            $refundFeeTax = $parentOrder->get_tax_refunded_for_item($data, $id[0], 'fee');
                        }
                    }
                }
                $itemsRefundedDataFee[] = $dataFee;
                if (0 !== $refundFee || 0 !== $refundFeeTax) {
                    $itemsRefundedDataFee[]['refund_fee'] = array(
                        'total'     => $refundFee,
                        'total_tax' => $refundFeeTax,
                    );
                }
                $itemsRefundedDataFee = array_filter($itemsRefundedDataFee);
            }
        }

        $filePath = '/inc/ordersRefundedJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        $this->setXmlData($data);
    }

    /**
     * Global condition
     *
     * @param $query
     *
     * @return bool
     * @since 1.0.0
     *
     */
    private function typeXmlCondition($query)
    {
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');

        return $query instanceof \WC_Order_Query ||
               $query instanceof $wcOrderClass ||
               $query instanceof $wcOrderRefundClass;
    }
}
