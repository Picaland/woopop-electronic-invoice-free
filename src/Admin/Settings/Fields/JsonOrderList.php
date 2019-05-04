<?php
/**
 * JsonList.php
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

use WcElectronInvoiceFree\EndPoint\Endpoints;

/**
 * Class JsonList
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class JsonOrderList
{
    /**
     * List type
     *
     * @since 1.0.0
     */
    const LIST_TYPE = 'shop_order';

    /**
     * EndPoint
     *
     * @since 1.0.0
     *
     * @var string endpoint
     */
    public $endpoint;

    /**
     * Post Type
     *
     * @since 1.0.0
     *
     * @var array The post type list
     */
    public $postType;

    /**
     * JsonList constructor.
     *
     * @param Endpoints $endpoint
     */
    public function __construct(Endpoints $endpoint)
    {
        $this->postType = (array)$endpoint->postType;
        $this->endpoint = $endpoint::ENDPOINT;
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
     * Json list template
     */
    public function template()
    {
        // List table.
        echo '<div class="wrap">';
        echo '<h2 style="color:red;">'.WC_EL_INV_PREMIUM.'</h2>';
        echo '</div>';
    }
}
