<?php
/**
 * Utils.php
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

namespace WcElectronInvoiceFree\Functions;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Plugin;
use WcElectronInvoiceFree\Sanitize\Arrays;
use WcElectronInvoiceFree\Sanitize\Text;

/**
 * Filter Input
 *
 * @since 1.0.0
 *
 * @uses  filter_var() To filter the value.
 *
 * @param array  $data    The haystack of the elements.
 * @param string $key     The key of the element within the haystack to filter.
 * @param int    $filter  The filter to use.
 * @param array  $options The option for the filter var.
 *
 * @return bool|mixed The value filtered on success false if filter fails or key doesn't exists.
 */
function filterInput($data, $key, $filter = FILTER_DEFAULT, $options = array())
{
    return isset($data[$key]) ? filter_var($data[$key], $filter, $options) : false;
}

/**
 * Strip Content
 *
 * @since 1.0.0
 *
 * @param      $string
 * @param bool $remove_breaks
 *
 * @return null|string|string[]
 */
function stripTags($string, $remove_breaks = false)
{
    // Strip tags
    $string = strip_tags($string);
    // Clean up things like &amp;
    $string = html_entity_decode($string, ENT_COMPAT, 'UTF-8');
    // Replace Multiple spaces with single space
    $string = preg_replace('/ +/', ' ', $string);
    // Strip shortcode
    $string = rtrim(strip_shortcodes($string), "\n\t\r");
    // Strip images.
    $string = preg_replace('/<img[^>]+\>/i', '', $string);
    // Strip div.
    $string = preg_replace("/<div>(.*?)<\/div>/", "$1", $string);
    // Strip scripts.
    $string = preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', $string);
    // Convert € symbol
    $string = str_replace('€', '[EURO]', $string);
    // Convert & symbol
    $string = str_replace('&', 'E', $string);
    // Remove illegal charset
    $string = str_replace(array('<', '>', '"', "'"), '', $string);
    // Convert dash
    $string = str_replace(array('-', '_'), ' ', $string);
    // Convert per cent
    $string = str_replace('%', '[PERCENT]', $string);
    // Convert accents
    $string = str_replace(array('à', 'á'), 'a', $string);
    $string = str_replace(array('é', 'è'), 'e', $string);
    $string = str_replace(array('ì', 'í'), 'i', $string);
    $string = str_replace(array('ò', 'ó'), 'o', $string);
    $string = str_replace(array('ù', 'ú'), 'u', $string);

    if ($remove_breaks) {
        $string = preg_replace('/\s+/', ' ', $string);
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }

    return trim($string);
}

/**
 * Sanitize function
 *
 * @since 1.0.0
 *
 * @param $input string|array The string or array to sanitize
 *
 * @return array|bool|string  Sanitized value text or array, otherwise false
 */
function sanitize($input)
{
    if (is_string($input)) {
        try {
            return Text::sanitize($input);
        } catch (\Exception $e) {
            echo 'Input is not a string: ', $e->getMessage(), "\n";
        };
    } elseif (is_int($input)) {
        try {
            return $input;
        } catch (\Exception $e) {
            echo 'Input is not a int value: ', $e->getMessage(), "\n";
        };
    } elseif (is_array($input)) {
        try {
            return Arrays::sanitize($input);
        } catch (\Exception $e) {
            echo 'Input is not a array: ', $e->getMessage(), "\n";
        };
    } else {
        return false;
    }
}

/**
 * WpMl Switch language
 *
 * @since 1.0.0
 */
function switchLang()
{
    if (isWpmlActive() && defined('ICL_LANGUAGE_CODE')) {
        global $sitepress;
        $sitepress->switch_lang(ICL_LANGUAGE_CODE);
    }
}

/**
 * Get current lang
 *
 * @since 1.0.0
 *
 * @return string
 */
function getCurrentLanguage()
{
    $lang   = '';
    $locale = substr(get_locale(), 0, -3);

    if ($locale) {
        $lang = $locale;
    }

    if (isWpmlActive()) {
        $lang = defined('ICL_LANGUAGE_CODE') ? ICL_LANGUAGE_CODE : '';
    }

    return $lang;
}

/**
 * Send a JSON response back to an Ajax request, indicating failure.
 *
 * If the `$data` parameter is a WP_Error object, the errors
 * within the object are processed and output as an array of error
 * codes and corresponding messages. All other types are output
 * without further processing.
 *
 * @since 1.0.0
 *
 * @param mixed $data        Data to encode as JSON, then print and die.
 * @param int   $status_code The HTTP status code to output.
 */
function sendJsonError($data = null, $status_code = null)
{
    $response = array('success' => false);

    if (isset($data)) {
        if (is_wp_error($data)) {
            $result = array();
            foreach ($data->errors as $code => $messages) {
                foreach ($messages as $message) {
                    $result[] = array('code' => $code, 'message' => $message);
                }
            }

            $response['data'] = $result;
        } else {
            $response['data'] = $data;
        }
    }

    sendJson($response, $status_code);
}

