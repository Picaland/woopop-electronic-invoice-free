<?php
/**
 * TextArea.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree\Admin\Settings\Fields
 * @author     alfiopiccione <alfio.piccione@gmail.com>
 * @copyright  Copyright (c) 2019, alfiopiccione
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2
 *
 * Copyright (C) 2019 alfiopiccione <alfio.piccione@gmail.com>
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
 * Class TextArea
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class TextArea extends Text
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
     * TextArea constructor.
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
            'type' => 'textarea',
        ));

        $this->allowHtml = array(
            'label'    => array(
                'for' => true,
            ),
            'textarea' => array(
                'class'       => true,
                'placeholder' => true,
                'name'        => true,
                'id'          => true,
                'disabled'    => true,
                'required'    => true,
            ),
            'p'        => true,
            'br'       => true,
            'small'    => true,
            'ul'       => true,
            'li'       => true,
            'strong'   => true,
        );

        parent::__construct($args, $fields, $page);
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

        return wp_kses($value, array(
            'p'      => true,
            'br'     => true,
            'small'  => true,
            'ul'     => true,
            'li'     => true,
            'strong' => true,
        ));
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
            '%1$s<textarea class="%2$s" placeholder="%3$s" name="%4$s[%5$s]" id="%6$s" %7$s>%8$s</textarea>%9$s %10$s',
            $labelBefore,
            esc_attr($this->args['class']),
            esc_attr($this->args['placeholder']),
            $this->page->optionsName,
            esc_attr($this->args['name']),
            esc_attr($this->args['id']),
            $this->getAttr($this->args['attrs']),
            $this->escape(),
            $labelAfter,
            $description
        );

        /**
         * Output Filter
         *
         * @since 1.0.0
         *
         * @param string $output The output of the input type.
         * @param TextArea The instance class
         */
        $output = apply_filters('wc_el_inv-input_textarea_output', $output, $this);

        /**
         * Before input hook
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-input_textarea_before_input_action', $this->args);

        echo wp_kses($output, $this->allowHtml);

        /**
         * After input hook
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-input_textarea_after_input_action', $this->args);
    }
}
