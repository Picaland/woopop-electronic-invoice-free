<?php
/**
 * GeneralFields.php
 *
 * @since      1.0.0
 * @package    WcElectronInvoiceFree\WooCommerce\Fields
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

namespace WcElectronInvoiceFree\WooCommerce\Fields;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Class GeneralFields
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
class GeneralFields
{
    /**
     * Regex VAT Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexVAT = "/^(ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{13})|HU[0-9]{8}|IE[0-9][A-Z0-9][0-9]{5}[A-Z]{1,2}|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$/i";

    /**
     * General Fields
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $fields;

    /**
     * Prefix option
     *
     * @since 1.0.0
     *
     * @var string
     */
    public static $optionPrefix = 'wc_el_inv-';

    /**
     * GeneralFields constructor.
     *
     * @param array $fields
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * Get EU countries
     *
     * @return string[]
     */
    public static function getEuCountries()
    {
        $countries = new \WC_Countries();

        return $countries->get_european_union_countries();
    }

    /**
     * Add General invoice fields
     *
     * @param $fields
     *
     * @return array
     * @since 1.0.0
     *
     */
    public function generalInvoiceFields($fields)
    {
        if (in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            $fields = array_merge($this->fields, $fields);
        }

        return $fields;
    }

    /**
     * General store name
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionName()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-general_store_your_name',
            get_option(self::$optionPrefix . 'general_store_your_name')
        ) ?: '';
    }

    /**
     * General store surname
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionSurname()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-general_store_your_surname',
            get_option(self::$optionPrefix . 'general_store_your_surname')
        ) ?: '';
    }

    /**
     * General store company name
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionCompanyName()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-general_store_company_name',
            \WcElectronInvoiceFree\Functions\stripTags(
                get_option(self::$optionPrefix . 'general_store_company_name')
            )
        ) ?: '';
    }

    /**
     * General store vat number
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionVatNumber()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-general_store_vat_number',
            get_option(self::$optionPrefix . 'general_store_vat_number')
        ) ?: '';
    }

    /**
     * General store email address
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionEmailAddress()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-general_store_email',
            get_option(self::$optionPrefix . 'general_store_email')
        ) ?: '';
    }

    /**
     * General store phone number
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionPhoneNumber()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-general_store_phone',
            get_option(self::$optionPrefix . 'general_store_phone')
        ) ?: '';
    }

    /**
     * General store tax regime
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionTaxRegime()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-general_store_tax_regime',
            get_option(self::$optionPrefix . 'general_store_tax_regime')
        ) ?: '';
    }

    /**
     * General store office register business
     *
     * @return mixed Value set for the option.
     * @since 2.2.1
     *
     */
    public static function getGeneralInvoiceOptionProvinceOfficeBusinessRegister()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-province_business_register_office',
            get_option(self::$optionPrefix . 'province_business_register_office')
        ) ?: '';
    }

    /**
     * General store REA number
     *
     * @return mixed Value set for the option.
     * @since 2.2.1
     *
     */
    public static function getGeneralInvoiceOptionReaNumber()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-rea_registration_number',
            get_option(self::$optionPrefix . 'rea_registration_number')
        ) ?: '';
    }

    /**
     * General store liquidation status
     *
     * @return mixed Value set for the option.
     * @since 2.2.1
     *
     */
    public static function getGeneralInvoiceOptionLiquidationStatus()
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-liquidation_status',
            get_option(self::$optionPrefix . 'liquidation_status')
        ) ?: '';
    }

    /**
     * General store invoice country state
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionCountryState()
    {
        $country = get_option('woocommerce_default_country');
        $country = explode(':', $country);

        if (! in_array($country[0], self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-woocommerce_store_country_state',
            $country[0]
        ) ?: '';
    }

    /**
     * General store invoice country province
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionCountryProvince()
    {
        $country = get_option('woocommerce_default_country');
        $country = explode(':', $country);

        if (count($country) === 1 && ! in_array($country[0], self::getEuCountries())) {
            return '';
        }

        return apply_filters('wc_el_inv-woocommerce_store_country_province',
            $country[1]
        ) ?: '';
    }

    /**
     * General store invoice city
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionCity()
    {
        $city = get_option('woocommerce_store_city');

        return apply_filters('wc_el_inv-woocommerce_store_city',
            $city
        ) ?: '';
    }

    /**
     * General store invoice city
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralShippingLocation()
    {
        $location = get_option('woocommerce_ship_to_countries');

        return apply_filters('wc_el_inv-woocommerce_ship_to_countries',
            $location
        ) ?: '';
    }

    /**
     * General store invoice post code
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionPostCode()
    {
        $postcode = get_option('woocommerce_store_postcode');

        return apply_filters('wc_el_inv-woocommerce_store_postcode',
            $postcode
        ) ?: '';
    }

    /**
     * General store invoice post code
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceOptionAddress()
    {
        $address = get_option('woocommerce_store_address');

        return apply_filters('wc_el_inv-woocommerce_store_address',
            $address
        ) ?: '';
    }

    /**
     * General store transmitter vat number
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceTransmitterOptionVatNumber()
    {
        return apply_filters('wc_el_inv-general_store_vat_number_transmitter',
            get_option(self::$optionPrefix . 'general_store_vat_number_transmitter') ?? null
        ) ?: '';
    }

    /**
     * General store transmitter phone
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceTransmitterOptionPhone()
    {
        return apply_filters('wc_el_inv-general_store_phone_transmitter',
            get_option(self::$optionPrefix . 'general_store_phone_transmitter') ?? null
        ) ?: '';
    }

    /**
     * General store transmitter email
     *
     * @return mixed Value set for the option.
     * @since 1.0.0
     *
     */
    public static function getGeneralInvoiceTransmitterOptionEmail()
    {
        return apply_filters('wc_el_inv-general_store_email_transmitter',
            get_option(self::$optionPrefix . 'general_store_email_transmitter') ?? null
        ) ?: '';
    }

    /**
     * Sanitize
     *
     * @param $value
     * @param $option
     * @param $rawValue
     *
     * @return bool|mixed|string
     * @since 1.0.0
     *
     */
    public function sanitize($value, $option, $rawValue)
    {
        if (! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return '';
        }

        return \WcElectronInvoiceFree\Functions\sanitize($value);
    }

    /**
     * Notice
     *
     * @since 1.0.0
     */
    public function notice()
    {
        if (empty($_POST) || ! in_array(self::getGeneralInvoiceOptionCountryState(), self::getEuCountries())) {
            return;
        }

        // @codingStandardsIgnoreLine
        $vatCode = \WcElectronInvoiceFree\Functions\filterInput($_POST,
            self::$optionPrefix . 'general_store_vat_number',
            FILTER_UNSAFE_RAW
        );

        // @codingStandardsIgnoreLine
        $page = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'page', FILTER_UNSAFE_RAW);
        $tab  = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'tab', FILTER_UNSAFE_RAW);

        if ('wc-settings' !== $page ||
            ($tab && ('wc-settings' === $page && 'general' !== $tab))
        ) {
            return;
        }

        // @codingStandardsIgnoreLine
        $name = \WcElectronInvoiceFree\Functions\filterInput($_POST,
            self::$optionPrefix . 'general_store_your_name',
            FILTER_UNSAFE_RAW
        );

        // @codingStandardsIgnoreLine
        $surname = \WcElectronInvoiceFree\Functions\filterInput($_POST,
            self::$optionPrefix . 'general_store_your_surname',
            FILTER_UNSAFE_RAW
        );

        // @codingStandardsIgnoreLine
        $company = \WcElectronInvoiceFree\Functions\filterInput($_POST,
            self::$optionPrefix . 'general_store_company_name',
            FILTER_UNSAFE_RAW
        );

        // @codingStandardsIgnoreLine
        $phone = \WcElectronInvoiceFree\Functions\filterInput($_POST,
            self::$optionPrefix . 'general_store_phone',
            FILTER_UNSAFE_RAW
        );

        // @codingStandardsIgnoreLine
        $email = \WcElectronInvoiceFree\Functions\filterInput($_POST,
            self::$optionPrefix . 'general_store_email',
            FILTER_VALIDATE_EMAIL
        );

        $country = \WcElectronInvoiceFree\Functions\filterInput($_POST,
            'woocommerce_default_country',
            FILTER_UNSAFE_RAW
        );

        $country = explode(':', $country);

        if (empty($vatCode) || strlen($vatCode) < 8) { ?>
            <div class="notice error is-dismissible">
                <p><?php esc_html_e('Please enter your valid VAT number', WC_EL_INV_FREE_TEXTDOMAIN); ?></p>
            </div>
        <?php } elseif (! empty($country) && ! empty($country[0]) && ! preg_match($this->regexVAT,
                $country[0] . $vatCode)) { ?>
            <div class="notice error is-dismissible">
                <p><?php echo sprintf(__('VAT number %1$s is not correct', WC_EL_INV_FREE_TEXTDOMAIN),
                        "<strong>{$country[0]}-{$vatCode}</strong>"
                    ); ?>
                </p>
            </div>
        <?php }

        if (! $company) { ?>

            <?php if (! $name && ! $surname) { ?>
                <div class="notice error is-dismissible">
                    <p><?php esc_html_e('Please enter your Company Name', WC_EL_INV_FREE_TEXTDOMAIN); ?></p>
                </div>
            <?php } ?>

            <?php if (! $name) { ?>
                <div class="notice error is-dismissible">
                    <p><?php esc_html_e('Please enter your Name', WC_EL_INV_FREE_TEXTDOMAIN); ?></p>
                </div>
            <?php }

            if (! $surname) { ?>
                <div class="notice error is-dismissible">
                    <p><?php esc_html_e('Please enter your Surname', WC_EL_INV_FREE_TEXTDOMAIN); ?></p>
                </div>
            <?php }
        }

        if (! $email) { ?>
            <div class="notice error is-dismissible">
                <p><?php esc_html_e('Please enter your Email address', WC_EL_INV_FREE_TEXTDOMAIN); ?></p>
            </div>
        <?php }

        if (! $phone) { ?>
            <div class="notice error is-dismissible">
                <p><?php esc_html_e('Please enter your Phone number', WC_EL_INV_FREE_TEXTDOMAIN); ?></p>
            </div>
        <?php }
    }
}