/**
 * Get Customers List
 *
 * @since 1.0.0
 *
 * @return array The customer list
 */
function getCustomersList()
{
    $users    = get_users();
    $userList = array();

    if (! empty($users) && ! is_wp_error($users)) {
        foreach ($users as $user) {
            $role                      = ! empty($user->roles) ? ucfirst(" - {$user->roles[0]}") : '';
            $userList[$user->data->ID] = "{$user->data->display_name}{$role}";
        }
    }

    return $userList;
}

/**≤
 * Send a JSON response back to an Ajax request.
 *
 * @since 1.0.0
 *
 * @param mixed $response    Variable (usually an array or object) to encode as JSON,
 *                           then print and die.
 * @param int   $status_code The HTTP status code to output.
 */
function sendJson($response, $status_code = null)
{
    @header('Content-Type: application/json; charset=' . get_option('blog_charset'));

    if (null !== $status_code) {
        status_header($status_code);
    }

    echo wp_json_encode($response);

    if (wp_doing_ajax()) {
        wp_die('', '', array(
            'response' => null,
        ));
    } else {
        die;
    }
}

/**
 * Get Post Meta
 *
 * @param      $key
 * @param null $default
 * @param int  $post
 * @param bool $single
 *
 * @return mixed|null
 */
function getPostMeta($key, $default = null, $post = 0, $single = true)
{
    // Get the post.
    $post = get_post($post);

    if (! $post) {
        return $default;
    }

    // Return the default value if meta data doesn't exists.
    if (! metadata_exists('post', $post->ID, $key) && null !== $default) {
        return $default;
    }

    // Retrieve the post meta.
    return get_post_meta($post->ID, $key, $single);
}

/**
 * Premium Banner.
 */
function premiumBanner()
{
    $lang = getCurrentLanguage();
    if ('it' !== $lang) {
        return;
    }
    ?>
    <div class="banner-premium">
        <div class="banner-logo">
            <img src="<?php echo esc_url(Plugin::getPluginDirUrl('assets/images/woopop.png')); ?>" alt="woopop">
        </div>
        <div class="banner-text">
            <h1>Ecco cosa potrai fare con la versione <span>PREMIUM</span> di WooPOP</h1>
            <ul class="banner-premium--list">
                <li><strong>1.</strong> Generare le fatture XML nella sezione "Fatture XML" e in ogni singolo ordine.</li>
                <li><strong>2.</strong> Scaricare le fatture sul tuo computer singolarmente o in formato .zip</li>
                <li><strong>3.</strong> Attivare il controllo VIES per i clienti dell'Unione Europea (non Italiani).</li>
                <li><strong>4.</strong> Marcare come inviate le fatture per una migliore gestione ed evitare errori.</li>
                <li><strong>e molto altro ancora...</strong></li>
            </ul>
            <p><a target="_blank" class="button" href="https://woopop.it/prodotto/woopop-plugin/">WooPOP Premium</a></p>
        </div>
    </div>

<?php }

/**
 * Disable Plugin
 *
 * This function disable the plugin because of his dependency.
 *
 * @since 1.0.0
 *
 * @return void
 */
function disablePlugin()
{
    if (! function_exists('deactivate_plugins')) {
        require_once untrailingslashit(ABSPATH) . '/wp-admin/includes/plugin.php';
    }

    if (! isWooCommerceActive()) :
        add_action('admin_notices', function () {
            ?>
            <div class="notice notice-error">
                <p><span class="dashicons dashicons-no"></span>
                    <?php esc_html_e(
                        'WooPop -> (Electronic Invoice) has been deactivated. The plugin require: WooCommerce',
                        WC_EL_INV_FREE_TEXTDOMAIN
                    ); ?>
                </p>
            </div>
            <?php

            // Don't show the activated notice.
            if (isset($_GET['activate'])) {
                unset($_GET['activate']);
            }
        });

        // Deactivate the plugin.
        deactivate_plugins(WC_EL_INV_FREE_PLUGIN_DIR . '/index.php');
    endif;
}

/**
 * Helper for get Wc Order class name
 *
 * @since {$_SINCE}
 * - Support for WC Admin package
 *
 * @param $classname
 *
 * @return string
 */
function wcOrderClassName($classname)
{
    if (isWooCommerceActive() && \WC()->version >= '4.0.1') {
        if ('\WC_Order' === $classname) {
            return '\Automattic\WooCommerce\Admin\Overrides\Order';
        } elseif ('\WC_Order_Refund' === $classname) {
            return '\Automattic\WooCommerce\Admin\Overrides\OrderRefund';
        } else {
            return $classname;
        }
    } else {
        return $classname;
    }
}
