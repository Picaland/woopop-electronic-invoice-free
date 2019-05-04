<?php
/**
 * XmlOrderListTable.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree\Admin
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

namespace WcElectronInvoiceFree\Admin;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Admin\Settings\OptionPage;
use WcElectronInvoiceFree\EndPoint\Endpoints;
use WcElectronInvoiceFree\Plugin;
use WcElectronInvoiceFree\Utils\TimeZone;
use WcElectronInvoiceFree\WooCommerce\Fields\InvoiceFields;

/**
 * Class XmlOrderListTable
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class XmlOrderListTable extends \WP_List_Table
{
    /**
     * List type
     *
     * @since 1.0.0
     */
    const LIST_TYPE = 'shop_order';

    /**
     * Order Data
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $data = array();

    /**
     * IDS list
     *
     * @var array
     */
    public static $listIds = array();

    /**
     * XmlOrderListTable constructor.
     *
     * @since 1.0.0
     *
     * @param array $args
     */
    public function __construct($args = array())
    {
        parent::__construct(array(
            'singular' => 'woopop-invoice',
            'plural'   => 'woopop-invoices',
            'ajax'     => false,
        ));

        $this->data = $this->convertObjInArray($this->getOrders());
    }

    /**
     * Convert Object in Array
     *
     * @since 1.0.0
     *
     * @param $dataObj
     *
     * @return array
     */
    private function convertObjInArray($dataObj)
    {
        $dataArray = array();

        if (! empty($dataObj)) {
            foreach ($dataObj as $data) {
                $dataArray[] = get_object_vars($data);
            }
        }

        return $dataArray;
    }

    /**
     * Get Actions
     *
     * @since 1.0.0
     *
     * @param $id
     * @param $item
     *
     * @return string
     */
    private function actions($id, $item)
    {
        $output = '';
        $nonce   = wp_create_nonce('wc_el_inv_invoice_pdf');
        $pdfArgs = "?format=pdf&nonce={$nonce}";
        $url = site_url() . '/' . Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/';

        $output .= sprintf(
            '<a id="mark_as_sent-%1$s" class="mark_trigger disabled mark_as_sent button button-secondary" href="javascript:;" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-yes"></span></a>',
            esc_html__('Mark as Sent', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        // XML structure
        $output .= sprintf(
            '<a class="button button-secondary disabled" href="javascript:;" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-editor-ul"></span></a>',
            esc_html__('Get Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        // XML view whit style
        $output .= sprintf(
            '<a class="button button-primary disabled" href="javascript:;" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-visibility"></span></a>',
            esc_html__('View Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        // XML download
        $output .= sprintf(
            '<a class="button button-secondary button-save disabled" href="javascript:;" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-media-code"></span></a>',
            esc_html__('Save Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        // PDF view
        $output .= sprintf(
            '<a class="button button-secondary button-pdf" %1$s href="%2$s%3$s" title="%4$s">' .
            '<span class="dashicons dashicons-media-text"></span></a>',
            'target="_blank"',
            esc_url($url . $id),
            $pdfArgs,
            esc_html__('View PDF', WC_EL_INV_FREE_TEXTDOMAIN)
        );

        /**
         * Filter actions out put
         *
         * @since 1.0.0
         */
        $output = apply_filters('wc_el_inv-xml_order_list_table_actions', $output);

        return $output;
    }

    /**
     * Get Order Type string
     *
     * @since 1.0.0
     *
     * @param $item
     *
     * @return string
     */
    private function orderCustomer($item)
    {
        $customerLink = get_edit_user_link($item['customer_id']);
        $edit         = esc_html__('Edit customer', WC_EL_INV_FREE_TEXTDOMAIN);

        $name     = isset($item['billing']['first_name']) ? $item['billing']['first_name'] : '';
        $lastName = isset($item['billing']['last_name']) ? $item['billing']['last_name'] : '';
        $company  = isset($item['billing']['company']) ? $item['billing']['company'] : '';

        $fullName = isset($name) ? $name . ' ' . $lastName : $company;

        return sprintf('<strong>%s</strong>%s',
            ucfirst($fullName),
            isset($item['customer_id']) && 0 !== $item['customer_id'] ?
                "<br><small class='edit'><a href='{$customerLink}' title='{$edit}'>{$edit}</a></small>" : ''
        );
    }

    /**
     * Get Order Title
     *
     * @since 1.0.0
     *
     * @param $item
     *
     * @return string
     */
    private function orderTitle($item)
    {
        if (! isset($item['id'])) {
            return '';
        }

        switch ($item['order_type']) {
            case 'shop_order':
                $type = esc_html__('Order', WC_EL_INV_FREE_TEXTDOMAIN);
                break;
            case 'shop_order_refund':
                $type = esc_html__('Refund', WC_EL_INV_FREE_TEXTDOMAIN);
                break;
            default:
                $type = '';
                break;
        }

        try {
            $date = isset($item['date_created']) && '' !== $item['date_created'] ?
                $item['date_created'] : $item['date_completed'];

            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime($date);
            $date->setTimezone($timeZone);

            $dateTime = $date->format('Y-m-d H:i');

            $editOrderLink = esc_url(get_edit_post_link($item['id']));
            $edit          = esc_html__('Edit order', WC_EL_INV_FREE_TEXTDOMAIN);

            // Refund edit link
            $order = wc_get_order($item['id']);
            if ('shop_order_refund' === $item['order_type']) {
                $editOrderLink = esc_url(get_edit_post_link($order->get_parent_id()) . '#order_refunds');
                $edit          = esc_html__('Edit refund', WC_EL_INV_FREE_TEXTDOMAIN);
            }

            switch ($order->get_status()) {
                case 'processing':
                    $status = esc_html__('Processing', WC_EL_INV_FREE_TEXTDOMAIN);
                    $color  = 'style="color:green"';
                    break;
                case 'completed':
                    $status = esc_html__('Completed', WC_EL_INV_FREE_TEXTDOMAIN);
                    $color  = 'style="color:dodgerblue"';
                    break;
                case 'refunded':
                    $status = esc_html__('Refunded', WC_EL_INV_FREE_TEXTDOMAIN);
                    $color  = 'style="color:red"';
                    break;
                default:
                    $status = $order->get_status();
                    $color  = '';
                    break;
            }

            return sprintf('<strong>%s</strong> - %s %s %s <br><strong>%s </strong><span %s>%s</span> %s',
                esc_html("#" . $item['id']),
                "{$type}",
                esc_html__('of', WC_EL_INV_FREE_TEXTDOMAIN),
                "{$dateTime}",
                sprintf('%s:', $type),
                $color,
                $status,
                "<br><small class='edit'><a href='{$editOrderLink}' title='{$edit}'>{$edit}</a></small>"
            );
        } catch (\Exception $e) {
            echo esc_html__('Order title DateTime error: ', WC_EL_INV_FREE_TEXTDOMAIN) . $e->getMessage();
        }
    }

    /**
     * Order Total
     *
     * @since 1.0.0
     *
     * @param $item
     *
     * @return string
     */
    private function orderTotal($item)
    {
        if (! isset($item['total'])) {
            return esc_html__('Error: no total order');
        }

        if (! empty($item['refunded']) && 'shop_order_refund' === $item['order_type']) {
            $total = sprintf('<strong>-%s %s</strong>',
                $this->numberFormat(abs($item['refunded']['total_refunded'])),
                get_woocommerce_currency_symbol($item['currency'])
            );
        } elseif (! empty($item['refunded']) && 'shop_order' === $item['order_type']) {

            $total = sprintf('<strong>%s %s</strong>',
                $this->numberFormat(abs($item['refunded']['remaining_amount'])),
                get_woocommerce_currency_symbol($item['currency'])
            );

            if (abs($item['refunded']['total_refunded']) === abs($item['total'])) {
                $total = sprintf('<strong>%s %s</strong>',
                    $this->numberFormat(abs($item['total'])),
                    get_woocommerce_currency_symbol($item['currency'])
                );
            }
        } else {
            $total = sprintf('<strong>%s %s</strong>',
                $this->numberFormat(abs($item['total'])),
                get_woocommerce_currency_symbol($item['currency'])
            );
        }

        return $total;
    }

    /**
     * Number Format
     *
     * @since 1.0.0
     *
     * @param int $number
     *
     * @return string
     */
    private function numberFormat($number = 0)
    {
        return number_format(abs($number), 2, '.', '');
    }

    /**
     * Invoice Number
     *
     * @since 1.0.0
     *
     * @param $number
     *
     * @return string
     */
    private function invoiceNumber($number)
    {
        $options = OptionPage::init();

        // Number of digits
        $digits = $options->getOptions('number_digits_in_invoice');
        $digits = isset($digits) && '' !== $digits ? $digits : 2;
        // Prefix
        $prefix = $options->getOptions('prefix_invoice_number');
        $prefix = isset($prefix) && '' !== $prefix ? $prefix : 'inv';
        // Suffix
        $suffix = $options->getOptions('suffix_invoice_number');
        $suffix = isset($suffix) && '' !== $suffix ? $suffix : '';
        // Invoice number
        $invNumber = str_pad($number, $digits, '0', STR_PAD_LEFT);

        return isset($number) && 0 !== $number && '' !== $number ? "{$prefix}-{$invNumber}{$suffix}" : '';
    }

    /**
     * Customer Type
     *
     * @since 1.0.0
     *
     * @param $item
     *
     * @return string
     */
    private function customerType($item)
    {
        if (! isset($item['invoice_type'])) {
            return '';
        }

        // No UE
        if (! in_array($item['billing']['country'], InvoiceFields::$euVatCountry, true)) {
            return esc_html__('* No UE customer *', WC_EL_INV_FREE_TEXTDOMAIN);
        }

        switch ($item['invoice_type']) {
            case 'company':
                $type = esc_html__('Company', WC_EL_INV_FREE_TEXTDOMAIN);
                break;
            case 'freelance':
                $type = esc_html__('Freelance', WC_EL_INV_FREE_TEXTDOMAIN);
                break;
            case 'private':
                $type = esc_html__('Private', WC_EL_INV_FREE_TEXTDOMAIN);
                break;
            default:
                $type = esc_html__('(*) No data, set the data from the user profile before generating the xml invoice',
                    WC_EL_INV_FREE_TEXTDOMAIN);;
                break;
        }

        return $type;
    }

    /**
     * Custom VAT or SDI
     *
     * @since 1.0.0
     *
     * @param $item
     * @param $key
     *
     * @return string
     */
    private function customerVatOrSdi($item, $key)
    {
        $value = isset($item[$key]) && '' !== $item[$key] ? $item[$key] : null;

        if ('sdi_type' === $key && null === $value) {
            $value = '0000000';
        }

        if ('private' === $item['invoice_type'] && 'tax_code' !== $key) {
            $value = '';
        }

        if (! $value && ! in_array($item['billing']['country'], InvoiceFields::$euVatCountry, true)) {
            return esc_html__('No UE customer', WC_EL_INV_FREE_TEXTDOMAIN);
        }

        if (null === $value) {
            return esc_html__('(*) No data', WC_EL_INV_FREE_TEXTDOMAIN);
        }

        return $value;
    }

    /**
     * Sent Invoice icon
     *
     * @param $item
     *
     * @return string
     */
    private function sentInvoice($item)
    {

        if (! isset($item['invoice_sent'])) {
            return '';
        }

        switch ($item['invoice_sent']) {
            case 'sent':
                return '<i class="mark-yes dashicons dashicons-yes" title="'.WC_EL_INV_PREMIUM.'"></i>';
                break;
            case 'no_sent':
                return '<i class="mark-warning dashicons dashicons-warning" title="'.WC_EL_INV_PREMIUM.'"></i>';
                break;
            default:
                return '<i class="mark-warning dashicons dashicons-warning" title="'.WC_EL_INV_PREMIUM.'"></i>';
                break;
        }
    }

    /**
     * Reorder
     *
     * @since 1.0.0
     */
    public function reorder($a, $b)
    {
        // If no sort, default to id
        $orderby = (! empty($_GET['orderby'])) ? $_GET['orderby'] : 'id';
        // If no order, default to asc
        $order = (! empty($_GET['order'])) ? $_GET['order'] : 'desc';
        // Determine sort order

        if (isset($orderby) && '' !== $orderby) {
            $result = strcmp($a[$orderby], $b[$orderby]);

            // Send final sort direction to usort
            return ($order === 'asc') ? $result : -$result;
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
        );

        // Order Invoice number.
        $invoiceNumber = $order->get_meta('order_number_invoice');
        $invoiceSent   = \WcElectronInvoiceFree\Functions\getPostMeta('_invoice_sent', '', $order->get_id());

        $refundedData = array(
            'remaining_amount'        => $order->get_remaining_refund_amount(),
            'remaining_items'         => $order->get_remaining_refund_items(),
            'total_qty_refunded'      => $order->get_total_qty_refunded(),
            'total_refunded'          => $order->get_total_refunded(),
            'refunded_payment_method' => $order->get_meta('refund_payment_method'),
        );

        // Initialize Order Items
        $orderItems   = $order->get_items();
        $itemsData    = array();
        $refundedItem = array();

        foreach ($orderItems as $item) {
            if ($item instanceof \WC_Order_Item_Product) {
                $product = wc_get_product($item->get_product_id());
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
                        'qty_refunded_for_item' => $order->get_qty_refunded_for_item($item->get_id()),
                    );
                }
            }
        }

        $filePath = '/inc/ordersJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        return $data;
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
        $customerID = $parentOrder->get_user_id();
        $refundID   = $order->get_id();

        // Initialize Orders data and type.
        $orderType    = $order->get_type();
        $orderData    = $order->get_data();
        $invoiceMeta  = array(
            'vat_number'   => $parentOrder->get_meta('_billing_vat_number'),
            'tax_code'     => $parentOrder->get_meta('_billing_tax_code'),
            'invoice_type' => $parentOrder->get_meta('_billing_invoice_type'),
            'sdi_type'     => $parentOrder->get_meta('_billing_sdi_type'),
        );
        $refundedData = array(
            'remaining_amount'        => $parentOrder->get_remaining_refund_amount(),
            'remaining_items'         => $parentOrder->get_remaining_refund_items(),
            'total_qty_refunded'      => $parentOrder->get_total_qty_refunded(),
            'total_refunded'          => $parentOrder->get_total_refunded(),
            'refunded_payment_method' => $parentOrder->get_meta('refund_payment_method'),
        );
        // Order Refund Invoice number.
        $invoiceNumber = $order->get_meta("refund_number_invoice-{$refundID}");
        $invoiceSent   = \WcElectronInvoiceFree\Functions\getPostMeta('_invoice_sent', '', $order->get_id());

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

        // Initialize Order Items
        $orderItems        = $parentOrder->get_items();
        $itemsRefundedData = array();
        $refundedItem      = array();

        foreach ($orderItems as $item) {
            if ($item instanceof \WC_Order_Item_Product) {
                $varID   = $item->get_variation_id();
                $id      = isset($varID) && '0' !== $varID ? $varID : $item->get_product_id();
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

        $filePath = '/inc/ordersRefundedJsonArgs.php';
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        return $data;
    }

    /**
     * Get Orders
     *
     * @since 1.0.0
     *
     * @return array
     */
    public function getOrders()
    {
        $args = array(
            'status'  => array('processing', 'completed', 'refunded'),
            'limit'   => -1,
            'orderby' => 'date',
            'order'   => 'DESC',
            'return'  => 'ids',
        );

        // Invoice last 30 days
        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $now      = new \DateTime('now');
            $now->setTimezone($timeZone);

            $before = $now->getTimestamp();
            $now->modify("-1 month");
            $after                 = $now->getTimestamp();
            $args['date_created'] = "{$after}...{$before}";
        } catch (\Exception $e) {
        }

        // @codingStandardsIgnoreLine
        $customer = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'customer_id', FILTER_SANITIZE_STRING);
        $type     = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'type', FILTER_SANITIZE_STRING);
        $dateIN   = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_in', FILTER_SANITIZE_STRING);
        $dateOUT  = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_out', FILTER_SANITIZE_STRING);

        if ($customer && isset($customer) && '' !== $customer) {
            $args['customer_id'] = intval($customer);
        }

        if ($type && isset($type) && '' !== $type) {
            $args['type'] = "{$type}";
        }

        if ($dateIN || $dateOUT) {
            if (isset($dateIN) && '' !== $dateIN && ! $dateOUT) {
                $args['date_created'] = ">{$dateIN}";
            } elseif (! $dateIN && isset($dateOUT) && '' !== $dateOUT) {
                $args['date_created'] = "<{$dateOUT}";
            } elseif (isset($dateIN) && '' !== $dateIN && isset($dateOUT) && '' !== $dateOUT) {
                $args['date_created'] = "{$dateIN}...{$dateOUT}";
            }

            // Equal date
            if ($dateIN === $dateOUT) {
                $date                 = date('Y-m-d', intval($dateIN));
                $args['date_created'] = "{$date}";
            }
        }

        $args = apply_filters('wc_el_inv-xml_list_query_args', $args);

        $query = new \WC_Order_Query($args);

        $ordersData = array();

        // Set order for list
        try {
            $orders          = $query->get_orders();
            $incrementRefund = 0;
            foreach ($orders as $index => $orderID) {
                $order = wc_get_order($orderID);
                if ($order instanceof \WC_Order) {
                    $data      = $order->get_data();
                    $checkSent = get_post_meta($order->get_id(), '_invoice_sent', true);
                    $checkSent = isset($checkSent) && 'sent' === $checkSent ? true : false;

                    if (! $checkSent &&
                        floatval($order->get_total()) === floatval($order->get_total_refunded())
                    ) {
                        // Unset order
                        // Invoice order not sent and order total is equal total refunded
                        unset($orders[$index]);
                    }

                    // Unset refund
                    // Check for remove order refund from list
                    if (method_exists($order, 'get_refunds')) {
                        $refunds = $order->get_refunds();
                        if (! empty($refunds)) {
                            foreach ($refunds as $indexRefund => $refund) {

                                if (! $checkSent) {
                                    // No sent Invoice remove refund from list
                                    unset($refunds[$indexRefund]);
                                }

                                if (! $checkSent &&
                                    floatval(0) === (abs($order->get_total()) - abs($refund->get_total()))
                                ) {
                                    // No sent and zero is diff from order total and total refunded
                                    // Order totally refunded and not sent
                                    unset($refunds[$indexRefund]);
                                } elseif (! $checkSent && $refund->get_parent_id() === $order->get_id()) {
                                    // No sent Invoice and order id equal refund id
                                    unset($refunds[$indexRefund]);
                                }

                                if (isset($args['customer_id'])) {
                                    if (! empty($refunds)) {
                                        // Merge order and refund if filter by customers
                                        $orders = array_merge($orders, array($refund));
                                    }
                                }
                            }
                        }
                    }

                    // Force date completed for filter invoice
                    if (null === $data['date_completed']) {
                        $timeZone = new TimeZone();
                        $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
                        $date     = new \DateTime($data['date_modified']);
                        $date->setTimezone($timeZone);

                        $order->set_date_completed($date->getTimestamp());
                        $order->save();
                    }
                }

                // Unset order refund
                // Check for remove order refund from list
                if ($order instanceof \WC_Order_Refund) {

                    // Get check order sent meta time
                    $checkOrderSentTime = get_post_meta($order->get_parent_id(), '_invoice_sent_timestamp', true);
                    $checkOrderSentTime = isset($checkOrderSentTime) && '' !== $checkOrderSentTime ? $checkOrderSentTime : false;
                    // Get check order sent meta
                    $checkSent   = get_post_meta($order->get_parent_id(), '_invoice_sent', true);
                    $checkSent   = isset($checkSent) && 'sent' === $checkSent ? true : false;
                    $parentOrder = wc_get_order($order->get_parent_id());
                    // Get refund sent meta
                    $checkRefundSent = get_post_meta($order->get_id(), '_invoice_sent', true);
                    $checkRefundSent = isset($checkRefundSent) && 'sent' === $checkRefundSent ? true : false;
                    // Get refund sent meta time
                    $invoiceRefundSentTime = get_post_meta($order->get_id(), '_invoice_sent_timestamp', true);
                    $invoiceRefundSentTime = isset($invoiceRefundSentTime) && '' !== $invoiceRefundSentTime ? $invoiceRefundSentTime : false;

                    if (! $checkSent) {
                        // No sent Invoice remove refund from list
                        unset($orders[$index]);
                    }

                    // If the invoice time stamp is greater than the repayment time, it means that the
                    // refund was generated before the invoice was sent.
                    if ($checkSent && (intval($checkOrderSentTime) > intval($invoiceRefundSentTime))) {
                        unset($orders[$index]);
                    }

                    $totalRefund = abs($parentOrder->get_total_refunded());
                    $refunds     = $parentOrder->get_refunds();
                    if (! $checkSent && floatval(0) === (abs($parentOrder->get_total()) - $totalRefund)) {
                        // No sent Invoice and zero is diff from order total and total refunded
                        unset($orders[$index]);
                    } elseif (! $checkSent && $order->get_parent_id() === $order->get_id()) {
                        // No sent Invoice and order id equal refund id
                        unset($orders[$index]);
                    } elseif ((! $checkSent && ! $checkRefundSent) && floatval(0) === (abs($parentOrder->get_total()) - $totalRefund)) {
                        // No sent Invoice and Refund Total refund order
                        unset($orders[$index]);
                    } elseif ($checkSent && ! $checkRefundSent) {
                        // If invoice sent and current refund not sent refund isset multi refund
                        $numberRefund = count($refunds);
                        if ($numberRefund > 1) {
                            // Last refund
                            $sent = get_post_meta($order->get_id(), '_invoice_sent', true);
                            // If last refund not sent and isset other refunds continue else unset order
                            if ('no_sent' === $sent && isset($refunds[$index - $incrementRefund]) && $refunds[$index - $incrementRefund] instanceof \WC_Order_Refund) {
                                continue;
                            } else {
                                unset($orders[$index]);
                            }
                        } elseif ($numberRefund === 1) {
                            // Refund
                            $sent = get_post_meta($order->get_id(), '_invoice_sent', true);
                            // Invoice
                            $sentParent = get_post_meta($parentOrder->get_id(), '_invoice_sent', true);
                            if (! $sentParent && 'no_sent' === $sent &&
                                isset($refunds[$index - $incrementRefund]) && $refunds[$index - $incrementRefund] instanceof \WC_Order_Refund
                            ) {
                                unset($orders[$index]);
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            echo esc_html__("No orders found: ", WC_EL_INV_FREE_TEXTDOMAIN) . $e->getMessage();

            return $ordersData;
        }

        if (! empty($orders)) {
            // Set list ids
            foreach ($orders as $orderID) {
                $order           = wc_get_order($orderID);
                self::$listIds[] = $order->get_id();
            }
            self::$listIds = array_unique(self::$listIds);

            foreach ($orders as $orderID) {
                $order = wc_get_order($orderID);
                switch ($order) {
                    // Shop Order
                    case $order instanceof \WC_Order:
                        $ordersData[] = $this->getDataOrder($order);
                        break;
                    // Order Refunded
                    case $order instanceof \WC_Order_Refund:
                        $ordersData[] = $this->getDataRefundOrder($order);
                        break;
                    default:
                        break;
                }
            }
        }

        return $ordersData;
    }

    /**
     * @inheritdoc
     */
    public function get_columns()
    {
        $columns = array(
            'id'             => esc_html__('Order number', WC_EL_INV_FREE_TEXTDOMAIN),
            'total'          => esc_html__('Order Total', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice_number' => esc_html__('Number', WC_EL_INV_FREE_TEXTDOMAIN),
            'customer_id'    => esc_html__('Customer', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice_type'   => esc_html__('Customer Type', WC_EL_INV_FREE_TEXTDOMAIN),
            'vat_number'     => esc_html__('VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
            'tax_code'       => esc_html__('Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
            'sdi_type'       => esc_html__('SDI or PEC', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice_sent'   => esc_html__('Sent', WC_EL_INV_FREE_TEXTDOMAIN),
            'actions'        => esc_html__('Actions', WC_EL_INV_FREE_TEXTDOMAIN),
        );

        $columns = apply_filters('wc_el_inv-xml_list_columns', $columns);

        return $columns;
    }

    /**
     * @inheritdoc
     */
    public function get_sortable_columns()
    {
        $sortableColumns = array(
            'id'           => array('id', false),
            'total'        => array('total', false),
            'customer_id'  => array('customer_id', true),
            'invoice_type' => array('invoice_type', true),
            'vat_number'   => array('vat_number', true),
            'tax_code'     => array('tax_code', true),
            'sdi_type'     => array('sdi_type', true),
            'invoice_sent' => array('invoice_sent', true),
        );

        $sortableColumns = apply_filters('wc_el_inv-xml_list_sortable_columns', $sortableColumns);

        return $sortableColumns;
    }

    /**
     * @inheritdoc
     */
    public function prepare_items()
    {
        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($this->data, array(&$this, 'reorder'));

        $perPage     = 25;
        $currentPage = $this->get_pagenum();
        $totalItems  = count($this->data);

        $foundData = array_slice($this->data, (($currentPage - 1) * $perPage), $perPage);
        $this->set_pagination_args(array(
            'total_items' => $totalItems, //WE have to calculate the total number of items
            'per_page'    => $perPage     //WE have to determine how many items to show on a page
        ));
        $this->items = $foundData;
    }

    /**
     * @inheritdoc
     */
    public function column_default($item, $columnName)
    {
        switch ($columnName) {
            case 'id':
                return $this->orderTitle($item);
                break;
            case 'total':
                return $this->orderTotal($item);
                break;
            case 'invoice_number':
                return isset($item[$columnName]) ? $this->invoiceNumber($item[$columnName]) : '';
                break;
            case 'customer_id':
                return $this->orderCustomer($item);
                break;
            case 'invoice_type':
                return $this->customerType($item);
            case 'tax_code':
            case 'vat_number':
            case 'sdi_type':
                return $this->customerVatOrSdi($item, $columnName);
                break;
            case 'invoice_sent':
                return $this->sentInvoice($item);
                break;
            case 'actions':
                return isset($item['id']) ? $this->actions($item['id'], $item) : '';
                break;
            case $columnName:
                apply_filters('wc_el_inv-xml_list_column', $item, $columnName);
                break;
            default:
                return print_r($item, true);
                break;
        }
    }
}
