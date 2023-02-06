<?php
/**
 * Helpers.php
 *
 * @since      1.0.0
 * @author     alfiopiccione <alfio.piccione@gmail.com>
 * @copyright  Copyright (c) 2020, alfiopiccione
 * @license    http://opensource.org/licenses/gpl-2.0.php GPL v2
 *
 * Copyright (C) 2020 alfiopiccione <alfio.piccione@gmail.com>
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

namespace WcElectronInvoiceFree\Utils;

use WcElectronInvoiceFree\Cache\CacheTransient;
use WcElectronInvoiceFree\Functions as F;

/**
 * Class Helpers
 *
 * @since  3.2.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class Helpers
{
    /**
     * Set Query Data
     *
     * @param $args
     * @param $wpQuery
     * @param $buildClass
     *
     * @return object|null
     * @since  3.2.0
     */
    public static function setQueryData($args, $wpQuery, $buildClass)
    {
        if ('prod' === WC_EL_INV_ENV) {
            if ('' === $args['format'] || $args['format'] !== $args['getFormat']) {
                return null;
            }
        }

        // Initialized tag
        $tag   = '';
        $idTag = '';

        // Anomaly - 404 set fallback params
        if (isset($wpQuery->query['error']) && 404 === (int)$wpQuery->query['error']) {
            $wpQuery->set('wc-elc-inv', 'yes');
            $wpQuery->set('post_type', 'shop_order');
            $buildClass->postType = 'shop_order';
            $tag = 'shop_order';
            $idTag = 'shop_order_id';
        }

        // Return, if wc-elc-inv is don't in query
        if (! isset($wpQuery->query['wc-elc-inv']) && ! isset($wpQuery->query_vars['wc-elc-inv'])) {
            return null;
        }

        if (! isset($wpQuery->query['shop_order']) && ! isset($wpQuery->query_vars['post_type'])) {
            return null;
        }

        // Return, if post is null or in query
        if ((null === $wpQuery->post && 1 < $wpQuery->post_count) &&
            ! isset($wpQuery->query_vars['wc-elc-inv']) &&
            ! isset($wpQuery->query_vars['post_type'])
        ) {
            return null;
        }

        // Get tag and reset unnecessary param from the query
        if (is_array($buildClass->postType) && sizeof($buildClass->postType) >= 0) {
            foreach ($buildClass->postType as $type) {
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
            if('' === $tag) {
                // Post type tag
                $tag = $wpQuery->get($buildClass->postType);
            }
            if('' === $idTag) {
                // Post ID tag
                $idTag = $wpQuery->get($buildClass->postType . '_id');
            }
        }

        // Esc if not $tag
        if (! $tag || $tag === '' || $buildClass->postType === null) {
            return null;
        }

        // Explode $tag or empty array
        $arrayTag = strpos($tag, '/') ? explode('/', $tag) : array();
        // Set post type
        $postType = ! empty($arrayTag) ? reset($arrayTag) : $tag;
        // Set post id
        $idTag = ! empty($arrayTag) && '' === $idTag ? end($arrayTag) : $idTag;

        // Set current post type
        if (! empty($buildClass->postType) && ! empty($arrayTag) && in_array(reset($arrayTag), $buildClass->postType)) {
            // Set post type
            $wpQuery->set('post_type', $postType);
            $wpQuery->query['post_type'] = $postType;
        }

        // Anomaly - 404 set fallback params
        if (isset($wpQuery->query['error']) && 404 === (int)$wpQuery->query['error']) {
            $uri = ! empty($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : null;
            if ($uri) {
                $uriParse    = parse_url($uri);
                $path        = $uriParse['path'];
                $pathArray   = explode('/', $path);
                $pathArray   = array_filter($pathArray);
                $shopOrderID = end($pathArray);
                $idTag       = $shopOrderID;
            } else {
                $idTag = null;
            }
        }

        return (object)array(
            'postType' => $postType,
            'idTag'    => $idTag,
        );
    }

    /**
     * Set Query
     *
     * @param $tag  string The post type
     * @param $args array The arguments
     *
     * @return bool|false|\WC_Order|\WC_Order_Query|\WC_Order_Refund|\WC_Product|\WC_Product_Query|null
     * @since  3.2.0
     */
    public static function setQuery($tag, $args)
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

    /**
     * Get Cache Query
     *
     * @param $tag   string Post type tag
     * @param $idTag string The ID tag
     *
     * @return bool|object|null
     * @since  3.2.0
     */
    public static function getCachedQuery($tag, $idTag)
    {
        // Initialized Cache Object.
        $cache  = new CacheTransient();
        $object = array();

        // Get Cached Object query
        $allDataCache = $cache->get($tag);

        if (! $allDataCache) {
            return false;
        }

        $singleDataCache = false;
        // // Get Cached Object single query
        if (isset($idTag) && '' !== $idTag) {
            $singleDataCache = $cache->get($tag . '-' . $idTag);
        }

        if (isset($tag) && '' === $idTag && $allDataCache) {
            $object = maybe_unserialize($allDataCache);
        }

        if (isset($idTag) && '' !== $idTag && $singleDataCache) {
            $object = maybe_unserialize($singleDataCache);
        }

        if (empty($object)) {
            return null;
        }

        return (object)$object;
    }

    /**
     * Set Cache Query
     *
     * @param $query
     * @param $args
     *
     * @since  3.2.0
     */
    public static function setCacheQuery($query, $args)
    {
        $cache              = new CacheTransient();
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName($query, '\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName($query, '\WC_Order_Refund');

        switch ($query) {
            case $query instanceof \WC_Order_Query :
                // Set Orders Cache
                $cache->set(maybe_serialize($query), 'shop_order');
                break;
            case $query instanceof $wcOrderClass :
            case $query instanceof $wcOrderRefundClass :
                if (isset($args['order_id'])) {
                    // Set Order Cache
                    $cache->set(maybe_serialize($query), "shop_order-{$args['order_id']}");
                }
                break;
            default:
                break;
        }
    }
}
