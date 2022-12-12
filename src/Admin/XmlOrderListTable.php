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
use function WcElectronInvoiceFree\Functions\filterInput;
use function WcElectronInvoiceFree\Functions\paymentMethodCode;

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
     * @var int
     */
    public static $itemN = 1;

    /**
     * XmlOrderListTable constructor.
     *
     * @param array $args
     *
     * @since 1.0.0
     *
     */
    public function __construct($args = array())
    {
        parent::__construct(array(
            'singular' => 'woopop-invoice',
            'plural'   => 'woopop-invoices',
            'ajax'     => false,
        ));

        $this->processBulkAction();

        $this->data = $this->convertObjInArray($this->getOrders());
    }

    /**
     * Convert Object in Array
     *
     * @param $dataObj
     *
     * @return array
     * @since 1.0.0
     *
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
     * @param $id
     * @param $item
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function actions($id, $item)
    {
        $output  = '';
        $nonce   = wp_create_nonce('wc_el_inv_invoice_pdf');
        $pdfArgs = "?format=pdf&nonce={$nonce}";
        $url     = home_url() . '/' . Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/';

        // Check if invoice is sent
        $checkSent = get_post_meta($id, '_invoice_sent', true);
        $checkSent = isset($checkSent) && 'sent' === $checkSent ? true : false;

        // Choice type
        $choiceTypeModifier = isset($item['choice_type']) ? esc_attr($item['choice_type']) : null;
        $orderType          = isset($item['order_type']) ? esc_attr($item['order_type']) : null;
        // Get params
        $dateOrderIN  = filterInput($_GET, 'date_in', FILTER_UNSAFE_RAW) ?: null;
        $dateOrderOUT = filterInput($_GET, 'date_out', FILTER_UNSAFE_RAW) ?: null;
        $orderSearch  = filterInput($_GET, 'order_search', FILTER_UNSAFE_RAW) ?: null;

        /**
         * Filter Check sent by Choice type modifier
         */
        $checkSent = apply_filters('wc_el_inv-actions_choice_type_modifier', $checkSent, $id, $choiceTypeModifier);

        $output .= sprintf('<div class="doc-type-wrap">' .
                           '<label title="%6$s" for="doc_type_invoice-%1$s">%2$s ' .
                           '<input class="doc-type-input" value="invoice" id="doc_type_invoice-%1$s" type="radio" name="doc_type-%1$s" %3$s></label>' .
                           '<label for="doc_type_receipt-%1$s">%4$s ' .
                           '<input class="doc-type-input" value="receipt"  id="doc_type_receipt-%1$s" type="radio" name="doc_type-%1$s" %5$s></label>' .
                           '</div><hr>',
            $item['id'],
            'shop_order' === $item['order_type'] ?
                esc_html_x('Invoice', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN) :
                esc_html_x('Refund', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN),
            '' === $item['choice_type'] || 'invoice' === $item['choice_type'] ? 'checked' : '',
            esc_html_x('Receipt', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN),
            'receipt' === $item['choice_type'] ? 'checked' : '',
            esc_html__('Remember to check the data entered by the customer', WC_EL_INV_FREE_TEXTDOMAIN)
        );

        $output .= sprintf('<input class="choice_type--current" type="hidden" value="%s">', $item['choice_type']);
        if ($checkSent) {
            $output .= sprintf(
                '<a id="mark_as_sent-%1$s" class="mark_trigger mark_undo button button-secondary" href="%2$s" title="%3$s">' .
                '<span class="dashicons dashicons-undo"></span></a>',
                $id,
                esc_url($url . "?id={$id}&undo=true&nonce={$nonce}"),
                esc_html__('Undo', WC_EL_INV_FREE_TEXTDOMAIN)
            );
        } else {
            $output .= sprintf(
                '<a id="mark_as_sent-%1$s" class="mark_trigger mark_as_sent button button-secondary" href="%2$s" title="%3$s">' .
                '<span class="dashicons dashicons-yes"></span></a>',
                $id,
                esc_url($url . "?id={$id}&sent=true&nonce={$nonce}"),
                true === $checkSent ? esc_html__('Disabled', WC_EL_INV_FREE_TEXTDOMAIN) : esc_html__('Mark as Sent',
                    WC_EL_INV_FREE_TEXTDOMAIN),
            );
        }

        // XML structure
        $output .= sprintf(
            '<a class="button button-secondary action-endpoint disabled" href="javascript:;" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-editor-ul"></span></a>',
            esc_html__('Get Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        // XML view whit style
        $output .= sprintf(
            '<a class="button button-primary action-endpoint disabled" href="javascript:;" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-visibility"></span></a>',
            esc_html__('View Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $itemDateTime = new \DateTime($item['date_created']);
            $itemDateTime->setTimezone($timeZone);
        } catch (\Exception $e) {
            $itemDateTime = null;
        }

        // Get 10 order IDs after date last night
        $ordersIds = wc_get_orders(array(
            'status'       => array('processing', 'completed', 'refunded'),
            'limit'        => 6,
            'orderby'      => 'date',
            'order'        => 'ASC',
            'date_created' => '>' . strtotime($item['date_created']),
            'return'       => 'ids',
        ));

        if (self::$itemN <= 10 &&
            'shop_order' === $orderType &&
            1 == $this->get_pagenum() &&
            ($itemDateTime instanceof \DateTime && $itemDateTime->format('Ym') === date('Ym', time())) &&
            count($ordersIds) < 5 &&
            (! $dateOrderIN &&
             ! $dateOrderOUT &&
             ! $orderSearch)
        ) {
            $n = base64_encode('item__' . self::$itemN);
            $nonce   = wp_create_nonce('wc_el_inv_invoice_xml');
            // XML download
            $output .= sprintf(
                '<a class="button button-secondary button-save action-endpoint" %1$s href="%2$s%3$s" title="%4$s" %5$s>' .
                '<span class="dashicons dashicons-media-code"></span></a>',
                false === $checkSent ? 'target="_blank"' : '',
                false === $checkSent ? esc_url($url . $id) : 'javascript:;',
                false === $checkSent ? "?format=xml&nonce={$nonce}&item={$n}&save=true" : '',
                false === $checkSent ?
                    esc_html__('Save Xml', WC_EL_INV_FREE_TEXTDOMAIN) :
                    esc_html__('Disabled', WC_EL_INV_FREE_TEXTDOMAIN),
                false === $checkSent ? '' : 'disabled="disabled"',
            );
        } else {
            // XML download
            $output .= sprintf(
                '<a class="button button-secondary button-save action-endpoint disabled" href="javascript:;" title="%1$s %2$s">' .
                '<span class="dashicons dashicons-media-code"></span></a>',
                esc_html__('Save Xml', WC_EL_INV_FREE_TEXTDOMAIN),
                WC_EL_INV_PREMIUM
            );
        }

        if (class_exists('\Dompdf\Dompdf')) {
            // PDF view
            $output .= sprintf(
                '<a class="button button-secondary button-pdf action-endpoint" %1$s href="%2$s%3$s" title="%4$s">' .
                '<span class="dashicons dashicons-media-text"></span></a>',
                'target="_blank"',
                esc_url($url . $id),
                $pdfArgs,
                esc_html__('View PDF', WC_EL_INV_FREE_TEXTDOMAIN)
            );
        }

        $output .= '<hr>';

        self::$itemN++;

        return $output;
    }

    /**
     * Get Order Type string
     *
     * @param $item
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function orderCustomer($item)
    {
        $customerLink = get_edit_user_link($item['customer_id']);
        $edit         = esc_html__('Edit customer', WC_EL_INV_FREE_TEXTDOMAIN);

        $name     = isset($item['billing']['first_name']) ? $item['billing']['first_name'] : '';
        $lastName = isset($item['billing']['last_name']) ? $item['billing']['last_name'] : '';
        $company  = isset($item['billing']['company']) ? $item['billing']['company'] : '';

        $fullName = isset($name) ? $name . ' ' . $lastName : $company;

        return sprintf('%s %s',
            ucfirst($fullName),
            isset($item['customer_id']) && 0 !== $item['customer_id'] ?
                "<br><small class='edit'><a href='{$customerLink}' title='{$edit}'>{$edit}</a></small>" : ''
        );
    }

    /**
     * Get Order Title
     *
     * @param $item
     *
     * @return string
     * @since 1.0.0
     *
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

            $output = '';
            $output .= sprintf('<strong>%s:</strong> %s<br><strong>%s</strong> - %s %s %s <br><strong>%s </strong><span %s>%s</span> %s',
                esc_html__('Customer', WC_EL_INV_FREE_TEXTDOMAIN),
                $this->orderCustomer($item),
                esc_html("#" . $item['id']),
                "{$type}",
                esc_html__('of', WC_EL_INV_FREE_TEXTDOMAIN),
                "{$dateTime}",
                sprintf('%s:', $type),
                $color,
                $status,
                "<br><small class='edit'><a href='{$editOrderLink}' title='{$edit}'>{$edit}</a></small>"
            );

            $nature = $order->get_meta('nature_rc');
            $ref    = $order->get_meta('ref_norm_rc');
            if ($nature) {
                $output .= sprintf('<hr><strong>%s:</strong> %s',
                    esc_html__('Nature operations', WC_EL_INV_FREE_TEXTDOMAIN),
                    $nature
                );
            }
            if ($ref) {
                $output .= sprintf('<br><strong>%s:</strong> %s',
                    esc_html__('Ref. Normative', WC_EL_INV_FREE_TEXTDOMAIN),
                    $ref
                );
            }

            return $output;
        } catch (\Exception $e) {
            echo esc_html__('Order title DateTime error: ', WC_EL_INV_FREE_TEXTDOMAIN) . $e->getMessage();
        }
    }

    /**
     * Order Total
     *
     * @param $item
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function orderTotal($item)
    {
        if (! isset($item['total'])) {
            return esc_html__('Error: no total order');
        }

        if (! empty($item['refunded']) && 'shop_order_refund' === $item['order_type']) {

            $total = $item['refunded']['total_refunded'];

            if (! empty($item['current_refund_items'])) {
                foreach ($item['current_refund_items'] as $item) {
                    $total = floatval($item['total']) + floatval($item['total_tax']);
                }
            }

            $editOrderLink = isset($item['order_id']) ? esc_url(get_edit_post_link($item['order_id'])) : '';
            $currency      = isset($item['currency']) ? $item['currency'] : '';
            $total         = sprintf('<strong>-%s %s</strong><br>%s: <a href="%s"><b>#%s</b></a>',
                $this->numberFormat(abs($total)),
                get_woocommerce_currency_symbol($currency),
                esc_html__('Linked order', WC_EL_INV_FREE_TEXTDOMAIN),
                $editOrderLink,
                $item['order_id']
            );
        } elseif (! empty($item['refunded']) && 'shop_order' === $item['order_type']) {

            $total = sprintf('<strong>%s %s</strong>',
                $this->numberFormat(abs($item['refunded']['remaining_amount'])),
                get_woocommerce_currency_symbol($item['currency'])
            );

            // check total refunded
            if (abs($item['refunded']['total_refunded']) === abs($item['total'])) {
                $total = sprintf('<strong>%s %s</strong>',
                    $this->numberFormat(abs($item['total'])),
                    get_woocommerce_currency_symbol($item['currency'])
                );
            }

            // add refunded total
            if (0 !== abs($item['refunded']['total_refunded']) &&
                abs($item['refunded']['total_refunded']) !== abs($item['total'])
            ) {
                $total = $total . sprintf('<br>%s <strong>%s %s</strong>',
                        esc_html__('Refunded', WC_EL_INV_FREE_TEXTDOMAIN),
                        $this->numberFormat(abs($item['refunded']['total_refunded'])),
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
     * @param int    $number
     * @param int    $decimal
     * @param bool   $abs
     * @param string $decSep
     * @param string $thSep
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function numberFormat($number = 0, $decimal = 2, $abs = true, $decSep = '.', $thSep = '')
    {
        if ($abs) {
            $number = abs($number);
        }

        return number_format($number, $decimal, $decSep, $thSep);
    }

    /**
     * Invoice Number
     *
     * @param $item
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function invoiceNumber($item)
    {
        $options = OptionPage::init();

        $order              = wc_get_order($item['id']);
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order_Refund');

        if (! $order instanceof $wcOrderClass && ! $order instanceof $wcOrderRefundClass) {
            return '';
        }

        $number = isset($item['invoice_number']) ? $item['invoice_number'] : '';

        if ('shop_order_refund' === $item['order_type']) {
            $number = $order->get_meta("refund_number_invoice-{$item['id']}");
        }

        // Number of digits
        $digits = $options->getOptions('number_digits_in_invoice');
        $digits = isset($digits) && '' !== $digits ? $digits : 2;
        // Prefix
        $prefix = $options->getOptions('prefix_invoice_number');
        $prefix = isset($prefix) && '' !== $prefix ? "{$prefix}-" : 'inv-';

        /**
         * Invoice prefix filter
         */
        $prefix = apply_filters('wc_el_inv-prefix_invoice', $prefix, $order);

        // Suffix
        $suffix     = $options->getOptions('suffix_invoice_number');
        $suffixYear = $options->getOptions('suffix_year_invoice_number');
        if ('on' === $suffixYear) {
            $created = $order->get_date_created();
            $suffix  = "/" . $created->format('Y');
        } else {
            $suffix = isset($suffix) && '' !== $suffix ? $suffix : '';
        }

        /**
         * Invoice suffix filter
         */
        $suffix = apply_filters('wc_el_inv-suffix_invoice', $suffix, $order);

        // Invoice number
        $invNumber = str_pad($number, $digits, '0', STR_PAD_LEFT);

        return isset($number) && 0 !== $number && '' !== $number ? "{$prefix}{$invNumber}{$suffix}" : '';
    }

    /**
     * Customer Type
     *
     * @param $item
     *
     * @return string
     * @since 1.0.0
     *
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
     * Choice Type
     *
     * @param $item
     *
     * @return string
     */
    private function choiceType($item)
    {
        switch ($item['choice_type']) {
            case 'receipt':
                $type = sprintf('<span style="color:dodgerblue;" class="dashicons dashicons-media-text" title="%s"></span>',
                    esc_html_x('Receipt', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN)
                );
                break;
            case 'invoice':
            case '':
                if ('shop_order' === $item['order_type']) {
                    $type = sprintf('<span style="color:green;" class="dashicons dashicons-media-text" title="%s"></span>',
                        esc_html_x('Invoice', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN)
                    );
                } else {
                    $type = sprintf('<span style="color:red;" class="dashicons dashicons-media-text" title="%s"></span>',
                        esc_html_x('Refund', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN)
                    );
                }
                break;
            default:
                $type = sprintf('<span style="color:green;" class="dashicons dashicons-media-text" title="%s"></span>',
                    esc_html_x('Invoice', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN)
                );
                break;
        }

        return $type;
    }

    /**
     * Custom VAT or SDI
     *
     * @param $item
     * @param $key
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerVatOrSdi($item, $key)
    {
        if ('receipt' === $item['choice_type']) {
            return '';
        }

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
                return '<i class="mark-yes dashicons dashicons-yes"></i>';
            case 'no_sent':
                return '<i class="mark-warning dashicons dashicons-warning"></i>';
            default:
                return '<i class="mark-warning dashicons dashicons-warning"></i>';
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
        $orderItems         = $order->get_items();
        $orderItemsTaxes    = $order->get_items('tax');
        $orderItemsShipping = $order->get_items('shipping');
        $orderItemsFee      = $order->get_items('fee');
        $itemsData          = array();
        $itemsDataTax       = array();
        $itemsDataShip      = array();
        $itemsDataFee       = array();
        $refundedItem       = array();

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

        // Tax
        foreach ($orderItemsTaxes as $itemID => $itemTax) {
            $itemsDataTax[] = $itemTax->get_data();
            $itemsDataTax   = array_filter($itemsDataTax);
        }

        // Shipping
        foreach ($orderItemsShipping as $itemID => $itemShip) {

            $dataShip   = $itemShip->get_data();
            $refundShip = $refundShipTax = 0;
            foreach ($dataShip as $key => $data) {
                if ('id' === $key) {
                    $taxRates = isset($dataShip['tax_class']) ? \WC_Tax::get_rates($dataShip['tax_class']) : array();
                    if (empty($taxRates)) {
                        $taxRates = \WC_Tax::get_base_tax_rates();
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

        // Fee
        foreach ($orderItemsFee as $itemID => $itemFee) {
            $dataFee   = $itemFee->get_data();
            $refundFee = $refundFeeTax = 0;
            foreach ($dataFee as $key => $data) {
                if ('id' === $key) {
                    $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                    if (empty($taxRates)) {
                        $taxRates = \WC_Tax::get_base_tax_rates();
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

        $filePath = '/inc/ordersJsonArgs.php';
        // Get Ids.
        if (get_query_var('get_ids') === 'true') {
            $orderID  = $order->get_id();
            $filePath = '/inc/ordersIdsJsonArgs.php';
        }
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        return $data;
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
        $customerID = $parentOrder->get_user_id();
        \WcElectronInvoiceFree\Functions\setCustomerLocation($customerID);
        $refundID = $order->get_id();

        // Initialize Orders data and type.
        $orderType    = $order->get_type();
        $orderData    = $order->get_data();
        $invoiceMeta  = array(
            'vat_number'   => $parentOrder->get_meta('_billing_vat_number'),
            'tax_code'     => $parentOrder->get_meta('_billing_tax_code'),
            'invoice_type' => $parentOrder->get_meta('_billing_invoice_type'),
            'sdi_type'     => $parentOrder->get_meta('_billing_sdi_type'),
            'choice_type'  => $parentOrder->get_meta('_billing_choice_type'),
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
        $orderItems                = $parentOrder->get_items();
        $orderItemsTaxes           = $parentOrder->get_items('tax');
        $orderItemsShipping        = $parentOrder->get_items('shipping');
        $orderItemsFee             = $parentOrder->get_items('fee');
        $itemsRefundedData         = array();
        $itemsRefundedDataTax      = array();
        $itemsRefundedDataFee      = array();
        $itemsRefundedDataShipping = array();
        $refundedItem              = array();

        // Current order refund item data
        // Product line
        $refundOrder      = wc_get_order($refundID);
        $refundOrderItems = $refundOrder->get_items();
        $currentRefund    = array();
        // Items refunded
        $refundItemsShipping = $refundOrder->get_items('shipping');
        $refundItemsFee      = $refundOrder->get_items('fee');
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
        if (! empty($refundItemsShipping) && false !== strpos($order->get_shipping_total(), '-')) {
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

        // Tax
        foreach ($orderItemsTaxes as $itemID => $itemTax) {
            $itemsRefundedDataTax[] = $itemTax->get_data();
            $itemsRefundedDataTax   = array_filter($itemsRefundedDataTax);
        }

        // Fee
        foreach ($orderItemsFee as $itemID => $itemFee) {
            $dataFee   = $itemFee->get_data();
            $refundFee = $refundFeeTax = 0;
            foreach ($dataFee as $key => $data) {
                if ('id' === $key) {
                    $taxRates = \WC_Tax::get_rates($dataFee['tax_class']);
                    if (empty($taxRates)) {
                        $taxRates = \WC_Tax::get_base_tax_rates();
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

        // Shipping
        foreach ($orderItemsShipping as $itemID => $itemShip) {
            $dataShip   = $itemShip->get_data();
            $refundShip = $refundShipTax = 0;
            foreach ($dataShip as $key => $data) {
                if ('id' === $key) {
                    $taxRates = \WC_Tax::get_rates($itemShip->get_tax_class());
                    if (empty($taxRates)) {
                        $taxRates = \WC_Tax::get_base_tax_rates();
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

        $filePath = '/inc/ordersRefundedJsonArgs.php';
        // Get Ids.
        if (get_query_var('get_ids') === 'true') {
            $orderID  = $order->get_id();
            $filePath = '/inc/ordersIdsJsonArgs.php';
        }
        // @codingStandardsIgnoreLine
        $data = include Plugin::getPluginDirPath($filePath);

        return $data;
    }

    /**
     * Get Orders
     *
     * @param bool $onlyCount
     *
     * @return array|int
     * @throws \Exception
     * @since 1.0.0
     */
    public function getOrders($onlyCount = false)
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
            $now->modify(apply_filters('wc_el_inv-orders_days_range', "-14 days"));
            $after                = $now->getTimestamp();
            $args['date_created'] = "{$after}...{$before}";
        } catch (\Exception $e) {
        }

        // Order Class
        $ordersData         = array();

        // @codingStandardsIgnoreLine
        $customer      = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'customer_id', FILTER_UNSAFE_RAW);
        $type          = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'type', FILTER_UNSAFE_RAW);
        $dateIN        = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_in', FILTER_UNSAFE_RAW);
        $dateOUT       = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_out', FILTER_UNSAFE_RAW);
        $orderToSearch = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'order_search',
            FILTER_SANITIZE_NUMBER_INT);

        if ($customer && isset($customer) && '' !== $customer) {
            $args['customer_id'] = intval($customer);
        }

        if ($type && isset($type) && '' !== $type && 'receipt' !== $type) {
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

        $query  = new \WC_Order_Query($args);
        $orders = $query->get_orders();

        // Get single order to search
        if ($orderToSearch) {
            $order = wc_get_order($orderToSearch);
            $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');
            $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order_Refund');

            if ($order instanceof $wcOrderClass || $order instanceof $wcOrderRefundClass) {
                switch ($order) {
                    // Shop Order
                    case $order instanceof $wcOrderClass:
                        $ordersData[] = $this->getDataOrder($order);
                        break;
                    // Order Refunded
                    case $order instanceof $wcOrderRefundClass:
                        $ordersData[] = $this->getDataRefundOrder($order);
                        break;
                    default:
                        $ordersData[] = array();
                        break;
                }
            }

            if ($onlyCount) {
                return count($ordersData);
            } else {
                return $ordersData;
            }
        }

        if ($onlyCount) {
            return count($orders);
        }

        /**
         * Add filter for get receipt order
         */
        add_filter('woocommerce_order_data_store_cpt_get_orders_query', function ($query, $queryVars) use ($type) {
            if ('receipt' === $type && empty($queryVars['_billing_choice_type'])) {
                $query['meta_query'][] = array(
                    'key'   => '_billing_choice_type',
                    'value' => esc_attr($type),
                );
            }

            return $query;
        }, 10, 2);

        // Set order for list
        try {
            $incrementRefund = 0;
            foreach ($orders as $index => $orderID) {
                $order = wc_get_order($orderID);
                $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order');
                $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($order, '\WC_Order_Refund');

                if ($order instanceof $wcOrderClass) {
                    $data      = $order->get_data();
                    $checkSent = get_post_meta($order->get_id(), '_invoice_sent', true);
                    $checkSent = isset($checkSent) && 'sent' === $checkSent ? true : false;

                    if ($type && '' !== $type && 'receipt' === $type) {
                        // Remove receipt if filter meta choice_type is't receipt
                        // Get meta order choice type
                        $meta = $order->get_meta('_billing_choice_type');
                        if ('receipt' !== $meta) {
                            unset($orders[$index]);
                        }
                    } elseif ($type && '' !== $type && 'shop_order' === $type) {
                        // Remove receipt if filter choice_type is't invoice
                        // Get meta order choice type
                        $meta = $order->get_meta('_billing_choice_type');
                        if ('invoice' !== $meta) {
                            unset($orders[$index]);
                        }
                    }

                    if (! $checkSent &&
                        (floatval($order->get_total()) === floatval(0) ||
                         floatval($order->get_total()) === floatval($order->get_total_refunded()))
                    ) {
                        // Unset order
                        // Invoice order not sent and order total is equal total refunded or order total is zero
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
                if ($order instanceof $wcOrderRefundClass) {

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
                            if ('no_sent' === $sent && isset($refunds[$index - $incrementRefund]) && $refunds[$index - $incrementRefund] instanceof $wcOrderRefundClass) {
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
                                isset($refunds[$index - $incrementRefund]) && $refunds[$index - $incrementRefund] instanceof $wcOrderRefundClass
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
                    case $order instanceof $wcOrderClass:
                        $ordersData[] = $this->getDataOrder($order);
                        break;
                    // Order Refunded
                    case $order instanceof $wcOrderRefundClass:
                        $ordersData[] = $this->getDataRefundOrder($order);
                        break;
                    default:
                        break;
                }
            }
        }

        // Store current filtered document
        if ($dateIN || $dateOUT || $customer || $type) {
            update_option('temp_order_filtered', $ordersData);
        } else {
            update_option('temp_order_filtered', array());
        }

        return $ordersData;
    }

    /**
     * @inheritdoc
     */
    public function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="%1$s[]" value="%2$s" />',
            $this->_args['singular'],
            $item['id']
        );
    }

    /**
     * Process Bulk
     */
    public function processBulkAction()
    {
        if (isset($_POST['woopop-invoice'])) {

            $action = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'bulk-sent', FILTER_UNSAFE_RAW);

            $args['woopop-invoice'] = array(
                'filter' => array(FILTER_UNSAFE_RAW),
                'flags'  => FILTER_FORCE_ARRAY,
            );

            $invoice = filter_var_array($_POST, $args);

            if (! empty($invoice)) {
                foreach ($invoice as $cbs) {
                    foreach ($cbs as $cbIndex => $id) {
                        if ($id) {
                            switch ($action) {
                                case 'sent':
                                    update_post_meta(intval($id), '_invoice_sent', 'sent');
                                    break;
                                case 'no_sent':
                                    update_post_meta(intval($id), '_invoice_sent', 'no_sent');
                                    break;
                                default:
                                    break;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function get_columns()
    {
        $columns = array(
            'cb'             => '<input type="checkbox" />',
            'id'             => esc_html__('Order', WC_EL_INV_FREE_TEXTDOMAIN),
            'total'          => esc_html__('Order Total', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice_number' => esc_html__('Number', WC_EL_INV_FREE_TEXTDOMAIN),
            'choice_type'    => esc_html__('Doc. required', WC_EL_INV_FREE_TEXTDOMAIN),
            'invoice_type'   => esc_html__('Customer Type', WC_EL_INV_FREE_TEXTDOMAIN),
            'data_order'     => esc_html__('Order data', WC_EL_INV_FREE_TEXTDOMAIN),
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
            'choice_type'  => array('choice_type', true),
            'invoice_type' => array('invoice_type', true),
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
            case 'total':
                return $this->orderTotal($item);
            case 'invoice_number':
                return $this->invoiceNumber($item);
            case 'invoice_type':
                return $this->customerType($item);
            case 'choice_type':
                return isset($item[$columnName]) ? $this->choiceType($item) : '';
            case 'data_order':
                $data = sprintf('<strong>%s:</strong> %s<br>',
                    esc_html__('VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
                    $this->customerVatOrSdi($item, 'vat_number')
                );
                $data .= sprintf('<strong>%s:</strong> %s<br>',
                    esc_html__('Tax Code', WC_EL_INV_FREE_TEXTDOMAIN),
                    $this->customerVatOrSdi($item, 'tax_code')
                );
                $data .= sprintf('<strong>%s:</strong> %s<br>',
                    esc_html__('SDI or PEC', WC_EL_INV_FREE_TEXTDOMAIN),
                    $this->customerVatOrSdi($item, 'sdi_type')
                );
                $data .= sprintf('<hr><strong>%s:</strong> %s',
                    esc_html__('Payment method', WC_EL_INV_FREE_TEXTDOMAIN),
                    paymentMethodCode((object)$item)
                );

                return $data;
            case 'invoice_sent':
                return $this->sentInvoice($item);
            case 'actions':
                return isset($item['id']) ? $this->actions($item['id'], $item) : '';
            case $columnName:
                return apply_filters('wc_el_inv-xml_list_column', $item, $columnName);
            default:
                return print_r($item, true);
        }
    }
}
