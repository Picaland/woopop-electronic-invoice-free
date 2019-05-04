<?php
/**
 * XmlOrdersList.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree\Admin\Settings\Fields
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

namespace WcElectronInvoiceFree\Admin\Settings\Fields;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Admin\XmlOrderListTable;
use WcElectronInvoiceFree\EndPoint\Endpoints;
use WcElectronInvoiceFree\Utils\TimeZone;

/**
 * Class XmlOrdersList
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class XmlOrdersList
{
    /**
     * List type
     *
     * @since 1.0.0
     */
    const LIST_TYPE = 'shop_order';

    /**
     * XmlOrdersList constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Init
     *
     * @since 1.0.0
     */
    private function init()
    {
        add_action('wc_el_inv-after_settings_form', array($this, 'template'));
    }

    /**
     * Summary data
     *
     * @return object
     */
    private function summaryData()
    {
        //  Initialized
        $sentCount   = 0;
        $noSentCount = 0;

        if (! empty(XmlOrderListTable::$listIds)) {
            foreach (XmlOrderListTable::$listIds as $postID) {
                $sent = get_post_meta($postID, '_invoice_sent', true);
                switch ($sent) {
                    case 'sent':
                        $sentCount++;
                        break;
                    case 'no_sent':
                        $noSentCount++;
                        break;
                    default:
                        break;
                }
            }
        }

        return (object)array(
            'sent'   => $sentCount,
            'noSent' => $noSentCount,
        );
    }

    /**
     * Filter customer
     *
     * @since 1.0.0
     */
    private function filterSelects()
    {
        // @codingStandardsIgnoreLine
        $customer    = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'customer_id', FILTER_SANITIZE_STRING);
        $type        = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'type', FILTER_SANITIZE_STRING);
        $current     = null;
        $currentType = null;
        if (isset($customer) && '' !== $customer) {
            $current = intval($customer);
        }

        if (isset($type) && '' !== $type) {
            $currentType = $type;
        }

        $options = \WcElectronInvoiceFree\Functions\getCustomersList();
        if (empty($options)) {
            return;
        }

        echo '<label for="filter_customer_id">';
        echo esc_html__('Users:', WC_EL_INV_FREE_TEXTDOMAIN);
        echo '<select id="filter_customer_id" name="filter_customer_id">';
        echo "<option value=''>" . esc_html__('All', WC_EL_INV_FREE_TEXTDOMAIN) . "</option>";
        foreach ($options as $key => $option) {
            $selected = $current === $key ? 'selected="selected"' : '';
            echo "<option {$selected} value='{$key}'>{$option}</option>";
        }
        echo '</select>';
        echo '</label>';

        $selectOptions = array(
            'shop_order'        => esc_html__('Invoice', WC_EL_INV_FREE_TEXTDOMAIN),
            'shop_order_refund' => esc_html__('Refund', WC_EL_INV_FREE_TEXTDOMAIN),
        );

        echo '<label for="filter_type">';
        echo esc_html__('Type:', WC_EL_INV_FREE_TEXTDOMAIN);
        echo '<select id="filter_type" name="filter_type">';
        echo "<option value=''>" . esc_html__('All', WC_EL_INV_FREE_TEXTDOMAIN) . "</option>";
        foreach ($selectOptions as $key => $option) {
            $selected = $currentType === $key ? 'selected="selected"' : '';
            echo "<option {$selected} value={$key}>{$option}</option>";
        }
        echo '</select>';
        echo '</label>';
    }

    /**
     * @return string
     */
    public function buttonBulkAction()
    {

        $output = '<div class="actions-sent-bulk">';
        $output .= sprintf('<h4>%s <strong style="color:red;">%s</strong></h4>',
            esc_html__('Actions Bulk', WC_EL_INV_FREE_TEXTDOMAIN), WC_EL_INV_PREMIUM);
        $output .= sprintf('<button id="mark_as_sent-bulk-undo" name="bulk-sent" value="no_sent" class="mark_bulk_trigger disabled mark_undo button button-secondary" type="submit">' .
                           '%s <span class="dashicons dashicons-undo"></span></button>',
            esc_html__('Mark no Sent', WC_EL_INV_FREE_TEXTDOMAIN));
        $output .= sprintf('<button id="mark_as_sent-bulk-sent" name="bulk-sent" value="sent" class="mark_bulk_trigger disabled mark_as_sent button button-secondary" type="submit">' .
                           '%s <span class="dashicons dashicons-yes"></span></button>',
            esc_html__('Mark as Sent', WC_EL_INV_FREE_TEXTDOMAIN));
        $output .= '</div>';

        echo wp_kses_post($output);
    }

    /**
     * Bulk Actions
     *
     * @since 1.1.0
     */
    private function bulkActions()
    {
        // @codingStandardsIgnoreLine
        $customer = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'customer_id', FILTER_SANITIZE_STRING);
        $dateIN   = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_in', FILTER_SANITIZE_STRING);
        $dateOUT  = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_out', FILTER_SANITIZE_STRING);
        $current  = null;
        $output   = '';

        // DatePicker scripts.
        wp_script_is('wc_el_inv_datepicker', 'registered') ? wp_enqueue_script('wc_el_inv_datepicker') : null;
        wp_script_is('datepicker-lang', 'registered') ? wp_enqueue_script('datepicker-lang') : null;

        if (isset($customer) && '' !== $customer) {
            $current  = intval($customer);
            $customer = "customer_id={$current}";
        }

        $key = get_option('wc_el_inv-secret-api-key');
        // Query args
        $QueryArgs = "?key={$key}&{$customer}&format=xml";
        if ('dev' === WC_EL_INV_ENV) {
            $QueryArgs = isset($customer) ? "?{$customer}&format=xml" : "?format=xml";
        }

        // Endpoint
        $url = site_url() . '/' . Endpoints::ENDPOINT . '/' . self::LIST_TYPE . '/' . $QueryArgs;
        // Admin page
        $adminUrl = admin_url() . "admin.php?page=wc_el_inv-options-page&tab=xml";

        $output .= ' <div class="bulk-actions">';

        $output .= sprintf('<label class="bulk-actions-label">%s</label>',
            esc_html__('Filter by date: (Matches on orders created during or in between the seconds)',
                WC_EL_INV_FREE_TEXTDOMAIN)
        );

        $output .= sprintf('<span> %s <input type="text" value="%s" id="date_in" class="wc_el_inv-datepicker"></span>',
            esc_html__('From: ', WC_EL_INV_FREE_TEXTDOMAIN),
            isset($dateIN) && false !== $dateIN ? date('Y-m-d', $dateIN) : ''
        );
        $output .= sprintf('<span> %s <input type="text" value="%s" id="date_out" class="wc_el_inv-datepicker"></span>',
            esc_html__('To: ', WC_EL_INV_FREE_TEXTDOMAIN),
            isset($dateOUT) && false !== $dateOUT ? date('Y-m-d', $dateOUT) : ''
        );

        $output .= '<span class="actions-button">';
        // Filter
        $output .= sprintf(
            '<a class="button button-secondary filter" href="%1$s" title="%2$s">' .
            '<span class="dashicons dashicons-filter"></span> %2$s</a>',
            esc_url($adminUrl),
            esc_html__('Filter', WC_EL_INV_FREE_TEXTDOMAIN)
        );

        $output .= sprintf(
            '<a class="button button-secondary disabled get-all" target="_blank" href="#" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-editor-ul"></span></a>',
            esc_html__('Get all Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        $output .= sprintf(
            '<a class="button button-primary disabled view-all" target="_blank" href="#" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-visibility"></span></a>',
            esc_html__('View all Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );

        $output .= sprintf(
            '<a class="button button-secondary disabled button-save save-all" target="_blank" href="#" title="%1$s %2$s">' .
            '<span class="dashicons dashicons-media-code"></span></a>',
            esc_html__('Save all Xml', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_PREMIUM
        );
        $output .= '<span>';

        $output .= '</div>';

        echo $output;
    }

    private function notice()
    {
        // Notice no found or disabled view
        $foundOrder   = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'found_order', FILTER_SANITIZE_STRING);
        $disabledView = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'disabled_view', FILTER_SANITIZE_STRING);

        if (isset($foundOrder) && 'no' === $foundOrder) : ?>
            <div class="notice notice-error is-dismissible">
                <p><span class="dashicons dashicons-no"></span>
                    <?php esc_html_e(
                        'No order found!',
                        WC_EL_INV_FREE_TEXTDOMAIN
                    ); ?>
                </p>
            </div>
        <?php endif;

        if (isset($disabledView) && 'yes' === $disabledView) : ?>
            <div class="notice notice-error is-dismissible">
                <p><span class="dashicons dashicons-no"></span>
                    <?php esc_html_e(
                        'The display of all xml is disabled. first filter orders by user and / or dates.',
                        WC_EL_INV_FREE_TEXTDOMAIN
                    ); ?>
                </p>
            </div>
        <?php endif;
    }

    /**
     * Xml list template
     *
     * @since 1.1.0
     */
    public function template()
    {
        $xmlOrdersListTable = new XmlOrderListTable(array());
        $orders             = $xmlOrdersListTable->getOrders();

        $dateIN  = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_in', FILTER_SANITIZE_STRING);
        $dateOUT = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'date_out', FILTER_SANITIZE_STRING);

        // Notice
        $this->notice();
        // List table.
        echo '<div class="wrap">';
        echo '<form id="wp-list-table-invoice-form" method="post">';
        if (! $dateIN && ! $dateOUT) {
            try {
                $timeZone = new TimeZone();
                $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
                $now      = new \DateTime('now');
                $now->setTimezone($timeZone);

                $before = date_i18n('d, M Y', $now->getTimestamp());
                $now->modify("-1 month");
                $after = date_i18n('d, M Y', $now->getTimestamp());
                echo sprintf('<h4 class="invoice_range">(*) %s <strong>%s</strong> %s <strong>%s</strong></h4>',
                    esc_html__('You are viewing invoices after the', WC_EL_INV_FREE_TEXTDOMAIN),
                    $after,
                    esc_html__('until', WC_EL_INV_FREE_TEXTDOMAIN),
                    $before
                );
            } catch (\Exception $e) {
            }
        }

        echo '<div class="filter-line">';
        echo '<div class="filter-selects">';
        $this->filterSelects();
        echo '</div>';
        $this->bulkActions();
        $this->buttonBulkAction();
        echo '</div>';

        echo '<div class="summary-data">';
        $summaryTitle = esc_html__('Summary', WC_EL_INV_FREE_TEXTDOMAIN);
        echo "<h3>{$summaryTitle}</h3>";
        $data        = $this->summaryData();
        $labelLines  = esc_html__('Totals:', WC_EL_INV_FREE_TEXTDOMAIN);
        $labelSent   = esc_html__('Sent:', WC_EL_INV_FREE_TEXTDOMAIN);
        $labelNoSent = esc_html__('No Sent:', WC_EL_INV_FREE_TEXTDOMAIN);
        $lineCount   = count(XmlOrderListTable::$listIds);
        echo "<p class='count-sent'>{$labelLines}<strong>{$lineCount}</strong></p>";
        echo "<p class='count-sent'>{$labelSent}<strong style='color:red;'>" . WC_EL_INV_PREMIUM . "</strong></p>";
        echo "<p class='count-no-sent'>{$labelNoSent}<strong style='color:red;'>" . WC_EL_INV_PREMIUM . "</strong></p>";
        echo '</div>';
        $xmlOrdersListTable->prepare_items();
        $xmlOrdersListTable->display();
        echo '</form>';
        echo '</div>'; // wrap
    }
}
