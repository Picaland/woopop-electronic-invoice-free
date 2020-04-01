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
     * @since 1.0.0
     *
     * @param array      $fields
     * @param OptionPage $options
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
     * @since 1.0.0
     *
     * @param $fields
     *
     * @return mixed
     */
    public function billingAddressFields($fields)
    {
        $invoiceFields = array();
        $priority      = 150;

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

        $fields = array_merge($fields, $invoiceFields);

        return $fields;
    }

    /**
     * Admin User billing fields
     *
     * @since 1.0.0
     *
     * @param $fields
     *
     * @return mixed
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
     * @since 1.0.0
     *
     * @param $fields
     *
     * @return mixed
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
     * @since 1.0.0
     *
     * @param $actions
     * @param $order
     *
     * @return mixed
     */
    public static function actionsFront($actions, $order)
    {
        // View invoice pdf link in my orders
        $myOrdersInvoice = OptionPage::init()->getOptions('invoice_in_my_orders');

        $nonce   = wp_create_nonce('wc_el_inv_invoice_pdf');
        $pdfArgs = "?format=pdf&nonce={$nonce}";
        if ('dev' === WC_EL_INV_ENV) {
            $pdfArgs = "?format=pdf";
        }

        $url = site_url() . '/' . Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/';

        if (method_exists($order, 'get_status') &&
            'on' === $myOrdersInvoice &&
            'no' === apply_filters('wc_el_inv-disable_my_orders_pdf_invoice', 'no')
        ) {
            if ('completed' === $order->get_status()) {
                $actions['invoice'] = array(
                    'url'  => esc_url($url . $order->get_id() . $pdfArgs),
                    'name' => apply_filters('wc_el_inv-invoice_my_account_button_text',
                        esc_html__('View PDF', WC_EL_INV_FREE_TEXTDOMAIN)
                    ),
                );
            }
        }

        return $actions;
    }

    /**
     * Get Actions
     *
     * @since 1.0.0
     *
     * @param $id
     * @param $order
     *
     * @return string
     */
    public static function actions($id, $order)
    {
        $output     = '';
        $nonce      = wp_create_nonce('wc_el_inv_invoice_pdf');
        $pdfArgs    = "?format=pdf&nonce={$nonce}";
        $url        = site_url() . '/' . Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/';
        $choiceType = $order->get_meta('_billing_choice_type');

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
            $pdfArgs . "&choice_type={$choiceType}",
            esc_html__('View PDF', WC_EL_INV_FREE_TEXTDOMAIN)
        );

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
     * @since 1.0.0
     *
     * @return null
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
     * @since 1.0.0
     *
     * @param $order \WC_Order
     */
    public function editGeneralFieldsFilter($order)
    {
        if (! is_a($order, 'WC_Order')) {
            return;
        }

        if ('completed' !== $order->get_status() && 'processing' !== $order->get_status()) {
            return;
        }

        // No It return
        if (isset($order->billing['country']) && 'IT' !== $order->billing['country']) {
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
        echo '<p class="actions">';
        echo self::actions($order->get_id(), $order);
        echo '</p>';
        echo '</div></div>';
    }

    /**
     * Edit refund invoice data
     *
     * @since 1.0.0
     *
     * @param $orderID
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

        if (is_a($order, 'WC_Order')) {

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
                    if (! $refund instanceof \WC_Order_Refund) {
                        return;
                    }

                    // Get refund sent meta
                    $invoiceRefundSentTime = get_post_meta($refund->get_id(), '_invoice_sent_timestamp', true);
                    $invoiceRefundSentTime = isset($invoiceRefundSentTime) && '' !== $invoiceRefundSentTime ? $invoiceRefundSentTime : false;
                    if ($checkOrderSent && (intval($checkOrderSentTime) > intval($invoiceRefundSentTime))) {
                        continue;
                    }

                    if (! $checkOrderSent) {
                        continue;
                    }

                    // Check Refund sent.
                    $refundSent = get_post_meta($refund->get_id(), '_invoice_sent', true);
                    if (count($refunds) > 1) {
                        if ('no_sent' === $refundSent && isset($refunds[$index - 1]) && $refunds[$index - 1] instanceof \WC_Order_Refund) {
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
                        '<p class="wc_el_inv__refund-invoice--text-data"><strong>%s:</strong> %s<br><strong>%s:</strong> %s</p><p class="actions">%s</p>',
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
                    echo '</tr>';
                }
            }
        }
    }

    /**
     * Refunded payment method
     *
     * @since 1.0.0
     *
     * @param $orderID
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

        if (is_a($order, 'WC_Order')) {
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
                        'MP01' => esc_html__('cash', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP02' => esc_html__('check', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP03' => esc_html__('circular check', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP05' => esc_html__('transfer', WC_EL_INV_FREE_TEXTDOMAIN),
                        'MP08' => esc_html__('payment card', WC_EL_INV_FREE_TEXTDOMAIN),
                    ),
                ));
                echo '</td></tr>';
            }
        }
    }

    /**
     * Admin Order view billing details
     *
     * @since 1.0.0
     *
     * @param $order
     */
    public function viewBillingFieldsFilter($order)
    {
        $orderData = $order->get_data();
        $output    = $type = '';

        if (! empty($this->fields)) {
            foreach ($this->fields as $key => $field) {
                $key = str_replace('billing_', '', $key);

                $meta = $this->getMeta($order, $key);

                if (! in_array($orderData['billing']['country'], self::$euVatCountry, true)) {
                    return;
                }

                if ('invoice_type' === $key && 'private' === $meta) {
                    $type = $meta;
                }

                if ('private' === $type && ('sdi_type' === $key || 'vat_number' === $key)) {
                    continue;
                }

                if ('sdi_type' === $key && '' === $meta) {
                    $meta = '0000000';
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

                $output .= sprintf('<p><strong>%s:</strong><br>%s</p>',
                    strtoupper($field['label']),
                    strtoupper($meta)
                );
            }

            echo $output;
        }
    }

    /**
     * Order Formatted Billing Address
     *
     * @since 1.0.0
     *
     * @param $fields
     * @param $order
     *
     * @return array
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
     * @since 1.0.0
     *
     * @param $fields
     * @param $customerID
     * @param $type
     *
     * @return mixed
     */
    public function myAccountFormattedAddress($fields, $customerID, $type)
    {
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
     * @since 1.0.0
     *
     * @param $address
     * @param $args
     *
     * @return mixed
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

                $address["{{$key}}"] = $value;
            }
        }

        if (! array_key_exists('{sdi_type}', $address) || '' === $address["{sdi_type}"]) {
            $address['{sdi_type}'] = "0000000";
        }

        if (! array_key_exists('{tax_code}', $address) || '' === $address["{tax_code}"]) {
            $address['{tax_code}'] = "***";
        }

        return $address;
    }

    /**
     * Localization Address Format
     *
     * @since 1.0.0
     *
     * @param $formats
     *
     * @return mixed
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
     * @since 1.0.0
     *
     * @param $customerData
     * @param $customer
     * @param $userID
     *
     * @return mixed
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
     * @since 1.0.0
     *
     * @param        $order
     * @param        $key
     * @param string $default
     *
     * @return mixed|string
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
                FILTER_SANITIZE_STRING
            );

            // @codingStandardsIgnoreLine
            $taxCode = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'tax_code',
                FILTER_SANITIZE_STRING
            );

            // @codingStandardsIgnoreLine
            $sdi = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'sdi_type',
                FILTER_SANITIZE_STRING
            );

            // @codingStandardsIgnoreLine
            $choiceDocType = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'choice_type',
                FILTER_SANITIZE_STRING
            );

            // @codingStandardsIgnoreLine
            $invoiceType = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                self::$metaKey . 'invoice_type',
                FILTER_SANITIZE_STRING
            );

            // @codingStandardsIgnoreLine
            $country = \WcElectronInvoiceFree\Functions\filterInput(
                $_POST,
                'billing_country',
                FILTER_SANITIZE_STRING
            );

            // No check if hide the VAT number and tax code field.
            if (! in_array($country, self::$euVatCountry, true) && 'hide' === $hideOutUe && '' !== $userCountry) {
                return true;
            }

            // Tax code length
            $vatCodeLength = isset($vatCode) ? strlen($vatCode) : 0;

            // Invoice IT
            if ('IT' === $country && 'company' === $invoiceType || 'freelance' === $invoiceType) {

                // Vat check
                if (false === $choiceDocType || 'invoice' === $choiceDocType) {
                    foreach ($users as $user) {
                        $vat            = get_user_meta($user->ID, 'billing_vat_number', true);
                        $currentUserVat = get_user_meta(get_current_user_id(), 'billing_vat_number', true);

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
                        if ('on' !== $disableTaxCode && empty($taxCode)) {
                            wc_add_notice(__('Please enter your Tax Code', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
                        }

                        if ('on' !== $disableTaxCode || empty($vatCode) || strlen($vatCode) < 8) {
                            wc_add_notice(__('Please enter your VAT number', WC_EL_INV_FREE_TEXTDOMAIN), 'error');
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
                    } elseif ('private' === $invoiceType) {
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

            // Check all key
            foreach ($this->keys as $key) {
                // @codingStandardsIgnoreLine
                $value = \WcElectronInvoiceFree\Functions\filterInput(
                    $_POST,
                    self::$metaKey . $key,
                    FILTER_SANITIZE_STRING
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
     * @since 1.0.0
     *
     * @param $orderID
     */
    public function saveOrderMetaBox($orderID)
    {
        $order = wc_get_order($orderID);

        if (! $order instanceof \WC_Order) {
            return;
        }

        // @codingStandardsIgnoreLine
        $number  = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_number_invoice',
            FILTER_SANITIZE_NUMBER_INT);
        $date    = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_date_invoice', FILTER_SANITIZE_STRING);
        $hour    = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_hours_invoice', FILTER_SANITIZE_STRING);
        $minute  = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'order_minutes_invoice',
            FILTER_SANITIZE_STRING);
        $payment = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'refund_payment_method',
            FILTER_SANITIZE_STRING);

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
     * @since 1.0.0
     *
     * @param $orderID
     */
    public function saveRefundMetaBox($orderID)
    {
        $order = wc_get_order($orderID);

        if (! $order instanceof \WC_Order) {
            return;
        }

        // Get Refunds
        $refunds = $order->get_refunds();
        if (! empty($refunds)) {
            foreach ($refunds as $refund) {
                // Order refund
                if (! $refund instanceof \WC_Order_Refund) {
                    return;
                }

                $refundID = $refund->get_id();

                // @codingStandardsIgnoreLine
                $number = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_number_invoice-{$refundID}",
                    FILTER_SANITIZE_NUMBER_INT);
                $date   = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_date_invoice-{$refundID}",
                    FILTER_SANITIZE_STRING);
                $hour   = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_hours_invoice-{$refundID}",
                    FILTER_SANITIZE_STRING);
                $minute = \WcElectronInvoiceFree\Functions\filterInput($_POST, "refund_minutes_invoice-{$refundID}",
                    FILTER_SANITIZE_STRING);

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
     * @since 1.0.0
     *
     * @param $orderID
     */
    public function store($orderID)
    {
        if (! empty($this->keys)) {
            foreach ($this->keys as $key) {
                // @codingStandardsIgnoreLine
                $value = \WcElectronInvoiceFree\Functions\filterInput($_POST, self::$metaKey . $key,
                    FILTER_SANITIZE_STRING);

                if (! empty($_POST[self::$metaKey . $key]) && $value) {
                    update_post_meta($orderID, self::$metaKey . $key,
                        \WcElectronInvoiceFree\Functions\sanitize($value));
                }
            }
        }
    }
}
