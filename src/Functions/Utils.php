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

use WcElectronInvoiceFree\Admin\Settings\OptionPage;
use WcElectronInvoiceFree\Sanitize\Arrays;
use WcElectronInvoiceFree\Sanitize\Text;
use WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields;

/**
 * Filter Input
 *
 * @param array  $data    The haystack of the elements.
 * @param string $key     The key of the element within the haystack to filter.
 * @param int    $filter  The filter to use.
 * @param array  $options The option for the filter var.
 *
 * @return bool|mixed The value filtered on success false if filter fails or key doesn't exists.
 * @uses  filter_var() To filter the value.
 *
 * @since 1.0.0
 *
 */
function filterInput($data, $key, $filter = FILTER_DEFAULT, $options = array())
{
    // Support for PHP >= 8.1
    // 513 = FILTER_SANITIZE_STRING
    if ((phpversion() >= 8.1) && $filter === 513) {
        $filter = FILTER_UNSAFE_RAW;
    }

    return isset($data[$key]) ? filter_var($data[$key], $filter, $options) : false;
}

/**
 * Strip Content
 *
 * @param      $string
 * @param bool $removeBreaks
 *
 * @return null|string|string[]
 * @since 1.0.0
 *
 */
function stripTags($string, $removeBreaks = false)
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
    $string = str_replace('#', '[HASH-TAG]', $string);
    // Remove illegal charset
    $string = str_replace(array('<', '>', '"', "'", "`", "´", "“", "”", "’"), ' ', $string);
    // Convert dash
    $string = str_replace(array('-', '–', '_'), ' ', $string);
    // Convert per cent
    $string = str_replace('%', '[PERCENT]', $string);
    // Convert accents
    $unwantedArray = array(
        'Š' => 'S',
        'š' => 's',
        'Ž' => 'Z',
        'ž' => 'z',
        'À' => 'A',
        'Á' => 'A',
        'Â' => 'A',
        'Ã' => 'A',
        'Ä' => 'A',
        'Å' => 'A',
        'Æ' => 'A',
        'Ç' => 'C',
        'È' => 'E',
        'É' => 'E',
        'Ê' => 'E',
        'Ë' => 'E',
        'Ì' => 'I',
        'Í' => 'I',
        'Î' => 'I',
        'Ï' => 'I',
        'Ñ' => 'N',
        'Ò' => 'O',
        'Ó' => 'O',
        'Ô' => 'O',
        'Õ' => 'O',
        'Ö' => 'O',
        'Ø' => 'O',
        'Ù' => 'U',
        'Ú' => 'U',
        'Û' => 'U',
        'Ü' => 'U',
        'Ý' => 'Y',
        'Þ' => 'B',
        'ß' => 'Ss',
        'à' => 'a',
        'á' => 'a',
        'â' => 'a',
        'ã' => 'a',
        'ä' => 'a',
        'å' => 'a',
        'æ' => 'a',
        'ç' => 'c',
        'è' => 'e',
        'é' => 'e',
        'ê' => 'e',
        'ë' => 'e',
        'ì' => 'i',
        'í' => 'i',
        'î' => 'i',
        'ï' => 'i',
        'ð' => 'o',
        'ñ' => 'n',
        'ò' => 'o',
        'ó' => 'o',
        'ô' => 'o',
        'õ' => 'o',
        'ö' => 'o',
        'ø' => 'o',
        'ù' => 'u',
        'ú' => 'u',
        'û' => 'u',
        'ü' => 'u',
        'ý' => 'y',
        'þ' => 'b',
        'ÿ' => 'y',
    );
    $string        = strtr($string, $unwantedArray);
    // Normalize string
    $string = preg_replace("/[^A-Za-z0-9.]]/u", ' ', $string);

    // Remove breaks
    if ($removeBreaks) {
        $string = preg_replace('/\s+/', ' ', $string);
        $string = preg_replace('/[\r\n\t ]+/', ' ', $string);
    }

    return trim($string);
}

/**
 * Sanitize function
 *
 * @param $input string|array The string or array to sanitize
 *
 * @return array|bool|string  Sanitized value text or array, otherwise false
 * @since 1.0.0
 *
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
 * @return string
 * @since 1.0.0
 *
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
 * @param mixed $data        Data to encode as JSON, then print and die.
 * @param int   $status_code The HTTP status code to output.
 *
 * @since 1.0.0
 *
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
 * @return array The customer list
 * @since 1.0.0
 *
 */
function getCustomersList()
{
    $users    = get_users();
    $userList = array();

    if (! empty($users) && ! is_wp_error($users)) {
        foreach ($users as $user) {
            $role                      = ! empty($user->roles) && isset($user->roles[0]) ? ucfirst(" - {$user->roles[0]}") : '';
            $userList[$user->data->ID] = "{$user->data->display_name}{$role}";
        }
    }

    return $userList;
}

