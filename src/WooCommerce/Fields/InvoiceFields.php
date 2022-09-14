<?php
/**
 * VatFields.php
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

namespace WcElectronInvoiceFree\WooCommerce\Fields;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Admin\Settings\OptionPage;
use WcElectronInvoiceFree\EndPoint\Endpoints;
use WcElectronInvoiceFree\Utils\TimeZone;
use function WcElectronInvoiceFree\Functions\disableInvoiceOnOrderTotalZero;

/**
 * Class InvoiceFields
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class InvoiceFields
{
    /**
     * List type
     *
     * @since 1.0.0
     */
    const LIST_TYPE = 'shop_order';

    /**
     * Meta Key
     *
     * @since 1.0.0
     *
     * @var string
     */
    public static $metaKey = 'billing_';

    /**
     * Option Key
     *
     * @since 1.0.0
     *
     * @var string
     */
    public static $optionKey = 'wc_el_inv-settings-';

    /**
     * Regex Tax Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexCF = "/^([A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-9LMNPQRSTUV]{2}[A-Za-z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1})$/i";

    /**
     * Regex Web Service Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexWEBSERV = "/^[a-zA-Z0-9]{7}$/i";

    /**
     * Regex PEC
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexPEC = "/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:\w*.?pec(?:.?\w+)*)$/i";

    /**
     * Regex Legal Mail
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexLEGALMAIL = "/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:\w*.?legalmail(?:.?\w+)*)$/i";

    /**
     * Regex VAT Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexVAT = "/^(ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{13})|HU[0-9]{8}|IE[0-9][A-Z0-9][0-9]{5}[A-Z]{1,2}|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$/i";

    /**
     * EU VAT Country
     *
     * @since 1.0.0
     *
     * @var
     */
    public static $euVatCountry;

    /**
     * Billing Fields
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $fields;

    /**
     * Billing Keys
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $keys = array();

    /**
     * Options
     *
     * @since 1.0.0
     *
     * @var OptionPage
     */
    private $options;

    /**
     * Countries
     *
     * @since 1.0.0
     *
     * @var \WC_Countries
     */
    private $countries;

    /**
     * InvoiceFields constructor.
     *
     * @param array      $fields
     * @param OptionPage $options
     *
     * @since 1.0.0
     *
     */
    public function __construct(array $fields, OptionPage $options)
    {
        $this->fields = $fields;
        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $this->keys[] = $key;
            }
        }

        $this->countries    = new \WC_Countries();
        self::$euVatCountry = $this->countries->get_european_union_countries();
        $this->options      = $options->getOptions();
    }

    /**
     * Front Address Billing Fields
     *
     * @param $fields
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public function billingAddressFields($fields)
    {
        $invoiceFields = array();

        /**
         * Filter Invoice Fields position
         *
         * 0   = first position
         * 150 = last position
         */
        $priority = apply_filters('billing_invoice_fields_priority', 150);

        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $userCountry = get_user_meta(get_current_user_id(), 'billing_country', true);
                $country     = is_user_logged_in() ? $userCountry : $this->countries->get_base_country();
                $hideOutUe   = isset($this->options[self::$optionKey . 'hide_outside_ue']) ? $this->options[self::$optionKey . 'hide_outside_ue'] : null;

                // No UE remove SDI field.
                if ('IT' !== $country &&
                    in_array($country, self::$euVatCountry, true) &&
                    'sdi_type' === $field['id']
                ) {
                    continue;
                }

                // Hide the VAT number and tax code field (via JS)
                // Set not required
                if (! in_array($country, self::$euVatCountry, true) &&
                    '' !== $userCountry &&
                    'hide' === $hideOutUe
                ) {
                    $field['required'] = false;
                    $field['value']    = '';
                }

                $field['label']       = esc_html__($field['label'], WC_EL_INV_FREE_TEXTDOMAIN);
                $field['description'] = esc_html__($field['description'], WC_EL_INV_FREE_TEXTDOMAIN);
                if (isset($field['placeholder'])) {
                    $field['placeholder'] = esc_html__($field['placeholder'], WC_EL_INV_FREE_TEXTDOMAIN);
                }

                if (isset($field['options'])) {
                    foreach ($field['options'] as $value => $label) {
                        $field['options'][$value] = esc_html__($label, WC_EL_INV_FREE_TEXTDOMAIN);
                    }
                }

                $invoiceFields[$key]             = $field;
                $invoiceFields[$key]['priority'] = $priority++;
            }
        }

        return array_merge((array)$fields, $invoiceFields);
    }

    /**
     * Admin User billing fields
     *
     * @param $fields
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public function customerFieldsFilter($fields)
    {
        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $field['class']                    = implode(' ', $field['class']);
                $fields['billing']['fields'][$key] = $field;
            }
        }

        return $fields;
    }

    /**
     * Admin Order Edit billing fields
     *
     * @param $fields
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public function editBillingFieldsFilter($fields)
    {
        $order                = wc_get_order();
        $choiceType           = $order->get_meta('_billing_choice_type');
        $removeValueIfReceipt = array(
            'sdi_type',
            'vat_number',
            'tax_code',
        );

        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $key = str_replace('billing_', '', $key);
                unset($field['class']);

                $field['wrapper_class'] = 'form-field-wide';
                $field['show']          = false;
                $field['id']            = "_billing_{$key}";
                $field['name']          = "_billing_{$key}";

                // Remove value if receipt is current choice
                if ('receipt' === $choiceType && in_array($key, $removeValueIfReceipt)) {
                    $field['value'] = '';
                }

                $fields[$key] = $field;
            }
        }

        return $fields;
    }

    /**
     * Front order actions
     *
     * @param $actions
     * @param $order
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public static function actionsFront($actions, $order)
    {
        if (class_exists('\Dompdf\Dompdf')) {
            // View invoice pdf link in my orders
            $myOrdersInvoice = OptionPage::init()->getOptions('invoice_in_my_orders');

            $nonce   = wp_create_nonce('wc_el_inv_invoice_pdf');
            $pdfArgs = "?format=pdf&nonce={$nonce}";
            if ('dev' === WC_EL_INV_ENV) {
                $pdfArgs = "?format=pdf";
            }

            // No pdf if order is 0 zero
            if (floatval(0) === floatval($order->get_total())) {
                return $actions;
            }

            $url = home_url() . '/' . Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/';

            if (method_exists($order, 'get_status') &&
                'on' === $myOrdersInvoice &&
                'no' === apply_filters('wc_el_inv-disable_my_orders_pdf_invoice', 'no')
            ) {
                if ('completed' === $order->get_status()) {
                    $actions['invoice'] = array(
                        'url'  => esc_url($url . $order->get_id() . $pdfArgs),
                        'name' => apply_filters('wc_el_inv-invoice_my_account_button_text',
                            esc_html__('Invoice', WC_EL_INV_FREE_TEXTDOMAIN)
                        ),
                    );
                    $actions['receipt'] = array(
                        'url'  => esc_url($url . $order->get_id() . $pdfArgs . '&choice_type=receipt'),
                        'name' => apply_filters('wc_el_inv-invoice_my_account_button_text',
                            esc_html__('Receipt', WC_EL_INV_FREE_TEXTDOMAIN)
                        ),
                    );
                }
            }
        }

        return $actions;
    }

    /**
     * Get Actions
     *
     * @param $id
     * @param $order
     *
     * @return string
     * @since 1.0.0
     *
     */
    public static function actions($id, $order)
    {
        $output  = '';
        $nonce   = wp_create_nonce('wc_el_inv_invoice_pdf');
        $pdfArgs = "?format=pdf&nonce={$nonce}";
        $url     = home_url() . '/' . Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/';

        // Check if invoice is sent
        $checkSent = get_post_meta($id, '_invoice_sent', true);
        $checkSent = isset($checkSent) && 'sent' === $checkSent ? true : false;

        // Choice type
        $choiceTypeModifier = $order->get_meta('_billing_choice_type');
        $choiceTypeModifier = isset($choiceTypeModifier) ? esc_attr($choiceTypeModifier) : null;

        /**
         * Filter Check sent by Choice type modifier
         */
        $checkSent = apply_filters('wc_el_inv-actions_choice_type_modifier', $checkSent, $id, $choiceTypeModifier);

        $orderType = wc_get_order($id)->get_type();
        $output    .= '<div class="actions">';

        $output .= sprintf('<strong>%s</strong>', esc_html__('Type of document to be created', WC_EL_INV_FREE_TEXTDOMAIN));

        $choiceType = $order->get_meta('_billing_choice_type');
        $output     .= sprintf('<p class="doc-type-wrap">' .
                               '<label for="doc_type_invoice-%1$s">%2$s ' .
                               '<input class="doc-type-input" value="invoice" id="doc_type_invoice-%1$s" type="radio" name="doc_type-%1$s" %3$s></label>' .
                               '<label for="doc_type_receipt-%1$s">%4$s ' .
                               '<input class="doc-type-input" value="receipt"  id="doc_type_receipt-%1$s" type="radio" name="doc_type-%1$s" %5$s></label>' .
                               '</p>',
            $id,
            'shop_order' === $orderType ?
                esc_html_x('Invoice', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN) :
                esc_html_x('Refund', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN),
            '' === $choiceType || 'invoice' === $choiceType ? 'checked' : '',
            esc_html_x('Receipt', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN),
            'receipt' === $choiceType ? 'checked' : ''
        );
        $output     .= sprintf('<input class="choice_type--current" type="hidden" value="%s">', $choiceType);

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
                true === $checkSent ? '' : esc_url($url . "?id={$id}&sent=true&nonce={$nonce}"),
                true === $checkSent ? esc_html__('Disabled', WC_EL_INV_FREE_TEXTDOMAIN) : esc_html__('Mark as Sent',
                    WC_EL_INV_FREE_TEXTDOMAIN)
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

        // XML download
        $output .= sprintf(
            '<a class="button button-secondary button-save action-endpoint disabled" href="javascript:;" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-media-code"></span></a>',
            esc_html__('Save Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

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

        $output .= '</div>';

        /**
         * Filter actions out put
         *
         * @since 1.0.0
         */
        $output = apply_filters('wc_el_inv-invoice_fields_actions', $output);

        return $output;
    }

    /**
     * Ajax Mark Invoice
     *
     * @return null
     * @since 2.0.0
     *
     */
    public function markInvoice()
    {
        if (! check_ajax_referer('wc_el_inv_ajax-ajax_nonce', 'nonce')) {
            return null;
        }

        if (! isset($_REQUEST) || false === wp_verify_nonce($_REQUEST['nonce'], 'wc_el_inv_ajax-ajax_nonce')) {
            return null;
        }

        if (defined('DOING_AJAX') && DOING_AJAX) {
            $urlParams = parse_url($_REQUEST['action_url']);
            $query     = isset($urlParams['query']) ? $urlParams['query'] : null;

            if (! $query) {
                return null;
            }

            $query   = explode('&', $query);
            $orderID = explode('=', $query[0]);
            $action  = explode('=', $query[1]);

            $orderID   = intval($orderID[1]);
            $checkSent = get_post_meta($orderID, '_invoice_sent');

            // Return if isset sent and action is sent
            if ('sent' === $checkSent && 'sent' === $action[0] && 'true' === $action[1]) {
                return null;
            }

            try {
                $timeZone = new TimeZone();
                $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
                $date     = new \DateTime('now');
                $date->setTimezone($timeZone);

                if ('sent' === $action[0] && 'true' === $action[1] && is_int($orderID) && 0 !== $orderID) {
                    update_post_meta($orderID, '_invoice_sent', 'sent');
                    update_post_meta($orderID, '_invoice_sent_timestamp', $date->getTimestamp());
                } elseif ('undo' === $action[0] && 'true' === $action[1] && is_int($orderID) && 0 !== $orderID) {
                    update_post_meta($orderID, '_invoice_sent', 'no_sent');
                    update_post_meta($orderID, '_invoice_sent_timestamp', $date->getTimestamp());
                }
            } catch (\Exception $e) {
                echo esc_html__('Order Mark DateTime error: ', WC_EL_INV_FREE_TEXTDOMAIN) . $e->getMessage();
            }
        }

        die();
    }

    /**
     * General Invoice data option order
     *
     * @param $order \WC_Order
     *
     * @since 1.0.0
     *
     */
    public function editGeneralFieldsFilter($order)
    {
        $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');

        if (! is_a($order, $wcOrderClass)) {
            return;
        }

        if ('completed' !== $order->get_status() && 'processing' !== $order->get_status()) {
            return;
        }

        // No It return
        if (isset($order->billing['country']) && 'IT' !== $order->billing['country']) {
            return;
        }

        // Disable invoice order whit total zero
        if (disableInvoiceOnOrderTotalZero($order)) {
            return;
        }

        $orderData = $order->get_data();

        echo '<div class="wc_el_inv__general-order">';
        echo sprintf('<h3>%s</h3>', esc_html__('Invoice data', WC_EL_INV_FREE_TEXTDOMAIN));

        // Get date completed
        $dateCompleted = isset($orderData['date_completed']) && '' !== $orderData['date_completed'] ?
            $orderData['date_completed'] : $orderData['date_modified'];
        $invoiceNumber = $order->get_meta('order_number_invoice');

        echo '<div class="wc_el_inv__general-order invoice-date">';
        // Saved data.
        echo sprintf(
            '<p class="wc_el_inv__general-order--text-data"><strong>%s:</strong> %s<br><strong>%s:</strong> %s</p>',
            esc_html__('Invoice number', WC_EL_INV_FREE_TEXTDOMAIN),
            $invoiceNumber,
            esc_html__('Invoice data', WC_EL_INV_FREE_TEXTDOMAIN),
            $dateCompleted->format('Y-m-d - H:i')
        );

        // Fields.
        echo '<div class="wc_el_inv__general-order--hidden-fields">';
        woocommerce_wp_text_input(array(
            'label'             => esc_html__('Invoice number', WC_EL_INV_FREE_TEXTDOMAIN),
            'class'             => 'wc_el_inv-order_fields',
            'id'                => 'order_number_invoice',
            'name'              => 'order_number_invoice',
            'custom_attributes' => array(
                'disabled' => 'disabled',
            ),
        ));

        // DatePicker scripts.
        wp_script_is('wc_el_inv_datepicker', 'registered') ? wp_enqueue_script('wc_el_inv_datepicker') : null;
        wp_script_is('datepicker-lang', 'registered') ? wp_enqueue_script('datepicker-lang') : null;

        echo sprintf('<p><label for="order_date_invoice">%s</label></p>',
            esc_html__('Invoice Date', WC_EL_INV_FREE_TEXTDOMAIN));
        woocommerce_wp_text_input(array(
            'label'             => '',
            'id'                => 'order_date_invoice',
            'class'             => 'wc_el_inv-order_fields wc_el_inv-datepicker',
            'name'              => 'order_date_invoice',
            'value'             => $dateCompleted->format('Y-m-d'),
            'custom_attributes' => array(
                'disabled' => 'disabled',
            ),
        ));
        echo '<span class="hours">@</span>';
        woocommerce_wp_text_input(array(
            'label'             => '',
            'type'              => 'number',
            'class'             => 'wc_el_inv-order_fields',
            'id'                => 'order_hours_invoice',
            'name'              => 'order_hours_invoice',
            'value'             => $dateCompleted->format('H'),
            'custom_attributes' => array(
                'disabled' => 'disabled',
            ),
        ));
        echo '<span class="minutes">:</span>';
        woocommerce_wp_text_input(array(
            'label'             => '',
            'type'              => 'number',
            'class'             => 'wc_el_inv-order_fields',
            'id'                => 'order_minutes_invoice',
            'name'              => 'order_minutes_invoice',
            'value'             => $dateCompleted->format('i'),
            'custom_attributes' => array(
                'disabled' => 'disabled',
            ),
        ));
        echo '</div>';
        echo self::actions($order->get_id(), $order);
        echo '</div></div>';
    }

    /**
     * Edit refund invoice data
     *
     * @param $orderID
     *
     * @since 1.0.0
     *
     */
    public function editInvoiceDataOrderRefund($orderID)
    {
        $order = null;

        if (is_int($orderID)) {
            $order = wc_get_order($orderID);
        }

        // No It return
        if (isset($order->billing['country']) && 'IT' !== $order->billing['country']) {
            return;
        }

        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');

        if (is_a($order, $wcOrderClass)) {

            // Get check order sent meta
            $checkOrderSent     = get_post_meta($order->get_id(), '_invoice_sent', true);
            $checkOrderSent     = isset($checkOrderSent) && 'sent' === $checkOrderSent ? true : false;
            $checkOrderSentTime = get_post_meta($order->get_id(), '_invoice_sent_timestamp', true);
            $checkOrderSentTime = isset($checkOrderSentTime) && '' !== $checkOrderSentTime ? $checkOrderSentTime : false;

            // Get Refunds
            $refunds = $order->get_refunds();
            if (! empty($refunds)) {
                foreach ($refunds as $index => $refund) {
                    // Order refund
                    if (! $refund instanceof $wcOrderRefundClass) {
                        return;
                    }

                    // Get refund sent meta
                    $invoiceRefundSentTime = get_post_meta($refund->get_id(), '_invoice_sent_timestamp', true);
                    $invoiceRefundSentTime = isset($invoiceRefundSentTime) && '' !== $invoiceRefundSentTime ? $invoiceRefundSentTime : false;
                    if ($checkOrderSent &&
                        $checkOrderSentTime &&
                        $invoiceRefundSentTime &&
                        (intval($checkOrderSentTime) > intval($invoiceRefundSentTime))
                    ) {
                        continue;
                    }

                    if (! $checkOrderSent) {
                        continue;
                    }

                    // Check Refund sent.
                    $refundSent = get_post_meta($refund->get_id(), '_invoice_sent', true);
                    if (count($refunds) > 1) {
                        if ('no_sent' === $refundSent && isset($refunds[$index - 1]) && $refunds[$index - 1] instanceof $wcOrderRefundClass) {
                            continue;
                        }
                    }

                    if (! $checkOrderSent && floatval(0) === (abs($order->get_total()) - abs($refund->get_total()))) {
                        continue;
                    }

                    $dateRefund = $refund->get_data();
                    $refundID   = $refund->get_id();
                    // Get date refund
                    $dateRefund    = $dateRefund['date_created'];
                    $invoiceNumber = $refund->get_meta("refund_number_invoice-{$refundID}");

                    echo "<tr class='refund wc_el_inv__refund-invoice' data-order_refund_id='{$refundID}'>";
                    echo '<td class="thumb"><div></div></td>';
                    echo '<td colspan="7">';
                    echo sprintf('<h3>%s #%s</h3>', esc_html__('Refund', WC_EL_INV_FREE_TEXTDOMAIN), $refundID);
                    // Saved data.
                    echo sprintf(
                        '<p class="wc_el_inv__refund-invoice--text-data"><strong>%s:</strong> %s<br><strong>%s:</strong> %s</p>%s',
                        esc_html__('Refund number', WC_EL_INV_FREE_TEXTDOMAIN),
                        $invoiceNumber,
                        esc_html__('Refund data', WC_EL_INV_FREE_TEXTDOMAIN),
                        $dateRefund->format('Y-m-d - H:i'),
                        self::actions($refundID, $order)
                    );

                    // Fields.
                    echo '<div class="wc_el_inv__refund-invoice--hidden-fields">';
                    woocommerce_wp_text_input(array(
                        'label'             => esc_html__('Invoice number', WC_EL_INV_FREE_TEXTDOMAIN),
                        'class'             => 'wc_el_inv-order_fields',
                        'id'                => "refund_number_invoice-{$refundID}",
                        'name'              => "refund_number_invoice-{$refundID}",
                        'value'             => $invoiceNumber,
                        'custom_attributes' => array(
                            'disabled' => 'disabled',
                        ),

                    ));
                    echo sprintf('<p><label for="order_date_invoice">%s</label></p>',
                        esc_html__('Invoice Date', WC_EL_INV_FREE_TEXTDOMAIN));
                    woocommerce_wp_text_input(array(
                        'label'             => '',
                        'id'                => "refund_date_invoice-{$refundID}",
                        'class'             => 'wc_el_inv-order_fields wc_el_inv-datepicker',
                        'name'              => "refund_date_invoice-{$refundID}",
                        'value'             => $dateRefund->format('Y-m-d'),
                        'custom_attributes' => array(
                            'disabled' => 'disabled',
                        ),
                    ));
                    echo '<span class="hours">@</span>';
                    woocommerce_wp_text_input(array(
                        'label'             => '',
                        'type'              => 'number',
                        'class'             => 'wc_el_inv-order_fields',
                        'id'                => "refund_hours_invoice-{$refundID}",
                        'name'              => "refund_hours_invoice-{$refundID}",
                        'value'             => $dateRefund->format('H'),
                        'custom_attributes' => array(
                            'disabled' => 'disabled',
                        ),
                    ));
                    echo '<span class="minutes">:</span>';
                    woocommerce_wp_text_input(array(
                        'label'             => '',
                        'type'              => 'number',
                        'class'             => 'wc_el_inv-order_fields',
                        'id'                => "refund_minutes_invoice-{$refundID}",
                        'name'              => "refund_minutes_invoice-{$refundID}",
                        'value'             => $dateRefund->format('i'),
                        'custom_attributes' => array(
                            'disabled' => 'disabled',
                        ),
                    ));

                    echo '</td>';

                    do_action('wc_el_inv-after_refund_order_item_td', $refundID);

                    echo '</tr>';
                }
            }
        }
    }

    /**
     * Refunded payment method
     *
     * @param $orderID
     *
     * @since 1.0.0
     *
     */
    public function refundedPaymentMethod($orderID)
    {
        $order = null;

        if (is_int($orderID)) {
            $order = wc_get_order($orderID);
        }

        // No It return
        if (isset($order->billing['country']) && 'IT' !== $order->billing['country']) {
            return;
        }

        $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
        if (is_a($order, $wcOrderClass)) {
            // Get Refunds
            $refunds = $order->get_refunds();

            if (! empty($refunds)) {

                $refundPayment = $order->get_meta('refund_payment_method');

                // Fields.
                echo '<tr class="wc_el_inv__refund-invoice--payment-fields">';
                echo sprintf('<td class="label refunded-total">%s</td>',
                    esc_html__('* Payment method', WC_EL_INV_FREE_TEXTDOMAIN)
                );
                echo '<td width="1%"></td><td class="total refunded-total">';
                woocommerce_wp_select(array(
                    'label'    => '',
                    'id'       => 'refund_payment_method',
                    'name'     => 'refund_payment_method',
                    'desc_tip' => false,
                    'value'    => $refundPayment,
                    'options'  => array(
                        ''     => esc_html__('Set payment method', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP01' => esc_html__('Cash money', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP02' => esc_html__('Bank check', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP03' => esc_html__('Circular check', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP05' => esc_html__('Bank transfer', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP08' => esc_html__('Payment Card', WC_EL_INV_FREE_TEXTDOMAIN),
                    ),
                ));
                echo '</td></tr>';
            }
        }
    }

    /**
     * Admin Order view billing details
     *
     * @param $order
     *
     * @since 1.0.0
     *
     */
    public function viewBillingFieldsFilter($order)
    {
        $orderData = $order->get_data();
        $output    = $type = $viesMsg = '';

        $nature = $order->get_meta('nature_rc');
        $ref    = $order->get_meta('ref_norm_rc');

        if ($nature) {
            $output .= sprintf('<p><strong>%s:</strong><br>%s</p>',
                esc_html__('Nature operations', WC_EL_INV_FREE_TEXTDOMAIN),
                $nature
            );
        }

        if ($ref) {
            $output .= sprintf('<p><strong>%s:</strong><br>%s</p>',
                esc_html__('Ref. Normative', WC_EL_INV_FREE_TEXTDOMAIN),
                $ref
            );
        }

        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $key  = str_replace('billing_', '', $key);
                $meta = $this->getMeta($order, $key);

                if ('invoice_type' === $key && 'private' === $meta) {
                    $type = $meta;
                }

                if ('private' === $type && ('sdi_type' === $key || 'vat_number' === $key)) {
                    continue;
                }

                // empty SDI IT
                if ('sdi_type' === $key && '' === $meta) {
                    $meta = '0000000';
                    // empty SDI extra UE
                    if (! in_array($orderData['billing']['country'], self::$euVatCountry, true)) {
                        $meta = 'XXXXXXX';
                    }
                }

                // Vies check message.
                if (in_array($orderData['billing']['country'], self::$euVatCountry, true) &&
                    'IT' !== $orderData['billing']['country'] && 'vat_number' === $key
                ) {
                    $invoiceType = $this->getMeta($order, 'invoice_type');
                    $vatNumber   = $this->getMeta($order, 'vat_number');
                    if ('company' === $invoiceType || 'freelance' === $invoiceType) {
                        $check = $this->viesCheck($vatNumber, $orderData['billing']['country']);
                        if (false === $check) {
                            $viesMsg = sprintf('<span style="color:red;"> %s <a target="_blank" href="%s">%s</a></span>',
                                esc_html__('VIES control: The VAT is not valid', WC_EL_INV_FREE_TEXTDOMAIN),
                                esc_url('http://ec.europa.eu/taxation_customs/vies/vatRequest.html'),
                                esc_html__('(see VIES)', WC_EL_INV_FREE_TEXTDOMAIN)
                            );
                        } elseif (true === $check) {
                            $viesMsg = sprintf('<span style="color:green;"> %s </span>',
                                esc_html__('VIES control: VAT is valid', WC_EL_INV_FREE_TEXTDOMAIN)
                            );
                        }
                    }
                } else {
                    $viesMsg = '';
                }

                switch ($meta) {
                    case 'company':
                        $meta = esc_html__('Company', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    case 'freelance':
                        $meta = esc_html__('Freelance', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    case 'private':
                        $meta = esc_html__('Private person', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    case 'invoice':
                        $meta = esc_html_x('Invoice', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    case 'receipt':
                        $meta = esc_html_x('Receipt', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    default:
                        break;
                }

                // Remove value if receipt is current choice
                $choiceType           = $order->get_meta('_billing_choice_type');
                $removeValueIfReceipt = array(
                    'sdi_type',
                    'vat_number',
                    'tax_code',
                );
                if ('receipt' === $choiceType && in_array($key, $removeValueIfReceipt)) {
                    $meta = '';
                }

                // Set label for choice type
                if ('choice_type' === $key) {
                    $field['label'] = esc_html__('Type of document', WC_EL_INV_FREE_TEXTDOMAIN);
                }

                $output .= sprintf('<p><strong>%s:%s</strong><br>%s</p>',
                    $field['label'],
                    $viesMsg,
                    strtoupper($meta)
                );
            }

            echo $output;
        }
    }

    /**
     * Order Formatted Billing Address
     *
     * @param $fields
     * @param $order
     *
     * @return array
     * @since 1.0.0
     *
     */
    public function orderFormattedBillingAddress($fields, $order)
    {
        $extraFields = array();
        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $key               = str_replace('billing_', '', $key);
                $extraFields[$key] = $this->getMeta($order, $key);
            }
        }

        $fields = array_merge($fields, $extraFields);

        return $fields;
    }

    /**
     * Front My Account Address
     *
     * @param $fields
     * @param $customerID
     * @param $type
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public function myAccountFormattedAddress($fields, $customerID, $type)
    {
        if ('shipping' === $type) {
            return $fields;
        }

        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $key = str_replace('billing_', '', $key);
                if ($type == 'billing') {
                    $fields[$key] = get_user_meta($customerID, "billing_{$key}", true);
                }
            }
        }

        return $fields;
    }

    /**
     * Formatted Address Replacements
     *
     * @param $address
     * @param $args
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public function formattedAddressReplacements($address, $args)
    {
        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $key = str_replace('billing_', '', $key);

                $value = isset($args[$key]) ? $args[$key] : '';
                // Capitalized Invoice Type.
                if ('invoice_type' === $key && '' !== $value) {
                    $value = esc_html__(ucfirst(str_replace('-', ' ', $value)), WC_EL_INV_FREE_TEXTDOMAIN);
                }

                if ('private' === $value && ($key === 'vat_number' || $key === 'sdi_type')) {
                    $value = '';
                }

                if ('vat_number' === $key && '11111111111' === $value) {
                    $value = '';
                }

                if ('sdi_type' === $key && '1111111' === $value) {
                    $value = '';
                }

                if ('tax_code' === $key && 'XXXXXX00L00L000X' === $value) {
                    $value = '';
                }

                $address["{{$key}}"] = $value;
            }
        }

        if (array_key_exists('sdi_type', $args) &&
            '' === $address["{sdi_type}"] &&
            ('' !== $address["{tax_code}"] || '' !== $address["{vat_number}"])
        ) {
            $address['{sdi_type}'] = "0000000";
        }

        if (isset($address["{choice_type}"])) {
            switch ($address["{choice_type}"]) {
                case 'invoice':
                    $address["{choice_type}"] = esc_html_x('Invoice', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN);
                    break;
                case 'receipt':
                    $address["{choice_type}"] = esc_html_x('Receipt', 'invoice_choice', WC_EL_INV_FREE_TEXTDOMAIN);
                    break;
                default:
                    $address["{choice_type}"] = '';
                    break;
            }
        }

        return $address;
    }

    /**
     * Localization Address Format
     *
     * @param $formats
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public function localisationAddressFormat($formats)
    {
        foreach ($formats as $country => $value) {
            if ('IT' === $country) {
                $formats[$country] = $value . "\n{invoice_type}\n{vat_number}\n{tax_code}\n{sdi_type}";
            } elseif (in_array($country, self::$euVatCountry, true)) {
                $formats[$country] = $value . "\n{invoice_type}\n{vat_number}\n{tax_code}";
            }
        }

        return $formats;
    }

    /**
     * Ajax Found Custom Meta
     *
     * @param $customerData
     * @param $customer
     * @param $userID
     *
     * @return mixed
     * @since 1.0.0
     *
     */
    public function foundCustomerMeta($customerData, $customer, $userID)
    {
        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $billingKey                           = str_replace('billing_', '', $key);
                $value                                = get_user_meta($userID, $key, true);
                $customerData['billing'][$billingKey] = $value;
            }
        }

        return $customerData;
    }

    /**
     * Get Meta
     *
     * @param        $order
     * @param        $key
     * @param string $default
     *
     * @return mixed|string
     * @since 1.0.0
     *
     */
    private function getMeta($order, $key, $default = '')
    {
        $value = $default;

        if ($order) {
            if (defined('WC_VERSION') && version_compare(WC_VERSION, '2.7', '<')) {
                $value = get_post_meta($order->get_id(), "_billing_{$key}", true);
            } else {
                if (method_exists($order, 'get_meta')) {
                    $value = $order->get_meta("_billing_{$key}", true);
                }
            }

            $value = '' !== $value ? $value : $default;
        }

        return $value;
    }

    /**
     * Check VIES VAT
     *
     * @param      $vatCode
     * @param      $country
     * @param bool $otherCheck
     *
     * @return array|bool|null
     */
    public static function viesCheck($vatCode, $country, $otherCheck = false)
    {
        // If soap not exist return true
        if (! class_exists('SoapClient')) {
            return true;
        }

        // Vies check options
        $invoiceViesCheck = OptionPage::init()->getOptions('invoice_vies_check');
        // If disable default check is null
        if (false === $otherCheck &&
            (is_checkout() || is_account_page()) &&
            ('off' === $invoiceViesCheck || false === $invoiceViesCheck)
        ) {
            return null;
        }

        try {
            $client = new \SoapClient("https://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl");
            $check  = $client->checkVat(array(
                'countryCode' => $country,
                'vatNumber'   => $vatCode,
            ));

            if (is_object($check) && $check instanceof \stdClass) {
                if ($check->valid) {
                    return true;
                } else {
                    return false;
                }
            }
        } catch (\Exception $e) {
            // To avoid blocking the process I exclude the control or in case of error
            return array(
                'code'    => $e->getCode(),
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            );
        }
    }

    /**
     * Reverse Charge actions
     * Allowed RC
     *
     * @return bool
     */
    public static function allowedCheckoutReverseCharge()
    {
        $allowedReverseCharge = true;
        $cart                 = WC()->cart;
        $contents             = $cart->get_cart_contents();
        if (! empty($contents)) {
            foreach ($contents as $key => $content) {
                $id            = $content['product_id'];
                $reverseCharge = \WcElectronInvoiceFree\Functions\getPostMeta(
                    '_active_reverse_charge',
                    '',
                    $id
                );

                if ('' === $reverseCharge || 'no' === $reverseCharge) {
                    $allowedReverseCharge = false;
                    break;
                }
            }
        }

        return $allowedReverseCharge;
    }

    /**
     * Reverse Charge actions
     * RC tax class
     *
     * @return object
     */
    public static function rcTaxClass()
    {
        $taxClassName = TaxFields::getReverseChargeTaxClass();
        $taxClass     = \WC_Tax::get_rates_for_tax_class($taxClassName);
        if (is_array($taxClass) && ! empty($taxClass)) {
            return (object)array(
                'taxClass'     => $taxClass,
                'taxClassName' => $taxClassName,
                'haveTax'      => true,
            );
        } else {
            return (object)array(
                'taxClass'     => array(),
                'taxClassName' => '',
                'haveTax'      => false,
            );
        }
    }

    /**
     * Reverse Charge actions
     * Add meta for reverse charge
     *
     * @param $item
     * @param $cartItemKey
     * @param $values
     * @param $order
     */
    public static function addMetaCartItemLine($item, $cartItemKey, $values, $order)
    {
        // Active vies control
        $invoiceViesCheck = OptionPage::init()->getOptions('invoice_vies_check');
        if ('off' === $invoiceViesCheck || false === $invoiceViesCheck) {
            return;
        }

        if (! $item instanceof \WC_Order_Item_Product) {
            return;
        }

        /**
         * Return id not Allowed reverse charge
         */
        if (! self::allowedCheckoutReverseCharge()) {
            return;
        }

        // Reverse charge (product)
        $reverseCharge = \WcElectronInvoiceFree\Functions\getPostMeta(
            '_active_reverse_charge',
            '',
            $item->get_product_id()
        );

        // Reverse charge (global disable)
        $reverseChargeGlobalDisable = TaxFields::getReverseChargeDisable();

        $checkout = new \WC_Checkout();
        $postData = $checkout->get_posted_data();

        // Customer type, vat number and country
        $customerVatNumber   = ! empty($postData['billing_vat_number']) ? $postData['billing_vat_number'] : null;
        $customerInvoiceType = ! empty($postData['billing_invoice_type']) ? $postData['billing_invoice_type'] : null;
        $customerCountry     = ! empty($postData['billing_country']) ? $postData['billing_country'] : null;

        // Country IT return
        if ('IT' === $customerCountry) {
            return;
        }

        // Set prefix type used for retrieved nature or ref normative value
        $prefix = null;
        if (in_array($customerCountry, GeneralFields::getEuCountries()) && 'IT' !== $customerCountry) {
            $prefix = 'ue_';
        } elseif (! in_array($customerCountry, GeneralFields::getEuCountries())) {
            $prefix = 'extra_ue_';
        }

        // Set type
        switch ($customerInvoiceType) {
            case 'company':
            case 'freelance':
                $type = 'company';
                break;
            case 'private':
                $type = 'private';
                break;
            default:
                $type = null;
                break;
        }

        if (! $prefix || ! $type) {
            return;
        }

        // No vies check
        // Used for company and Ref. N7
        if ($customerVatNumber) {
            $vies = self::viesCheck($customerVatNumber, $customerCountry, true);
            if (false === $vies && ('company' === $customerInvoiceType || 'freelance' === $customerInvoiceType)) {
                $type = "{$type}_no_vies";
            }
        }

        $nature            = TaxFields::getReverseChargeNature("{$prefix}{$type}");
        $refNormative      = TaxFields::getReverseChargeRefNormative("{$prefix}{$type}");
        $refNormativeValue = esc_html($refNormative);

        // Add custom meta to line item
        if ('yes' === $reverseCharge && 'no' === $reverseChargeGlobalDisable && $order instanceof \WC_Order) {
            if ('' !== $nature && '' !== $refNormativeValue) {
                $order->add_meta_data('nature_rc', $nature);
                $order->add_meta_data('ref_norm_rc', $refNormativeValue);
            }
        }
    }

    /**
     * Reverse Charge actions
     * Vies Calc totals on checkout process.
     *
     * - If Vies control is ok remove tax on update checkout
     */
    private static function viesCalcTotals()
    {
        // Active vies control
        $invoiceViesCheck = OptionPage::init()->getOptions('invoice_vies_check');
        if ('off' === $invoiceViesCheck || false === $invoiceViesCheck) {
            return;
        }

        // Reverse charge (global disable)
        $reverseChargeGlobalDisable = TaxFields::getReverseChargeDisable();
        if ('yes' === $reverseChargeGlobalDisable) {
            return;
        }

        $rcTaxClass = self::rcTaxClass();
        if (! $rcTaxClass->haveTax) {
            return;
        }

        /**
         * Allowed reverse charge
         */
        if (self::allowedCheckoutReverseCharge()) {
            // Tax object
            $taxObj       = end($rcTaxClass->taxClass);
            $taxClassName = $rcTaxClass->taxClassName;

            // Remove shipping
            add_action('woocommerce_package_rates',
                function ($rates, $package) use ($taxObj) {
                    foreach ($rates as $rate) {
                        $rateTax = array();
                        $cost    = $rate->cost;
                        foreach ($rate->taxes as $key => $tax) {
                            $vat           = (($taxObj->tax_rate * 1));
                            $taxCost       = (floatval($cost) * $vat / 100);
                            $rateTax[$key] = $taxCost;
                        }
                        $rate->taxes = $rateTax;
                    }

                    return $rates;
                }, 2, PHP_INT_MAX
            );

            // Remove fee
            add_action('woocommerce_cart_totals_get_fees_from_cart_taxes',
                function ($feeTaxes, $fee, $instance) use ($taxObj) {
                    $feeTaxes = array();

                    $amount     = floatval($fee->object->amount);
                    $vat        = (($taxObj->tax_rate * 1));
                    $taxCost    = (floatval($amount) * $vat);
                    $feeTaxes[] = floatval($taxCost);

                    return $feeTaxes;
                }, 3, PHP_INT_MAX
            );

            // Set product tax based on Reverse Charge
            add_filter('woocommerce_product_get_tax_class',
                function ($taxClass, $product) use ($taxClassName) {

                    // Reverse charge (product)
                    $reverseCharge = \WcElectronInvoiceFree\Functions\getPostMeta(
                        '_active_reverse_charge',
                        '',
                        $product->get_id()
                    );

                    if ('yes' === $reverseCharge) {
                        $taxClass = $taxClassName;
                    }

                    return $taxClass;
                }, 2, PHP_INT_MAX
            );

            // Set product variation tax based on Reverse Charge
            add_filter('woocommerce_product_variation_get_tax_class',
                function ($taxClass, $product) use ($taxClassName) {

                    // Reverse charge (product)
                    $reverseCharge = \WcElectronInvoiceFree\Functions\getPostMeta(
                        '_active_reverse_charge',
                        '',
                        $product->get_id()
                    );

                    if ('yes' === $reverseCharge) {
                        $taxClass = $taxClassName;
                    }

                    return $taxClass;
                }, 2, PHP_INT_MAX
            );
        }
    }

    /**
     * Reverse Charge actions
     * Remove tax from order on update checkout
     *
     * @param $postData
     *
     * @since 3.0.0
     */
    public static function removeTaxFromOrder($postData)
    {
        // Active vies control
        $invoiceViesCheck = OptionPage::init()->getOptions('invoice_vies_check');
        if ('off' === $invoiceViesCheck || false === $invoiceViesCheck) {
            return;
        }

        // Reverse charge (global disable)
        $reverseChargeGlobalDisable = TaxFields::getReverseChargeDisable();
        if ('yes' === $reverseChargeGlobalDisable) {
            return;
        }

        $data = array();
        parse_str($postData, $data);

        $taxCode      = isset($data['billing_tax_code']) && '' !== $data['billing_tax_code'] ? $data['billing_tax_code'] : null;
        $vatNumber    = isset($data['billing_vat_number']) && '' !== $data['billing_vat_number'] ? $data['billing_vat_number'] : null;
        $country      = isset($data['billing_country']) && '' !== $data['billing_country'] ? $data['billing_country'] : null;
        $customerType = isset($data['billing_invoice_type']) && '' !== $data['billing_invoice_type'] ? $data['billing_invoice_type'] : null;

        // Company or Private
        if ($vatNumber || $taxCode || $customerType) {

            $viesCheck = false;

            // Vies check
            if ($vatNumber) {
                $viesCheck = self::viesCheck($vatNumber, $country);
            }

            // Remove if vies check or extra UE
            // 1. private and company extra UE
            // 2. company UE vies ok
            if (! in_array($country, self::$euVatCountry) xor
                ('IT' !== $country &&
                 in_array($country, self::$euVatCountry) &&
                 true === $viesCheck &&
                 'private' !== $customerType
                )
            ) {

                $rcTaxClass = self::rcTaxClass();
                if (! $rcTaxClass->haveTax) {
                    return;
                }

                /**
                 * Allowed reverse charge
                 */
                if (self::allowedCheckoutReverseCharge()) {
                    // Tax object
                    $taxObj       = end($rcTaxClass->taxClass);
                    $taxClassName = $rcTaxClass->taxClassName;

                    // Remove shipping
                    add_action('woocommerce_package_rates',
                        function ($rates, $package) use ($taxObj) {
                            foreach ($rates as $rate) {
                                $rateTax = array();
                                $cost    = $rate->cost;
                                foreach ($rate->taxes as $key => $tax) {
                                    $vat           = (($taxObj->tax_rate * 1));
                                    $taxCost       = (floatval($cost) * $vat / 100);
                                    $rateTax[$key] = $taxCost;
                                }
                                $rate->taxes = $rateTax;
                            }

                            return $rates;
                        }, 2, PHP_INT_MAX
                    );

                    // Remove fee
                    add_action('woocommerce_cart_totals_get_fees_from_cart_taxes',
                        function ($feeTaxes, $fee, $instance) use ($taxObj) {
                            $feeTaxes   = array();
                            $cost       = floatval($fee->object->amount);
                            $vat        = (($taxObj->tax_rate * 1));
                            $taxCost    = (floatval($cost) * $vat);
                            $feeTaxes[] = floatval($taxCost);

                            return $feeTaxes;
                        }, 3, PHP_INT_MAX
                    );

                    // Set product tax based on Reverse Charge
                    add_filter('woocommerce_product_get_tax_class',
                        function ($taxClass, $product) use ($taxClassName) {

                            // Reverse charge (product)
                            $reverseCharge = \WcElectronInvoiceFree\Functions\getPostMeta(
                                '_active_reverse_charge',
                                '',
                                $product->get_id()
                            );

                            if ('yes' === $reverseCharge) {
                                $taxClass = $taxClassName;
                            }

                            return $taxClass;
                        }, 2, PHP_INT_MAX
                    );

                    // Set product variation tax based on Reverse Charge
                    add_filter('woocommerce_product_variation_get_tax_class',
                        function ($taxClass, $product) use ($taxClassName) {

                            // Reverse charge (product)
                            $reverseCharge = \WcElectronInvoiceFree\Functions\getPostMeta(
                                '_active_reverse_charge',
                                '',
                                $product->get_id()
                            );

                            if ('yes' === $reverseCharge) {
                                $taxClass = $taxClassName;
                            }

                            return $taxClass;
                        }, 2, PHP_INT_MAX
                    );
                }

                // Set totals and add trigger element for view vies message
                add_filter('woocommerce_calculated_total',
                    function ($total, $instance) use ($viesCheck) {

                        // Cart total html
                        add_filter('woocommerce_cart_totals_order_total_html',
                            function ($value) use ($viesCheck) {
                                // Set class used from js script
                                if (true === $viesCheck) {
                                    $code = 'vies_tax';
                                } else {
                                    $code = is_array($viesCheck) ? strtolower($viesCheck['message']) : 'not_valid';
                                }

                                $abbr = 'vies_tax' === $code ? sprintf("<abbr style='color:green;' title='%s'>(*)</abbr>",
                                    esc_html__('Reverse Charge vat', WC_EL_INV_FREE_TEXTDOMAIN)) : '';

                                return $value . "<span id='wc_el_inv_vies_trigger' class='{$code}'> {$abbr} </span>";
                            }, PHP_INT_MAX);

                        return $total;
                    }, PHP_INT_MAX, 2
                );
            }
        }
    }

    /**
     * Set custom order meta for check shipping to different address
     *
     * @param $orderID
     * @param $postedData
     * @param $order
     *
     * @return mixed
     * @since 2.0.3
     *
     */
    public function haveShippingAddress($orderID, $postedData, $order)
    {
        $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');

        if ($order instanceof $wcOrderClass) {
            update_post_meta(
                $orderID,
                '_ship_to_different_address',
                esc_attr($postedData['ship_to_different_address'])
            );
        }

        return $orderID;
    }

    /**
     * Checkout Process
     *
     * @since 1.0.0
     */
    public function process()
    {
        $hideOutUe      = isset($this->options[self::$optionKey . 'hide_outside_ue']) ? $this->options[self::$optionKey . 'hide_outside_ue'] : null;
        $userCountry    = get_user_meta(get_current_user_id(), 'billing_country', true);
        $users          = get_users(array('meta_key' => 'billing_vat_number'));
        $disableTaxCode = OptionPage::init()->getOptions('invoice_disable_cf');
        $required       = OptionPage::init()->getOptions('invoice_required');

        if (! empty($this->keys)) {

            // @codingStandardsIgnoreLine
            $vatCode = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'vat_number',
                FILTER_UNSAFE_RAW
            );

            // @codingStandardsIgnoreLine
            $taxCode = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'tax_code',
                FILTER_UNSAFE_RAW
            );

            // @codingStandardsIgnoreLine
            $sdi = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'sdi_type',
                FILTER_UNSAFE_RAW
            );

            // @codingStandardsIgnoreLine
            $choiceDocType = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'choice_type',
                FILTER_UNSAFE_RAW
            );

            // @codingStandardsIgnoreLine
            $invoiceType = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'invoice_type',
                FILTER_UNSAFE_RAW
            );

            // @codingStandardsIgnoreLine
            $country = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                'billing_country',
                FILTER_UNSAFE_RAW
            );

            $company = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                'billing_company',
                FILTER_UNSAFE_RAW
            );

            if ('IT' === $country && 'company' === $invoiceType && '' === $company) {
                wc_add_notice(sprintf('<strong>%s</strong> %s', __('Name of the company', WC_EL_INV_FREE_TEXTDOMAIN),
                    __('it is a required field.', WC_EL_INV_FREE_TEXTDOMAIN)), 'error');
            }

            // No check if hide the VAT number and tax code field.
            if (! in_array($country, self::$euVatCountry, true) && 'hide' === $hideOutUe && '' !== $userCountry) {
                return true;
            }

            // Tax code length
            $vatCodeLength = isset($vatCode) ? strlen($vatCode) : 0;

            // Receipt ? reset sdi, vat and tax code fake data
            if ($choiceDocType && 'receipt' === $choiceDocType) {
                $_POST['billing_sdi_type']   = '';
                $_POST['billing_vat_number'] = '';
                $_POST['billing_tax_code']   = '';
            }

            // Invoice IT
            if ('IT' === $country && 'company' === $invoiceType || 'freelance' === $invoiceType) {

                /**
                 * Filter wc_el_inv-vat_checkout_check
                 * - activate or not vat check
                 *
                 * @since ${SINCE}
                 */
                // Vat check
                if (apply_filters('wc_el_inv-vat_checkout_check', true) &&
                    $invoiceType !== 'company' &&
                    (false === $choiceDocType || 'invoice' === $choiceDocType)
                ) {
                    foreach ($users as $user) {
                        $vat            = get_user_meta($user->ID, 'billing_vat_number', true);
                        $currentUserVat = get_user_meta(get_current_user_id(), 'billing_vat_number', true);

                        if ('' === $vatCode && '' === $vat && '' === $currentUserVat) {
                            continue;
                        }

                        if (
                            (! is_user_logged_in() && '' !== $vatCode && $vat === $vatCode) ||
                            (is_user_logged_in() && '' === $currentUserVat && $vatCode === $vat)
                        ) {
                            wc_add_notice(
                                sprintf(
                                    __('The VAT code entered "%s" is already associated with an account. please log in 
                            with the correct account or enter another VAT number', WC_EL_INV_FREE_TEXTDOMAIN),
                                    "<strong>" . strtoupper($vatCode) . "</strong>"
                                ),
                                'error'
                            );
                            break;
                        }
                    }
                }

                // Company IT Country and TAX code check
                if ('company' === $invoiceType && 'IT' === $country) {
                    if (! preg_match($this->regexVAT, $country . $vatCode) || $vatCodeLength < 11) {
                        wc_add_notice(
                            sprintf(
                                __('VAT number %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                                "<strong>{$country}-{$vatCode}</strong>"
                            ),
                            'error'
                        );
                    }

                    if ('on' !== $disableTaxCode && $taxCode !== $vatCode && ! preg_match($this->regexCF, $taxCode)) {
                        $code = strtoupper($taxCode);
                        wc_add_notice(
                            sprintf(
                                __('TAX code %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                                "<strong>{$code}</strong>"
                            ),
                            'error'
                        );
                    } else {
                        if ('on' !== $disableTaxCode && $taxCode === $vatCode &&
                            ! preg_match($this->regexVAT, $country . $taxCode)
                        ) {
                            $code = strtoupper($taxCode);
                            wc_add_notice(
                                sprintf(
                                    __('TAX code %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                                    "<strong>{$code}</strong>"
                                ),
                                'error'
                            );
                        }
                    }
                } // Freelance IT Country and TAX code check
                elseif ('freelance' === $invoiceType && 'IT' === $country) {
                    if (! preg_match($this->regexVAT, $country . $vatCode) || $vatCodeLength < 11) {
                        wc_add_notice(
                            sprintf(
                                __('VAT number %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                                "<strong>{$country}-{$vatCode}</strong>"
                            ),
                            'error'
                        );
                    }

                    if ('on' !== $disableTaxCode && ! preg_match($this->regexCF, $taxCode)) {
                        $code = strtoupper($taxCode);
                        wc_add_notice(
                            sprintf(
                                __('TAX code %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                                "<strong>{$code}</strong>"
                            ),
                            'error'
                        );
                    }
                }

                // E-mail PEC or recipient code check for Company and freelance
                if ('IT' === $country &&
                    ('company' === $invoiceType || 'freelance' === $invoiceType) &&
                    ! empty($sdi) &&
                    (! preg_match($this->regexPEC, $sdi) &&
                     ! preg_match($this->regexLEGALMAIL, $sdi)) &&
                    ! preg_match($this->regexWEBSERV, $sdi) &&
                    false === filter_var($sdi, FILTER_VALIDATE_EMAIL)
                ) {
                    wc_add_notice(
                        sprintf(
                            __('E-mail (PEC) or Web-Service code %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                            "<strong>{$sdi}</strong>"
                        ),
                        'error'
                    );
                }
            }

            // EU Country VAT check
            if (in_array($country, self::$euVatCountry) && $country !== 'IT') {
                // Unset SDI
                if (is_checkout()) {
                    add_filter('woocommerce_checkout_posted_data', function ($data) {
                        unset($data['billing_sdi_type']);

                        return $data;
                    });
                }

                if ('required' === $required) {
                    if ('company' === $invoiceType || 'freelance' === $invoiceType) {
                        if ('on' !== $disableTaxCode) {
                            if (empty($taxCode)) {
                                wc_add_notice(__('Please enter your Tax Code', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
                            }
                        }

                        if ('on' !== $disableTaxCode) {
                            if ((empty($vatCode) || strlen($vatCode) < 8)) {
                                wc_add_notice(__('Please enter your VAT number', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
                            }
                        }

                        if (! preg_match($this->regexVAT, $country . $vatCode)) {
                            wc_add_notice(
                                sprintf(
                                    __('VAT number %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                                    "<strong>{$country}-{$vatCode}</strong>"
                                ),
                                'error'
                            );
                        }
                    } elseif ('private' === $invoiceType && empty($taxCode)) {
                        wc_add_notice(__('Please enter your Tax Code', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
                    }
                }
            }

            // Private customer
            if ('private' === $invoiceType && 'IT' === $country) {
                // Unset SDI and VAT
                if (is_checkout()) {
                    add_filter('woocommerce_checkout_posted_data', function ($data) {
                        unset($data['billing_sdi_type']);
                        unset($data['billing_vat_number']);

                        return $data;
                    });
                }

                if (! preg_match($this->regexCF, $taxCode)) {
                    $code = strtoupper($taxCode);
                    wc_add_notice(
                        sprintf(
                            __('Tax Identification Number %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                            "<strong>{$code}</strong>"
                        ),
                        'error'
                    );
                }
            }

            // No UE check
            if (! in_array($country, self::$euVatCountry)) {
                if ('required' === $required) {
                    if ('company' === $invoiceType || 'freelance' === $invoiceType) {
                        if ('on' !== $disableTaxCode) {
                            if (empty($taxCode)) {
                                wc_add_notice(__('Please enter your Tax Code', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
                            }
                        }

                        if ('on' !== $disableTaxCode) {
                            if (empty($vatCode)) {
                                wc_add_notice(__('Please enter your VAT number', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
                            }
                        }
                    } elseif ('private' === $invoiceType && empty($taxCode)) {
                        wc_add_notice(__('Please enter your Tax Code', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
                    }
                }
            }

            // -- Reverse Charge Company vies check or extra ue -- //
            // 1. private and company extra UE
            // 1. company UE vies ok
            if (! in_array($country, self::$euVatCountry) xor
                ('IT' !== $country &&
                 in_array($country, self::$euVatCountry) &&
                 $this->viesCheck($vatCode, $country) &&
                 'private' !== $invoiceType
                )
            ) {
                self::viesCalcTotals();
            }
            // -- Reverse Charge Company vies check or extra ue -- //

            // Check all key
            foreach ($this->keys as $key) {
                // @codingStandardsIgnoreLine
                $value = \WcElectronInvoiceFree\Functions\filterInput(
                    $_POST,
                    $key,
                    FILTER_UNSAFE_RAW
                );

                $string = '';

                switch ($key) {
                    case 'billing_invoice_type':
                        $string = esc_html__('CUSTOMER TYPE', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    case 'billing_sdi_type':
                        $string = esc_html__('SDI OR PEC TYPE', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    case 'billing_vat_number':
                        $string = esc_html__('VAT NUMBER', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    case 'billing_tax_code':
                        $string = esc_html__('TAX CODE', WC_EL_INV_FREE_TEXTDOMAIN);
                        break;
                    default:
                        break;
                }

                if (! isset($value) || '' === $value) {
                    if ($country !== 'IT' && 'billing_sdi_type' === $key) {
                        continue;
                    }
                    wc_add_notice(
                        sprintf(
                            esc_html__('Please enter "%1$s" value.', WC_EL_INV_FREE_TEXTDOMAIN), 'error'),
                        $string
                    );
                }
            }
        }
    }

    /**
     * Admin Store meta
     *
     * @param $orderID
     *
     * @since 1.0.0
     *
     */
    public function saveOrderMetaBox($orderID)
    {
        $order        = wc_get_order($orderID);
        $wcOrderClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');

        if (! $order instanceof $wcOrderClass) {
            return;
        }

        // @codingStandardsIgnoreLine
        $number  = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_number_invoice', FILTER_SANITIZE_NUMBER_INT);
        $date    = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_date_invoice', FILTER_UNSAFE_RAW);
        $hour    = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_hours_invoice', FILTER_UNSAFE_RAW);
        $minute  = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_minutes_invoice', FILTER_UNSAFE_RAW);
        $payment = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'refund_payment_method', FILTER_UNSAFE_RAW);

        try {
            if ($number) {
                update_post_meta($order->get_id(), 'order_number_invoice', intval($number));
            }
            if ($payment) {
                update_post_meta($order->get_id(), 'refund_payment_method', $payment);
            }
            if ($date && $hour && $minute) {
                $date = gmdate('Y-m-d H:i', strtotime($date . ' ' . (int)$hour . ':' . (int)$minute));
                $order->set_date_completed($date);
                $order->save();
            }
        } catch (\Exception $e) {
            echo 'dev' === WC_EL_INV_ENV ? "No date completed updated: {$e->getMessage()}" : '';
        }
    }

    /**
     * Admin Store meta
     *
     * @param $orderID
     *
     * @since 1.0.0
     *
     */
    public function saveRefundMetaBox($orderID)
    {
        $order              = wc_get_order($orderID);
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');

        if (! $order instanceof $wcOrderClass) {
            return;
        }

        // Get Refunds
        $refunds = $order->get_refunds();
        if (! empty($refunds)) {
            foreach ($refunds as $refund) {
                // Order refund
                if (! $refund instanceof $wcOrderRefundClass) {
                    return;
                }

                $refundID = $refund->get_id();

                // @codingStandardsIgnoreLine
                $number = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_number_invoice-{$refundID}",
                    FILTER_SANITIZE_NUMBER_INT);
                $date   = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_date_invoice-{$refundID}",
                    FILTER_UNSAFE_RAW);
                $hour   = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_hours_invoice-{$refundID}",
                    FILTER_UNSAFE_RAW);
                $minute = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_minutes_invoice-{$refundID}",
                    FILTER_UNSAFE_RAW);

                try {
                    if ($number) {
                        update_post_meta($refundID, "refund_number_invoice-{$refundID}", intval($number));
                    }
                    if ($date && $hour && $minute) {
                        $date = gmdate('Y-m-d H:i', strtotime($date . ' ' . (int)$hour . ':' . (int)$minute));
                        $refund->set_date_created($date);
                        $refund->save();
                    }
                } catch (\Exception $e) {
                    echo 'dev' === WC_EL_INV_ENV ? "No date created updated: {$e->getMessage()}" : '';
                }
            }
        }
    }

    /**
     * Checkout Store
     *
     * @param $orderID
     *
     * @since 1.0.0
     *
     */
    public function store($orderID)
    {
        if (! empty($this->keys)) {
            foreach ($this->keys as $key) {
                // @codingStandardsIgnoreLine
                $value = \WcElectronInvoiceFree\Functions\filterInput($_POST, self::$metaKey . $key,
                    FILTER_UNSAFE_RAW);

                if (! empty($_POST[self::$metaKey . $key]) && $value) {
                    update_post_meta($orderID, self::$metaKey . $key, \WcElectronInvoiceFree\Functions\sanitize($value));
                }
            }
        }
    }
}
