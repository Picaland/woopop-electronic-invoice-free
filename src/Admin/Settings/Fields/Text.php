<?php
/**
 * Text.php
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
 * Class Text
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class Text
{
    /**
     * Args for fields
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $args;

    /**
     * OptionFields
     *
     * @since 1.0.0
     *
     * @var
     */
    public $fields;

    /**
     * Page
     *
     * @since 1.0.0
     *
     * @var
     */
    public $page;

    /**
     * Allow Html
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $allowHtml = array();

    /**
     * Text constructor.
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
            'type'           => 'text',
            'name'           => 'wc_el_inv-text_field',
            'id'             => 'wc_el_inv-text_field',
            'class'          => '',
            'label'          => '',
            'description'    => '',
            'placeholder'    => '',
            'filter'         => FILTER_UNSAFE_RAW,
            'filter_options' => array(
                'flags' => FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_ENCODE_AMP,
            ),
            'attrs'          => array(),
        ));

        $this->allowHtml = array(
            'label' => array(
                'for' => true,
            ),
            'input' => array(
                'class'       => true,
                'placeholder' => true,
                'type'        => true,
                'name'        => true,
                'id'          => true,
                'value'       => true,
                'disabled'    => true,
                'required'    => true,
                // For Input Number.
                'min'         => true,
                'max'         => true,
            ),
            'p'     => true,
            'br'    => true,
            'small' => true,
            'ul'    => true,
            'li'    => true,
        );

        $this->args   = $args;
        $this->fields = $fields;
        $this->page   = $page;
    }

    /**
     * Escape
     *
     * @since  1.0.0
     *
     * @return string The escaped value of this type
     */
    public function escape()
    {
        $value = isset($this->fields->options[$this->args['name']]) ? $this->fields->options[$this->args['name']] : '';

        return esc_html($value);
    }

    /**
     * Get Attrs
     *
     * @since  1.0.0
     *
     * @param $attrs array The attributes Key|Value
     *
     * @return string
     */
    public function getAttr($attrs)
    {
        $att = '';
        if (! empty($attrs)) {
            foreach ($attrs as $key => $value) {
                $att .= "{$key}='{$value}' ";
            }
        }

        return $att;
    }

    /**
     * Output Field
     *
     * @since 1.0.0
     */
    public function field()
    {
        $output      = '';
        $labelBefore = '';
        $labelAfter  = '';
        $description = '';

        if (isset($this->args['label'])) {
            $labelBefore = sprintf('<label for="%s">%s', esc_attr($this->args['id']),
                esc_html__($this->args['label'], WC_EL_INV_FREE_TEXTDOMAIN));
            $labelAfter  = '</label>';
        }

        if (isset($this->args['description'])) {
            $description = sprintf('<br><small>%s</small>',
                esc_html__($this->args['description'], WC_EL_INV_FREE_TEXTDOMAIN));
        }

        $output .= sprintf(
            '%1$s<input class="%2$s" placeholder="%3$s" type="%4$s" name="%5$s[%6$s]" id="%7$s" value="%8$s" %9$s/>%10$s %11$s',
            $labelBefore,
            esc_attr($this->args['class']),
            esc_attr($this->args['placeholder']),
            sanitize_key($this->args['type']),
            $this->page->optionsName,
            esc_attr($this->args['name']),
            esc_attr($this->args['id']),
            $this->escape(),
            $this->getAttr($this->args['attrs']),
            $labelAfter,
            $description
        );

        /**
         * Output Filter
         *
         * @since 1.0.0
         *
         * @param string $output The output of the input type.
         * @param Text The instance class
         */
        $output = apply_filters('wc_el_inv-input_text_output', $output, $this);

        /**
         * Before input hook
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-input_text_before_input_action', $this->args);

        echo wp_kses($output, $this->allowHtml);

        /**
         * After input hook
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-input_text_after_input_action', $this->args);
    }
}