/**≤
 * Send a JSON response back to an Ajax request.
 *
 * @param mixed $response    Variable (usually an array or object) to encode as JSON,
 *                           then print and die.
 * @param int   $status_code The HTTP status code to output.
 *
 * @since 1.0.0
 *
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
 * No Key message
 *
 * @param $key
 *
 * @return bool
 * @since 1.0.0
 *
 */
function noKey($key)
{
    if ('' === $key && 'prod' === WC_EL_INV_ENV) {
        echo sprintf('<p class="wc_el_inv__no-key"><strong>%s:</strong> <code>%s</code></p>',
            esc_html__('Warning', WC_EL_INV_FREE_TEXTDOMAIN),
            esc_html__('enter the third-party key to generate your secret key to enable APIs', WC_EL_INV_FREE_TEXTDOMAIN)
        );

        return true;
    }

    return false;
}

/**
 * Is Associative Array
 *
 * @param array $array The array to know if is associative or not.
 *
 * @return bool         True if array is associative, false otherwise.
 * @since  1.0.0
 *
 */
function isArrayAssoc(array $array)
{
    return array_keys($array) !== range(0, count($array) - 1);
}

/**
 * Implode Assoc
 *
 * Implode an associative array
 *
 * @param string $glue    The string to use for separate the elements value.
 * @param string $assGlue The string to use to separate key and value.
 * @param array  $arr     The array to implode.
 *
 * @return string|bool The array imploded. False if $arr is not an array. False if $arr is not associative.
 * @since 1.0.0
 *
 */
function implodeAssoc($glue, $assGlue, $arr)
{
    if (wp_is_numeric_array($arr)) {
        return false;
    }

    // The string.
    $string = '';

    foreach ($arr as $k => $v) {
        if (! $v) {
            continue;
        }

        $string .= $k . $assGlue . $v . $glue;
    }

    // Remove the latest glue string.
    $string = rtrim($string, $glue);

    return $string;
}

/**
 * Insert an element into array in a specific position
 *
 * @param array $needle    The array to insert in.
 * @param array $haystack  The array to insert on.
 * @param mixed $pos       The key or index where the array should be inserted.
 * @param bool  $preserve  If the original value and positions should be preserved.
 * @param bool  $recursive To merge recursively or not the result array.
 *
 * @return array            The new merged array
 * @since  1.0.0
 *
 */
function arrayInsertInPos($needle, array &$haystack, $pos, $preserve = false, $recursive = false)
{
    $keys = array_filter(array_intersect(array_keys($needle), array_keys($haystack)), 'is_string');

    if (is_array($pos)) {
        list($key, $before) = $pos;
        $before = (isset($before) && true === $before) ? true : false;
        $pos    = array_search($key, array_keys($haystack), true);
        $pos    = $before ? $pos : $pos + 1;
    }

    if ($keys) {
        if ($preserve) {
            $arr =& $needle;
        } else {
            $arr =& $haystack;
        }

        foreach ($keys as $k => $v) {
            unset($arr[$v]);
        }
    }

    $start    = array_splice($haystack, 0, (int)$pos);
    $func     = $recursive ? 'array_merge_recursive' : 'array_merge';
    $haystack = call_user_func_array($func, array($start, (array)$needle, $haystack));

    return $haystack;
}

/**
 * Disable Plugin
 *
 * This function disable the plugin because of his dependency.
 *
 * @return void
 * @since 1.0.0
 *
 */
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
 * @param $classname
 *
 * @return string
 *
 * @since 2.2
 * - Support for WC Admin package
 *
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

/**
 * Get customer
 *
 * @param $customerID
 *
 * @return null
 */
function setCustomerLocation($customerID)
{
    $parse = ! empty($_SERVER) ? parse_url($_SERVER['REQUEST_URI']) : array();
    if (! empty($parse) && isset($parse['path'])) {
        $path = explode('/', $parse['path']);
        $path = array_filter($path);
        if (! in_array('wc-inv', $path, true) &&
            ! in_array('wp-admin', $path, true)
        ) {
            return;
        }
    }

    try {
        // Init customer by ID
        $initCustomer = new \WC_Customer($customerID);

        add_filter('woocommerce_get_tax_location', function ($location, $taxClass, $customer) use ($initCustomer) {
            if ($initCustomer instanceof \WC_Customer) {
                $location = array(
                    $initCustomer->get_billing_country(),
                    $initCustomer->get_billing_state(),
                    $initCustomer->get_billing_postcode(),
                    $initCustomer->get_billing_city(),
                );
            }

            return $location;
        }, 3, 20);

        return null;
    } catch (\Exception $e) {
        return null;
    }
}

/**
 * Premium Banner.
 */
