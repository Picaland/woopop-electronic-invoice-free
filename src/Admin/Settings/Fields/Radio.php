<?php
/**
 * Radio.php
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

use WcElectronInvoiceFree\Admin\Settings\OptionFields;
use WcElectronInvoiceFree\Admin\Settings\OptionPage;

/**
 * Class Radio
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class Radio extends Text
{
    /**
     * Allow Html
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $allowHtml = array();

    /**
     * Radio constructor.
     *
     * @since 1.0.0
     *
     * @param              $args
     * @param OptionFields $fields
     * @param OptionPage   $page
     */
    public function __construct($args, OptionFields $fields, OptionPage $page)
    {
        $args = wp_parse_args($args, array(
            'options' => array(),
            'type'    => 'radio',
        ));

        $this->allowHtml = array(
            'label' => array(
                'for' => true,
            ),
            'input' => array(
                'class'    => true,
                'type'     => true,
                'id'       => true,
                'name'     => true,
                'value'    => true,
                'checked'  => true,
                'disabled' => true,
                'required' => true,
            ),
            'p'     => true,
            'br'    => true,
            'small' => true,
            'ul'    => true,
            'li'    => true,
        );

        parent::__construct($args, $fields, $page);
    }

    /**
     * Escape
     *
     * @since  1.0.0
     *
     * @param null $value
     *
     * @return string string The escaped value of this type
     */
    public function escape($value = null)
    {
        $value = $value ?: $this->getValue();

        return esc_html($value);
    }

    /**
     * Get Saved Value
     *
     * @since  1.0.0
     *
     * @return string
     */
    private function getValue()
    {
        $options  = $this->fields->options;
        $newValue = isset($options[$this->args['name']]) ? $options[$this->args['name']] : '';

        return esc_html($newValue);
    }

    /**
     * Output Field
     *
     * @since 1.0.0
     */
    public function field()
    {
        $labelBefore = '';
        $labelAfter  = '';
        $description = '';

        if (empty($this->args['options'])) {
            return '';
        }

        $radios = array();
        $c      = 1;

        if (isset($this->args['description'])) {
            $description = sprintf('<p><small>%s</small></p>',
                esc_html__($this->args['description'], WC_EL_INV_FREE_TEXTDOMAIN));
        }

        foreach ($this->args['options'] as $value => $label) {
            $labelString = esc_html__($label, WC_EL_INV_FREE_TEXTDOMAIN);
            if (isset($this->args['label'])) {
                $labelBefore = sprintf('<label for="%s">', esc_attr($this->args['id'] . '-' . $c));
                $labelAfter  = sprintf('%s</label>', $labelString);
            }

            array_push($radios, sprintf(
                '<li>%1$s <input type="%2$s" name="%3$s[%4$s]" id="%5$s" value="%6$s" %7$s/>%8$s</li>',
                $labelBefore,
                sanitize_key($this->args['type']),
                $this->page->optionsName,
                esc_attr($this->args['name']),
                esc_attr($this->args['id'] . '-' . $c),
                $this->escape($value),
                checked($this->escape(), $this->escape($value), false),
                $labelAfter
            ));

            ++$c;

            $output = '<ul>' . implode("\n", $radios) . '</ul>' . $description;
        }

        /**
         * Output Filter
         *
         * @since 1.0.0
         *
         * @param string $output The output of the input type.
         * @param Radio The instance class
         */
        $output = apply_filters('wc_el_inv-input_radio_output', $output, $this);

        /**
         * Before input hook
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-input_radio_before_input_action', $this->args);

        echo wp_kses($output, $this->allowHtml);

        /**
         * After input hook
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-input_radio_after_input_action', $this->args);
    }
}
