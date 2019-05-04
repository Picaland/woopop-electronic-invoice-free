<?php
/**
 * JsonEndpoint.php
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

namespace WcElectronInvoiceFree\EndPoint;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Functions as F;

/**
 * Class Endpoints
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class Endpoints implements EndpointsInterface
{
    /**
     * Api endpoint
     *
     * @var string
     */
    const ENDPOINT = 'wc-inv';

    /**
     * Query var
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $var;

    /**
     * Post Type
     *
     * @since 1.0.0
     *
     * @var string The post type for this json
     */
    public $postType = array('shop_order');

    /**
     * JsonEndpoint constructor.
     *
     */
    public function __construct()
    {
        $this->var  = 'wc-elc-inv';
    }

    /**
     * Add EndPoint
     *
     * @return null
     */
    public function addEndPoint()
    {
        // Esc if empty post type & endpoint
        if (null === $this->postType && null === self::ENDPOINT) {
            return null;
        }

        if (is_array($this->postType) && sizeof($this->postType) >= 0) {
            // Multi post type
            foreach ($this->postType as $type) {
                $this->addRewriteRule(self::ENDPOINT, (string)$type);
            }
        } else {
            $this->addRewriteRule(self::ENDPOINT, (string)$this->postType);
        }

        flush_rewrite_rules();
    }

    /**
     * Add Query Vars
     *
     * @since 1.0.0
     *
     * @param $vars
     *
     * @return array
     */
    public function addQueryVars($vars)
    {
        $vars[] = $this->var;

        return $vars;
    }

    /**
     * Add Rewrite Rule
     *
     * @since 1.0.0
     *
     * @param $endpoint
     * @param $postType
     */
    public function addRewriteRule($endpoint, $postType)
    {
        // Add new rewrite tags to WP for our endpoint's post_type
        add_rewrite_tag('%' . $postType . '%', '([^&]+)');

        // and post_id arguments
        add_rewrite_tag('%' . $postType . '_id%', '([0-9]+)');

        // general rule finds "all" (post_per_page) of a given post_type
        add_rewrite_rule(
            $endpoint . '/([^&]+)/?$',
            'index.php?' . $postType . '=$matches[1]&' . $this->var . '=yes',
            'top'
        );

        // specific rule finds a single post
        add_rewrite_rule(
            $endpoint . '/([^&]+)/([0-9]+)/?$',
            'index.php?' . $postType . '=$matches[1]&' . $postType . '_id=$matches[2]&' . $this->var . '=yes',
            'top'
        );
    }

    /**
     * Add Rewrite Endpoint
     *
     * @since 1.0.0
     */
    public function addRewriteEndpoint()
    {
        add_rewrite_endpoint(self::ENDPOINT, EP_PAGES);
    }

    /**
     * Set Query Args
     *
     * @since 1.0.0
     *
     * @return null
     */
    public function setQueryArgs()
    {
        $query = F\getWpQuery();

        $format = F\filterInput($_GET, 'format', FILTER_SANITIZE_STRING);

        if (! $query) {
            return null;
        }

        // Set format
        if ($format) {
            $query->set('format', $format);
        }
    }
}
