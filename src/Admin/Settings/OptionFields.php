<?php
/**
 * OptionFields.php
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

namespace WcElectronInvoiceFree\Admin\Settings;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Plugin;
use function WcElectronInvoiceFree\Functions\checkOptions;

/**
 * Class OptionFields
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
abstract class OptionFields
{
    /**
     * Holds the values to be used in the fields callbacks
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $options;

    /**
     * Contains the arguments for add fields settings
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $sectionArgs = array();

    /**
     * Contains the arguments for add fields settings
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $fieldsArgs = array();

    /**
     * Set section and fields
     *
     * @since 1.0.0
     */
    protected function sectionFields()
    {
        include_once Plugin::getPluginDirPath('/inc/settings/pageSettingsFields.php');
    }

    /**
     * Add settings section and setting fields in an array
     *
     * @return array|bool $settings_and_fields The settings section and setting fields in an array
     * @since 1.0.0
     *
     */
    public function sectionAndFieldsOptions()
    {
        $sectionsAndFields = array();

        // Settings section arguments
        if (! empty($this->sectionArgs)) {
            foreach ($this->sectionArgs as $key => $section) {
                $sectionsAndFields['section'][$key] = $section;
            }

            // Settings fields arguments
            $sectionsAndFields['fields'] = $this->fieldsArgs;
            if (empty($sectionsAndFields)) {
                return false;
            }
        }

        return $sectionsAndFields;
    }

    /**
     * Print the Section text settings
     *
     * @since 1.0.0
     */
    public function sectionSettingsGeneralDescription()
    {
        echo sprintf('<div class="wc_el_inv__description wc_el_inv__description--general"><p><span class="dashicons dashicons-info"></span> %1$s</p><ol><li><a href="%2$s">%3$s</a> %4$s</li><li><a href="%5$s">%6$s</a> %7$s</li><li><a href="%8$s">%9$s</a> %10$s</li></ol></div>',
            esc_html__('After activating your license, set up the general options for e-invoice and the invoice and checkout options',
                WC_EL_INV_FREE_TEXTDOMAIN),
            esc_url(admin_url('admin.php?page=wc-settings')),
            esc_html__('General Options Invoice', WC_EL_INV_FREE_TEXTDOMAIN),
            checkOptions('general') ? '<span class="dashicons dashicons-yes-alt"></span>' : '<span class="dashicons dashicons-dismiss"></span>',
            esc_url(admin_url('admin.php?page=wc_el_inv-options-page&tab=invoice')),
            esc_html__('Invoice settings', WC_EL_INV_FREE_TEXTDOMAIN),
            checkOptions('invoice') ? '<span class="dashicons dashicons-yes-alt"></span>' : '<span class="dashicons dashicons-dismiss"></span>',
            esc_url(admin_url('admin.php?page=wc_el_inv-options-page&tab=wc-checkout')),
            esc_html__('General WooCommerce integration settings', WC_EL_INV_FREE_TEXTDOMAIN),
            checkOptions('checkout') ? '<span class="dashicons dashicons-yes-alt"></span>' : '<span class="dashicons dashicons-dismiss"></span>',
        );
    }
    /**
     * Print the Section text settings
     *
     * @since 1.0.0
     */
    public function fieldsVatRules()
    {
        $required    = esc_html__('required', WC_EL_INV_FREE_TEXTDOMAIN);
        $notRequired = esc_html__('not required', WC_EL_INV_FREE_TEXTDOMAIN);

        $output = '<table class="wc_el_inv-vat-rules wp-list-table widefat fixed striped">';
        $output .= '<thead>';
        $output .= '<tr>';
        $output .= '<td width="18%"><strong>' . esc_html__('Customer Country', WC_EL_INV_FREE_TEXTDOMAIN) . '</strong></td>';
        $output .= '<td width="64%"><strong>' . esc_html__('The user selects: Company or Freelance',
                WC_EL_INV_FREE_TEXTDOMAIN) . '</strong></td>';
        $output .= '<td width="18%"><strong>' . esc_html__('The user selects: Private',
                WC_EL_INV_FREE_TEXTDOMAIN) . '</strong></td>';
        $output .= '<tr>';
        $output .= '</thead>';
        $output .= '<tbody>';
        $output .= '<tr>';
        $output .= '<td><strong>' . esc_html__('IT', WC_EL_INV_FREE_TEXTDOMAIN) . '</strong></td>';
        $output .= '<td>' . sprintf(__('VAT number or Tax Code and E-mail (PEC) or the web-service code: %1$s',
                WC_EL_INV_FREE_TEXTDOMAIN),
                "<strong class='req'>{$required}</strong>"
            ) . '</td>';
        $output .= '<td>' . sprintf("<strong class='req'>%s %s</strong>", $required,
                esc_html__('(Tax Code)', WC_EL_INV_FREE_TEXTDOMAIN)) . '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td><strong>' . esc_html__('UE', WC_EL_INV_FREE_TEXTDOMAIN) . '</strong></td>';
        $output .= '<td>' . sprintf(__('VAT number or Tax Code: %1$s',
                WC_EL_INV_FREE_TEXTDOMAIN),
                "<strong class='not-req'>{$notRequired}</strong>"
            ) . '</td>';
        $output .= '<td>' . sprintf("<strong class='not-req'>%s</strong>", $notRequired) . '</td>';
        $output .= '</tr>';
        $output .= '<tr>';
        $output .= '<td><strong>' . esc_html__('Extra UE', WC_EL_INV_FREE_TEXTDOMAIN) . '</strong> ';
        $output .= '<small><abbr title="' . esc_html__('View (1)', WC_EL_INV_FREE_TEXTDOMAIN) . '">(1)</abbr></small></td>';
        $output .= '<td>' . sprintf(__('VAT number or Tax Code: %2$s',
                WC_EL_INV_FREE_TEXTDOMAIN),
                "<strong class='req'>{$required}</strong>",
                "<strong class='not-req'>{$notRequired}</strong>"
            ) . ' ';
        $output .= '</td>';
        $output .= '<td>' . sprintf("<strong class='not-req'>%s</strong>", $notRequired) . '</td>';
        $output .= '</tr>';
        $output .= '</tbody>';
        $output .= '</table>';

        echo $output;
    }

    /**
     * changelog page
     *
     * @return void
     */
    public function changelog()
    {
        // Close current table
        $output = '</tr></tbody></table>';
        $output .= '<style>.form-table{display:none;}</style>';
        $output .= '<!-- changelog_content --><div class="changelog_content">';

        // Current version
        $output .= '<ul>';
        $output .= sprintf('<li><strong>%s:</strong> %s</li>',
            __('Versione corrente', WC_EL_INV_FREE_TEXTDOMAIN),
            WC_EL_INV_FREE_VERSION
        );

        $output .= '<hr>';
        $output .= '<p><strong>Changelog completo consultabile <a target="_blank" href="' . WC_EL_INV_FREE_URL . 'changelog.txt">qui</a></strong></p>';
        // Current version

        $output .= '</div><!--/ changelog_content -->';

        echo $output;
    }
}