function premiumBanner()
{
    $lang = \WcElectronInvoiceFree\Functions\getCurrentLanguage();
    if ('it' !== $lang) {
        return;
    }
    ?>
    <div class="banner-premium">
        <div class="banner-logo">
            <img src="<?php echo esc_url(\WcElectronInvoiceFree\Plugin::getPluginDirUrl('assets/images/woopop.png')); ?>" alt="woopop">
        </div>
        <div class="banner-text">
            <h1>Ecco cosa potrai fare con la versione <span>PREMIUM</span> di WooPOP</h1>
            <ul class="banner-premium--list">
                <li><strong>1.</strong> Scaricare le fatture in formato XML senza alcun limite.</li>
                <li><strong>2.</strong> Generare la fattura elettronica nella sezione "Fatture XML" e in ogni singolo ordine.</li>
                <li><strong>3.</strong> Scaricare le fatture sul tuo computer singolarmente o in formato .zip</li>
                <li><strong>4.</strong> Attivare il controllo VIES per i clienti dell'Unione Europea (non Italiani).</li>
                <li><strong>5.</strong> Inviare le fatture allo SDI direttamente da WooCommerce tramite add-on per <strong>Fatture in cloud</strong></li>
                <li><strong>e molto altro ancora...</strong></li>
            </ul>
            <p><a target="_blank" class="button" href="https://woopop.it/?ref=1&free_banner">Passa a WooPOP Premium</a></p>
        </div>
    </div>

<?php }


/**
 * Payment Method Code info
 *
 * @param $order
 *
 * @return mixed|string|void
 * @since 4.1.5
 *
 */
function paymentMethodCode($order)
{
    if (! property_exists($order, 'payment_method') &&
        ! property_exists($order, 'refunded') &&
        ! isset($order->refunded['refunded_payment_method'])
    ) {
        return '';
    }

    /**
     * Payment method for refunded:
     */
    if ('shop_order_refund' === $order->order_type &&
        isset($order->refunded['refunded_payment_method'])
    ) {
        if ('' === $order->refunded['refunded_payment_method']) {
            return sprintf('<b style="color:#007cba;">MP01</b> - %s', esc_html__('Cash money', WC_EL_INV_FREE_TEXTDOMAIN));
        }
        switch ($order->refunded['refunded_payment_method']) {
            default:
            case 'MP01':
                return sprintf('<b style="color:#007cba;">MP01</b> - %s',
                    esc_html__('Cash money', WC_EL_INV_FREE_TEXTDOMAIN));
            case 'MP02':
                return sprintf('<b style="color:#007cba;">MP02</b> - %s',
                    esc_html__('Bank check', WC_EL_INV_FREE_TEXTDOMAIN));
            case 'MP03':
                return sprintf('<b style="color:#007cba;">MP03</b> - %s',
                    esc_html__('Bank check', WC_EL_INV_FREE_TEXTDOMAIN));
            case 'MP05':
                return sprintf('<b style="color:#007cba;">MP05</b> - %s',
                    esc_html__('Bank transfer', WC_EL_INV_FREE_TEXTDOMAIN));
            case 'MP08':
                return sprintf('<b style="color:#007cba;">MP08</b> - %s',
                    esc_html__('Credit Card', WC_EL_INV_FREE_TEXTDOMAIN));
        }
    }

    if (property_exists($order, 'payment_method')) {
        false !== strpos($order->payment_method, 'stripe') ? $order->payment_method = 'stripe' : '';
        false !== strpos($order->payment_method, 'paypal') ? $order->payment_method = 'paypal' : '';
    }

    /**
     * Payment method for order:
     *
     * - Bacs
     * - Cheque
     * - PayPal Express Checkout
     * - Stripe
     * - Stripe SEPA
     */
    if (property_exists($order, 'payment_method')) {
        $methodTitle = $order->payment_method_title;
        switch ($order->payment_method) {
            case 'bacs':
                return sprintf('<b style="color:#007cba;">MP05</b> - %s', $methodTitle);
            case 'cheque':
                return sprintf('<b style="color:#007cba;">MP02</b> - %s', $methodTitle);
            case 'paypal':
            case 'ppec_paypal':
            case 'ppcp-gateway':
            case 'stripe':
            case 'xpay':
            case 'soisy':
                return sprintf('<b style="color:#007cba;">MP08</b> - %s', $methodTitle);
            case 'stripe_sepa':
                return sprintf('<b style="color:#007cba;">MP19</b> - %s', $methodTitle);
            default:
                return apply_filters('wc_el_inv-default_payment_method_invoice_table',
                    sprintf('<b style="color:#007cba;">MP01</b> - %s', $methodTitle),
                    $order->payment_method
                );
        }
    }
}

/**
 * checkOptions
 *
 * @param $type
 *
 * @return bool
 */
function checkOptions($type = null)
{
    if (! $type) {
        return false;
    }

    if ('general' === $type &&
        ! boolval(GeneralFields::getGeneralInvoiceOptionCompanyName()) ||
        ! boolval(GeneralFields::getGeneralInvoiceOptionVatNumber()) ||
        ! boolval(GeneralFields::getGeneralInvoiceOptionTaxRegime())
    ) {
        return false;
    }

    if ('invoice' === $type &&
        ! boolval(OptionPage::init()->getOptions('prefix_invoice_number')) ||
        ! boolval(OptionPage::init()->getOptions('number_next_invoice'))
    ) {
        return false;
    }

    if ('checkout' === $type &&
        ! boolval(OptionPage::init()->getOptions('invoice_required'))) {
        return false;
    }

    return true;
}
