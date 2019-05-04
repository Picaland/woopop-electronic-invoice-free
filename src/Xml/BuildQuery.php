<?php
/**
 * BuildQuery.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree\Xml
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

namespace WcElectronInvoiceFree\Xml;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\EndPoint\Endpoints;
use WcElectronInvoiceFree\Functions as F;

/**
 * Class BuildQuery
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class BuildQuery extends Endpoints
{
    /**
     * Xml Query
     *
     * @since  1.0.0
     *
     * @return null|\WP_Query
     */
    public function xmlQuery()
    {
        $wpQuery = F\getWpQuery();
        $query   = null;

        // @codingStandardsIgnoreLine
        $getFormat = F\filterInput($_GET, 'format', FILTER_SANITIZE_STRING);
        $format    = get_query_var('format');

        if ('json' === $format) {
            return null;
        }

        if ('prod' === WC_EL_INV_ENV) {
            if ('pdf' !== $format) {
                return null;
            }
            if ('' === $format || $format !== $getFormat) {
                return null;
            }
        }

        // Return, if wc-elc-inv is don't in query
        if (! isset($wpQuery->query['wc-elc-inv'])) {
            return null;
        }

        if (! isset($wpQuery->query['shop_order'])) {
            return null;
        }

        // Return, if post is null or in query
        if (null === $wpQuery->post && 1 < $wpQuery->post_count) {
            return null;
        }

        // Initialized tag
        $tag   = '';
        $idTag = '';

        // Get tag and reset unnecessary param from the query
        if (is_array($this->postType) && sizeof($this->postType) >= 0) {
            foreach ($this->postType as $type) {
                // Post type tag
                $tag = $wpQuery->get($type);
                // Post ID tag
                $idTag = $wpQuery->get($type . '_id');

                // Unset unnecessary query param
                unset($wpQuery->query[$type]);
                unset($wpQuery->query_vars[$type]);

                if ($idTag) {
                    unset($wpQuery->query[$type . '_id']);
                    unset($wpQuery->query_vars[$type . '_id']);
                }
            }
        } else {
            // Post type tag
            $tag = $wpQuery->get($this->postType);
            // Post ID tag
            $idTag = $wpQuery->get($this->postType . '_id');
        }

        // Esc if not $tag
        if (! $tag || $tag === '' || $this->postType === null) {
            return null;
        }

        // Explode $tag or empty array
        $arrayTag = strpos($tag, '/') ? explode('/', $tag) : array();
        // Set post type
        $postType = ! empty($arrayTag) ? reset($arrayTag) : $tag;
        // Set post id
        $idTag = ! empty($arrayTag) && '' === $idTag ? end($arrayTag) : $idTag;

        // Set current post type
        if (! empty($this->postType) && ! empty($arrayTag) && in_array(reset($arrayTag), $this->postType)) {
            // Set post type
            $wpQuery->set('post_type', $postType);
            $wpQuery->query['post_type'] = $postType;
        }

        // Set arguments for query
        switch ($postType) {
            case 'shop_order':
                $args = array(
                    'status'  => array('processing', 'completed', 'refunded'),
                    'limit'   => -1,
                    'orderby' => 'date',
                    'order'   => 'ASC',
                );

                if (isset($idTag) && '' !== $idTag) {
                    $args['order_id'] = intval($idTag);
                }
                break;
            default:
                $args = array();
                break;
        }

        // Set query
        $query = $this->setQuery($postType, $args);

        return $query;
    }

    /**
     * Set Query
     *
     * @since  1.0.0
     *
     * @param $tag
     * @param $args
     *
     * @return null|\WC_Order_Query|\WP_Query
     */
    public function setQuery($tag, $args)
    {
        $query = null;

        // switch lang.
        F\switchLang();

        switch ($tag) {
            case 'shop_order':
                if (! isset($args['order_id'])) {
                    $query = new \WC_Order_Query($args);
                } else {
                    $query = wc_get_order(intval($args['order_id']));
                }
                break;
            default:
                break;
        }

        // Query is null? return
        if (null === $query) {
            return null;
        }

        return $query;
    }
}
