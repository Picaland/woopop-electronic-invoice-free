<?php
/**
 * CreateXml.php
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

use WcElectronInvoiceFree\Admin\Settings\OptionPage;
use WcElectronInvoiceFree\Plugin;
use WcElectronInvoiceFree\Utils\TimeZone;
use WcElectronInvoiceFree\WooCommerce\Fields\GeneralFields;
use WcElectronInvoiceFree\WooCommerce\Fields\InvoiceFields;

/**
 * Class CreateXml
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class CreateXml
{
    /**
     * List type
     *
     * @since 1.0.0
     */
    const LIST_TYPE = 'shop_order';

    /**
     * Code Type
     *
     * @since 1.0.0
     */
    const CODE_TYPE = 'INTERNO';

    /**
     * Shipping code
     *
     * @since 1.0.0
     */
    const CODE_SHIPPING = 'SHIPPING';

    /**
     * Fee code
     *
     * @since 1.0.0
     */
    const CODE_FEE = 'FEE';

    /**
     * Refund code
     *
     * @since 1.0.0
     */
    const CODE_REFUND = 'REFUND';

    /**
     * Extra Italian SDI code
     *
     * @since 1.0.0
     */
    const NO_IT_SDI_CODE = 'XXXXXXX';

    /**
     * Sent increment number
     *
     * @since 1.0.0
     *
     * @var int
     */
    private static $sentNumber = 1;

    /**
     * XML Element
     *
     * @since 1.0.0
     *
     * @var \SimpleXMLElement
     */
    private $xml;

    /**
     * Regex Web Service Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexWEBSERV = "/^[a-zA-Z0-9]{7}$/i";

    /**
     * Regex PEC
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexPEC = "/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:\w*.?pec(?:.?\w+)*)$/i";

    /**
     * Regex Legal Mail
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexLEGALMAIL = "/^(?:[a-z0-9!#$%&'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+\/=?^_`{|}~-]+)*|\"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*\")@(?:\w*.?legalmail(?:.?\w+)*)$/i";

    /**
     * Regex Tax Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexCF = "/^([A-Z]{6}[0-9LMNPQRSTUV]{2}[ABCDEHLMPRST]{1}[0-9LMNPQRSTUV]{2}[A-Za-z]{1}[0-9LMNPQRSTUV]{3}[A-Z]{1})$/i";

    /**
     * Regex VAT Code
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $regexVAT = "/^(ATU[0-9]{8}|BE0[0-9]{9}|BG[0-9]{9,10}|CY[0-9]{8}L|CZ[0-9]{8,10}|DE[0-9]{9}|DK[0-9]{8}|EE[0-9]{9}|(EL|GR)[0-9]{9}|ES[0-9A-Z][0-9]{7}[0-9A-Z]|FI[0-9]{8}|FR[0-9A-Z]{2}[0-9]{9}|GB([0-9]{9}([0-9]{3})?|[A-Z]{2}[0-9]{13})|HU[0-9]{8}|IE[0-9][A-Z0-9][0-9]{5}[A-Z]{1,2}|IT[0-9]{11}|LT([0-9]{9}|[0-9]{12})|LU[0-9]{8}|LV[0-9]{11}|MT[0-9]{8}|NL[0-9]{9}B[0-9]{2}|PL[0-9]{10}|PT[0-9]{9}|RO[0-9]{2,10}|SE[0-9]{12}|SI[0-9]{8}|SK[0-9]{10})$/i";

    /**
     * /**
     * CreateXml constructor.
     *
     * @param \SimpleXMLElement $element
     *
     * @since 1.0.0
     *
     */
    public function __construct(\SimpleXMLElement $element)
    {
        $this->xml = $element;
    }

    /**
     * Add Processing Instruction
     *
     * @param $name
     * @param $value
     *
     * @since 1.0.0
     *
     */
    private function addProcessingInstruction($name, $value)
    {
        // Create a DomElement from this simpleXML object
        $domSxe = dom_import_simplexml($this->xml);

        // Create a handle to the owner doc of this xml
        $domParent = $domSxe->ownerDocument;

        // Find the topmost element of the domDocument
        $xpath        = new \DOMXPath($domParent);
        $firstElement = $xpath->evaluate('/*[1]')->item(0);

        // Add the processing instruction before the topmost element
        $pi = $domParent->createProcessingInstruction($name, $value);
        $domParent->insertBefore($pi, $firstElement);
    }

    /**
     * Version
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function version($ordersData)
    {
        // Not set invoice type? FPR12 format default
        if (! property_exists($ordersData[0], 'invoice_type') ||
            empty($ordersData[0]->invoice_type) ||
            ! isset($ordersData[0]->invoice_type) ||
            ($ordersData[0]->invoice_type && '' === $ordersData[0]->invoice_type)
        ) {
            return 'FPR12';
        }

        switch ($ordersData[0]->invoice_type) {
            case 'freelance':
            case 'private':
            case 'company':
                return 'FPR12';
                break;
            default:
                break;
        }
    }

    /**
     * Date modified
     *
     * @param $order
     *
     * @return string
     * @since 1.0.0
     */
    private function dateLastModified($order)
    {
        $dateOrder = $order->date_modified;

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime($dateOrder);
            $date->setTimezone($timeZone);

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            echo esc_html__('Error DateTime in dateLastModified: ') . $e->getMessage();
        }
    }

    /**
     * Date invoice
     *
     * @param $order
     *
     * @return string
     */
    private function dateInvoice($order)
    {
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');

        $orderObj    = wc_get_order($order->id);
        $dateInvoice = null;

        if ($orderObj instanceof $wcOrderRefundClass) {
            $dateInvoice = $orderObj->get_date_created();
        } elseif ($orderObj instanceof $wcOrderClass) {
            $dateInvoice = $orderObj->get_date_completed();
        }

        if ($dateInvoice) {
            return $dateInvoice->date_i18n('Y-m-d');
        }

        return $dateInvoice;
    }

    /**
     * Date completed
     *
     * @param      $order
     * @param bool $parent
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function dateCompleted($order, $parent = false)
    {
        $dateOrder = $order->date_created;

        // Get parent order if current data is refund
        if ($parent && 'shop_order_refund' === $order->order_type) {
            $parentOrder = wc_get_order($order->parent_id);

            // Parent order created
            $dateOrder = $parentOrder->get_date_created();
            $dateOrder = $dateOrder->format('c');
        }

        try {
            $timeZone = new TimeZone();
            $timeZone = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date     = new \DateTime($dateOrder);
            $date->setTimezone($timeZone);

            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            echo esc_html__('Error DateTime in dateCompleted: ') . $e->getMessage();
        }
    }

    /**
     * Unique Progressive code
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function progressiveUniqueCode($ordersData)
    {
        if (! property_exists($ordersData[0], 'date_completed') &&
            ! property_exists($ordersData[0], 'date_modified') &&
            ! property_exists($ordersData[0], 'id')
        ) {
            return '';
        }

        try {
            $completedDate = isset($ordersData[0]->date_completed) ? $ordersData[0]->date_completed : $ordersData[0]->date_modified;
            $timeZone      = new TimeZone();
            $timeZone      = new \DateTimeZone($timeZone->getTimeZone()->getName());
            $date          = new \DateTime($completedDate);
            $date->setTimezone($timeZone);

            $schema = site_url('/') . $ordersData[0]->id . $date->format('y');

            return wp_create_nonce($schema);
        } catch (\Exception $e) {
            echo esc_html__('Error DateTime in progressiveUniqueCode: ') . $e->getMessage();
        }
    }

    /**
     * Format
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function formatTransmission($ordersData)
    {
        // Not set invoice type? FPR12 format default
        if (! property_exists($ordersData[0], 'invoice_type') ||
            empty($ordersData[0]->invoice_type) ||
            ! isset($ordersData[0]->invoice_type) ||
            (isset($ordersData[0]->invoice_type) && '' === $ordersData[0]->invoice_type)
        ) {
            return 'FPR12';
        }

        // Set format
        switch ($ordersData[0]->invoice_type) {
            case 'freelance':
            case 'private':
            case 'company':
                $format = 'FPR12';
                break;
            default:
                $format = '';
                break;
        }

        return $format;
    }

    /**
     * Code or Pec
     *
     * @param $ordersData
     * @param $type
     *
     * @return null|string
     * @since 1.0.0
     *
     */
    private function codeOrPec($ordersData, $type)
    {
        if (! property_exists($ordersData[0], 'sdi_type')) {
            return '';
        }

        $country = $this->customerCountry($ordersData);

        // Get pec
        $pec         = preg_match($this->regexPEC, $ordersData[0]->sdi_type) ? $ordersData[0]->sdi_type : null;
        $legalMail   = preg_match($this->regexLEGALMAIL, $ordersData[0]->sdi_type) ? $ordersData[0]->sdi_type : null;
        $generalMail = false !== filter_var($ordersData[0]->sdi_type,
            FILTER_VALIDATE_EMAIL) ? $ordersData[0]->sdi_type : null;

        if (null === $pec && null === $legalMail && $generalMail) {
            $pec = $generalMail;
        }

        $pec = $pec ?: $legalMail;

        $invoiceType = $ordersData[0]->invoice_type;

        $code     = '';
        $emailPec = '';

        switch ($invoiceType) {
            case 'private':
                $code     = ! preg_match($this->regexWEBSERV, $ordersData[0]->sdi_type) ?
                    '0000000' : $ordersData[0]->sdi_type;
                $emailPec = '0000000' !== $code ? $pec : null;
                break;
            case 'freelance':
            case 'company':
                $code     = ! preg_match($this->regexWEBSERV,
                    $ordersData[0]->sdi_type) && $pec || '' === $ordersData[0]->sdi_type ?
                    '0000000' : $ordersData[0]->sdi_type;
                $emailPec = '0000000' === $code ? $pec : null;
                break;
            default:
                $emailPec = '';
                $code     = '0000000';
                break;
        }

        switch ($type) {
            case 'pec':
                return $emailPec;
                break;
            case 'code':
                return 'IT' === $country ? strtoupper($code) : self::NO_IT_SDI_CODE;
                break;
            default:
                return '';
                break;
        }
    }

    /**
     * Customer country
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerCountry($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        return $ordersData[0]->billing['country'] ?? '';
    }

    /**
     * Customer vat
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerVatNumber($ordersData)
    {
        if (! property_exists($ordersData[0], 'vat_number')) {
            return '';
        }

        return isset($ordersData[0]->vat_number) ? $ordersData[0]->vat_number : '';
    }

    /**
     * Customer Tax Code
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerTaxCodeNumber($ordersData)
    {
        if (! property_exists($ordersData[0], 'tax_code')) {
            return '';
        }

        $euVatCountry = GeneralFields::getEuCountries();
        $country      = $this->customerCountry($ordersData);

        if ('private' !== $ordersData[0]->invoice_type) {
            // If VAT format
            if (preg_match($this->regexVAT, $country . $ordersData[0]->tax_code)) {
                $taxCode = $ordersData[0]->tax_code;

                return isset($ordersData[0]->tax_code) ? strtoupper($taxCode) : '';
            }
        }

        if ((! in_array($country, $euVatCountry, true) || $country !== 'IT') &&
            'private' === $ordersData[0]->invoice_type
        ) {
            return '';
        }

        return isset($ordersData[0]->tax_code) && preg_match($this->regexCF, $ordersData[0]->tax_code) ?
            strtoupper($ordersData[0]->tax_code) : '';
    }

    /**
     * Customer company
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerCompany($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        $customerCompany = isset($ordersData[0]->billing['company']) ? $ordersData[0]->billing['company'] : '';
        $customerCompany = str_replace('`', "&rsquo;", $customerCompany);
        $customerCompany = str_replace('´', "&rsquo;", $customerCompany);

        if ('private' === $ordersData[0]->invoice_type) {
            return '';
        }

        return \WcElectronInvoiceFree\Functions\stripTags($customerCompany);
    }

    /**
     * Customer name
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerName($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        $name = isset($ordersData[0]->billing['first_name']) ? $ordersData[0]->billing['first_name'] : '';
        $name = str_replace('`', "&rsquo;", $name);
        $name = str_replace('´', "&rsquo;", $name);

        return $name;
    }

    /**
     * Customer last name
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerLastName($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        $lastName = isset($ordersData[0]->billing['last_name']) ? $ordersData[0]->billing['last_name'] : '';
        $lastName = str_replace('`', "&rsquo;", $lastName);
        $lastName = str_replace('´', "&rsquo;", $lastName);

        return $lastName;
    }

    /**
     * Customer address
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerAddress($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        $address = isset($ordersData[0]->billing['address_1']) ? $ordersData[0]->billing['address_1'] : '';

        return \WcElectronInvoiceFree\Functions\stripTags($address);
    }

    /**
     * Customer post code
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerPostCode($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        $postCode = $ordersData[0]->billing['postcode'] ?? '';

        $country      = $this->customerCountry($ordersData);
        $euVatCountry = GeneralFields::getEuCountries();
        if ('' === $postCode && (! in_array($country, $euVatCountry, true) || 'IT' !== $country)) {
            $postCode = '00000';
        }

        return $postCode;
    }

    /**
     * Customer city
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerCity($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        return isset($ordersData[0]->billing['city']) ? $ordersData[0]->billing['city'] : '';
    }

    /**
     * Customer state
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function customerState($ordersData)
    {
        if (! property_exists($ordersData[0], 'billing')) {
            return '';
        }

        return isset($ordersData[0]->billing['state']) ? $ordersData[0]->billing['state'] : '';
    }

    /**
     * Payment Method
     *
     * @param $order
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function paymentMethod($order)
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
            $method = $order->refunded['refunded_payment_method'];
            if ('' === $order->refunded['refunded_payment_method']) {
                $method = 'MP01';
            }

            return $method;
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
            switch ($order->payment_method) {
                case 'bacs':
                    return 'MP05';
                case 'cheque':
                    return 'MP02';
                case 'paypal':
                case 'ppec_paypal':
                case 'ppcp-gateway':
                case 'stripe':
                case 'soisy':
                    return 'MP08';
                case 'stripe_sepa':
                    return 'MP19';
                    break;
                default:
                    return apply_filters('wc_el_inv-default_payment_method_xml_invoice', 'MP01',
                        $order->payment_method);
                    break;
            }
        }
    }

    /**
     * Product Description
     *
     * @param      $item
     * @param null $type
     *
     * @return string|string[]|null
     * @since 1.0.0
     *
     */
    private function productDescription($item, $type = null)
    {
        if (! isset($item['product_id'])) {
            return '';
        }

        $post = get_post(intval($item['product_id']));

        /**
         * Filter - description excerpt + title or only title
         */
        if (apply_filters('wc_el_inv-product_excerpt_description_xml_invoice', true) && $post->post_excerpt) {
            $description = $post->post_title . ' ' . $post->post_excerpt;
        } else {
            $description = $post->post_title;
        }

        /**
         * Filter - force description only post title
         */
        if (true === apply_filters('wc_el_inv-product_title_description_xml_invoice', false)) {
            $description = $post->post_title;
        }

        /**
         * Filter - Product Meta data
         */
        if (true === apply_filters('wc_el_inv-product_meta_description_xml_invoice', false)) {
            $metaString = '';
            if (! empty($item['meta_data'])) {
                foreach ($item['meta_data'] as $index => $meta) {
                    $sep        = $index === count($item['meta_data']) - 1 ? '' : ', ';
                    $metaString = $metaString . "{$meta['key']}: {$meta['value']}{$sep}";
                }

                $description = "{$description} {$metaString}";
            }
        }

        /**
         * Filter - description for item
         */
        $description = apply_filters('wc_el_inv-product_description_xml_invoice', $description, $item);

        if ('refund' === $type) {
            $description = sprintf('%s %s', esc_html__('Refund: ', WC_EL_INV_TEXTDOMAIN), "{$description}");
        }

        $description = mb_strimwidth($description, 0, 500, '');

        return \WcElectronInvoiceFree\Functions\stripTags($description, true);
    }

    /**
     * Document type
     *
     * @param $order
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function documentType($order)
    {
        if (! property_exists($order, 'order_type')) {
            return '';
        }

        $docType = '';

        if (isset($order->order_type)) {
            // Set doc type
            switch ($order->order_type) {
                case 'shop_order':
                    $docType = 'TD01';
                    break;
                case 'shop_order_refund':
                    $docType = 'TD04';
                    break;
                default:
                    $docType = '';
                    break;
            }
        }

        return $docType;
    }

    /**
     * Tax Rate
     *
     * @param        $item
     * @param string $get
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function taxRate($item, $get = 'rate')
    {
        if (! isset($item['product_id']) && ! isset($item['variation_id'])) {
            return '';
        }

        $taxEnabled = get_option('woocommerce_calc_taxes');
        if ('yes' !== $taxEnabled) {
            return floatval(0);
        }

        $taxStatus = '';
        $taxRates  = array();

        $id      = isset($item['variation_id']) && 0 !== intval($item['variation_id']) ? intval($item['variation_id']) : intval($item['product_id']);
        $product = wc_get_product($id);
        if ($product instanceof \WC_Product) {
            // Get rates by product tax class
            $taxRates  = \WC_Tax::get_rates($product->get_tax_class());
            $taxStatus = $product->get_tax_status();

            // Double check get rates by billing country
            $order = wc_get_order($item['order_id']);
            $taxes = \WC_Tax::get_rates_for_tax_class($product->get_tax_class());
            foreach ($taxes as $tax) {
                if ($tax->tax_rate_country === $order->get_billing_country()) {
                    return $this->numberFormat($tax->tax_rate, 0);
                    break;
                }
            }
        }

        if (empty($taxRates)) {
            $taxRates = \WC_Tax::get_base_tax_rates();
        }

        $taxRate = reset($taxRates);

        switch ($get) {
            case 'rate':
                return $taxStatus === 'taxable' && floatval(0) !== floatval($item['total_tax']) ? $taxRate[$get] : floatval(0);
            default:
                return '';
                break;
        }
    }

    /**
     * Vies valid or Extra UE TAX rate
     *
     * @param $ordersData
     * @param $defaultVat
     * @param $vies
     *
     * @return int
     */
    private function viesValidTaxRate($ordersData, $defaultVat, $vies)
    {
        // UE valid VIES rate or Extra UE
        if ($vies &&
            in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true) &&
            'IT' !== $this->customerCountry($ordersData)
        ) {
            return 0;
        } elseif (! in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true)) {
            return 0;
        }

        return $defaultVat;
    }

    /**
     * Shipping Rate
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function shippingRate()
    {
        $taxRates = \WC_Tax::get_shipping_tax_rates();
        if (empty($taxRates)) {
            $taxRates = \WC_Tax::get_base_tax_rates();
        }

        $taxRate = reset($taxRates);
        if ('no' === $taxRate['shipping']) {
            return '';
        }

        return $taxRate['rate'];
    }

    /**
     * Invoice Number
     *
     * @param $order
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function invoiceNumber($order)
    {
        $wcOrderClass       = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order');
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');

        if ($order instanceof $wcOrderClass ||
            $order instanceof $wcOrderRefundClass
        ) {
            $id = $order->get_id();
        } else {
            $id = $order->id;
        }

        $order = wc_get_order($id);
        if (! $order instanceof $wcOrderClass && ! $order instanceof $wcOrderRefundClass) {
            return '';
        }

        $options = OptionPage::init();
        $number  = $order->get_meta('order_number_invoice');

        if ($order instanceof $wcOrderRefundClass) {
            $number = $order->get_meta("refund_number_invoice-{$order->get_id()}");
        }

        // Number of digits
        $digits = $options->getOptions('number_digits_in_invoice');
        $digits = isset($digits) && '' !== $digits ? $digits : 2;
        // Prefix
        $prefix = $options->getOptions('prefix_invoice_number');
        $prefix = isset($prefix) && '' !== $prefix ? "{$prefix}-" : 'inv-';

        /**
         * Invoice prefix filter
         */
        $prefix = apply_filters('wc_el_inv-prefix_invoice', $prefix, $order);

        // Suffix
        $suffix     = $options->getOptions('suffix_invoice_number');
        $suffixYear = $options->getOptions('suffix_year_invoice_number');
        if ('on' === $suffixYear) {
            $created = $order->get_date_created();
            $suffix  = "/" . $created->format('Y');
        } else {
            $suffix = isset($suffix) && '' !== $suffix ? $suffix : '';
        }

        /**
         * Invoice suffix filter
         */
        $suffix = apply_filters('wc_el_inv-suffix_invoice', $suffix, $order);

        // Invoice number
        $invNumber = str_pad($number, $digits, '0', STR_PAD_LEFT);

        return isset($number) && 0 !== $number && '' !== $number ? "{$prefix}{$invNumber}{$suffix}" : $order->get_id();
    }

    /**
     * Progressive file number
     *
     * @param $ordersData
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function progressiveFileNumber($ordersData)
    {
        if (! property_exists($ordersData[0], 'id')) {
            return '';
        }

        $order              = wc_get_order($ordersData[0]->id);
        $wcOrderRefundClass = \WcElectronInvoiceFree\Functions\wcOrderClassName('\WC_Order_Refund');

        $number = $order->get_meta('order_number_invoice');

        if ($order instanceof $wcOrderRefundClass) {
            $number = $order->get_meta("refund_number_invoice-{$order->get_id()}");
        }

        if ($number >= 1) {
            $number = base_convert($number, 10, 36);
            $number = str_pad($number, 5, '0', STR_PAD_LEFT);

            return strtoupper($number);
        }
    }

    /**
     * Increment number
     *
     * @return int
     * @since 1.0.0
     *
     */
    private function increment()
    {
        $number = self::$sentNumber++;

        return str_pad($number, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Doc ID
     *
     * @param $order
     *
     * @return int The doc ID
     * @since 1.0.0
     *
     */
    private function docID($order)
    {
        $id = $order->id;

        // If Refund get id of the parent.
        if ('shop_order_refund' === $order->order_type) {
            $order = wc_get_order($order->parent_id);

            // return parent invoice number
            return $this->invoiceNumber($order);
        }

        return intval($id);
    }

    /**
     * Number Format
     *
     * @param int  $number
     * @param int  $decimal
     * @param bool $abs
     *
     * @return string
     * @since 1.0.0
     *
     */
    private function numberFormat($number = 0, $decimal = 2, $abs = true)
    {
        if ($abs) {
            $number = abs($number);
        }

        // specific number format for xml
        return number_format($number, $decimal, '.', '');
    }

    /**
     * Percentage Discount
     *
     * @param $initial
     * @param $discounted
     *
     * @return float|int
     * @since 1.0.0
     *
     */
    private function percentageDiscount($initial, $discounted)
    {
        if ($discounted) {
            return (($initial - $discounted) / $initial) * 100;
        }
    }

    /**
     * Total
     *
     * @param $order
     * @param $summary
     *
     * @return mixed  The total order or total refunded
     * @since 1.0.0
     *
     */
    private function total($order, $summary = array())
    {
        if ('shop_order_refund' === $order->order_type) {
            $invoiceSent = get_post_meta($order->parent_id, '_invoice_sent', true);

            $orderTotals = 0;
            if (! empty($summary)) {
                foreach ($summary as $index => $totals) {
                    foreach ($totals as $total) {
                        $orderTotals += ($total['total'] + $total['total_tax']);
                    }
                }

                if ('sent' === $invoiceSent) {
                    return $orderTotals;
                }
            }

            return $order->amount;
        } elseif ('shop_order' === $order->order_type) {
            return $order->total;
        }

        // Default totals
        return $order->total;
    }

    /**
     * Save xml files in temp dir
     *
     * @param $fileName
     *
     * @return string The file path
     * @since 1.1.0
     *
     */
    private function saveXmlFiles($fileName)
    {
        $file = Plugin::getPluginDirPath('/') . '/tempXml/' . $fileName . '.xml';

        $this->xml->asXML($file);

        ob_start();
        $data = file_get_contents($file);
        $data = preg_replace('/xmlns:p:FatturaElettronica/', 'p:FatturaElettronica', $data);
        file_put_contents($file, $data);
        $data = ob_get_clean();

        return $file;
    }

    /**
     * Filter Order data
     *
     * @param $ordersData
     *
     * @return mixed
     */
    public static function filterData($ordersData)
    {
        $orderID       = $ordersData['id'];
        $orderType     = $ordersData['order_type'];
        $orderRefunded = $ordersData['refunded'];
        $orderTotal    = $ordersData['total'];

        $sentInvoiceCheck = get_post_meta($orderID, '_invoice_sent', true);

        if ('shop_order' === $orderType && 'no_sent' === $sentInvoiceCheck && ! empty($orderRefunded)) {
            $ordersData['total'] = $orderTotal - $orderRefunded['total_refunded'];
        }

        return $ordersData;
    }

    /**
     * Calc Unit price from total and total tax
     *
     * @param      $item
     * @param null $ordersData
     * @param null $vies
     *
     * @return float|int
     * @since 2.2.0
     */
    public function calcUnitPrice($item, $ordersData = null, $vies = null)
    {
        $total    = isset($item['total']) ? $item['total'] : 0;
        $subtotal = isset($item['subtotal']) ? $item['subtotal'] : 0;
        $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;
        $quantity = isset($item['quantity']) ? $item['quantity'] : 1;

        if ($subtotal > $total) {
            $unitTaxedPrice = ($subtotal / $quantity);
        } else {
            $unitTaxedPrice = (($total + $totalTax) / $quantity);
        }
        //$unitTaxedPrice = $this->numberFormat($unitTaxedPrice, 6);
        // Vat
        $taxEnabled = get_option('woocommerce_calc_taxes');
        $vat        = 0;
        if ('yes' === $taxEnabled) {
            $vat = $this->numberFormat($this->taxRate($item));
            if ($ordersData && $vies) {
                $vat = $this->viesValidTaxRate($ordersData, $vat, $vies);
            }
        }

        if ($subtotal > $total) {
            $finalPrice = floatval($unitTaxedPrice);
        } else {
            $finalPrice = floatval($unitTaxedPrice) / (1 + (floatval($vat) / 100));
        }

        return $this->numberFormat($finalPrice, 6); // es: $unitTaxedPrice / 1,22 or 1.04
    }

    /**
     * Create
     *
     * @param $ordersData
     *
     * @return string The file path
     * @since 1.0.0
     *
     */
    public function create($ordersData)
    {
        // @codingStandardsIgnoreLine
        $view       = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'view', FILTER_UNSAFE_RAW);
        $saveFile   = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'save', FILTER_UNSAFE_RAW);
        $saveBulk   = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'all', FILTER_UNSAFE_RAW);
        $customerID = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'customer_id', FILTER_UNSAFE_RAW);
        // Referer used for add XML tag or custom tag
        $referer = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'referer', FILTER_UNSAFE_RAW);
        // Override choice_type from $_GET['choice_type'] value
        $choiceType = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'choice_type', FILTER_UNSAFE_RAW);
        if ($choiceType) {
            $ordersData[0]->choice_type = $choiceType;
        }

        // Set version.
        $version = $this->version($ordersData);
        $this->xml->addAttribute('versione', $version);
        // Set attributes
        $protocol = 'http://';
        $this->xml->addAttribute('xmlns:xmlns:p', "{$protocol}ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2");
        $this->xml->addAttribute('xmlns:xmlns:ds', "{$protocol}www.w3.org/2000/09/xmldsig#e");
        $this->xml->addAttribute('xmlns:xmlns:xsi', "{$protocol}www.w3.org/2001/XMLSchema-instance");
        $this->xml->addAttribute('xmlns:xsi:schemaLocation',
            "{$protocol}ivaservizi.agenziaentrate.gov.it/docs/xsd/fatture/v1.2 {$protocol}www.fatturapa.gov.it/export/documenti/fatturapa/v1.2.1/Schema_del_file_xml_FatturaPA_versione_1.2.xsd");

        // Xml file name
        $fileName = GeneralFields::getGeneralInvoiceOptionCountryState() .
                    GeneralFields::getGeneralInvoiceOptionVatNumber() . '_' .
                    $this->progressiveFileNumber($ordersData);

        // Add stylesheet
        if ('true' === $view) {
            $style = Plugin::getPluginDirUrl('/assets/css/invoice/fatturaPA_v1.2.1.xsl');
            $this->addProcessingInstruction(
                'xml-stylesheet',
                "type='text/xsl' href='{$style}'"
            );
        }

        // Save xml file if not save bulk in query
        if (isset($saveFile) && 'true' === $saveFile && false === $saveBulk) {
            // Set header for open dialog and save xml file.
            @header("Content-Disposition: attachment;filename={$fileName}.xml");
        }

        // Set global header if not save bulk in query
        if (! isset($saveBulk) || 'true' !== $saveBulk) {
            @header('Content-Type: text/xml; charset=' . get_option('blog_charset'), true);
        }

        // *********************************** //
        // Start XML
        // *********************************** //
        if (! empty($ordersData)) {

            // ****************************************************************************************************** //
            // Start Invoice Header
            // ****************************************************************************************************** //
            #1
            $header = $this->xml->addChild('FatturaElettronicaHeader');

            // #1.1
            // transmitter data
            $transmitterVat   = GeneralFields::getGeneralInvoiceTransmitterOptionVatNumber();
            $transmitterPhone = GeneralFields::getGeneralInvoiceTransmitterOptionPhone();
            $transmitterEmail = GeneralFields::getGeneralInvoiceTransmitterOptionEmail();

            $dataHeader = $header->addChild('DatiTrasmissione');

            $idTransmitter = $dataHeader->addChild('IdTrasmittente');
            $idTransmitter->addChild('IdPaese', GeneralFields::getGeneralInvoiceOptionCountryState());

            if ($transmitterVat) {
                $idTransmitter->addChild('IdCodice', $transmitterVat);
            } else {
                $idTransmitter->addChild('IdCodice', GeneralFields::getGeneralInvoiceOptionVatNumber());
            }

            $dataHeader->addChild('ProgressivoInvio', $this->progressiveUniqueCode($ordersData));
            $dataHeader->addChild('FormatoTrasmissione', $this->formatTransmission($ordersData));
            $dataHeader->addChild('CodiceDestinatario', $this->codeOrPec($ordersData, 'code'));

            if ($transmitterPhone || $transmitterEmail) {
                $contactTransmitter = $dataHeader->addChild('ContattiTrasmittente');
                if ($transmitterPhone) {
                    $contactTransmitter->addChild('Telefono', $transmitterPhone);
                }
                if ($transmitterEmail) {
                    $contactTransmitter->addChild('Email', $transmitterEmail);
                }
            } else {
                if (GeneralFields::getGeneralInvoiceOptionPhoneNumber() || GeneralFields::getGeneralInvoiceOptionEmailAddress()) {
                    $contactTransmitter = $dataHeader->addChild('ContattiTrasmittente');
                    if (GeneralFields::getGeneralInvoiceOptionPhoneNumber()) {
                        $contactTransmitter->addChild('Telefono', GeneralFields::getGeneralInvoiceOptionPhoneNumber());
                    }
                    if (GeneralFields::getGeneralInvoiceOptionEmailAddress()) {
                        $contactTransmitter->addChild('Email', GeneralFields::getGeneralInvoiceOptionEmailAddress());
                    }
                }
            }

            if ($this->codeOrPec($ordersData, 'pec') &&
                'private' !== $ordersData[0]->invoice_type &&
                '0000000' === $this->codeOrPec($ordersData, 'code') ||
                '999999' === $this->codeOrPec($ordersData, 'code')
            ) {
                $dataHeader->addChild('PECDestinatario', $this->codeOrPec($ordersData, 'pec'));
            }

            // #1.2
            $transferLender = $header->addChild('CedentePrestatore');
            $personalData   = $transferLender->addChild('DatiAnagrafici');
            $vatData        = $personalData->addChild('IdFiscaleIVA');
            $vatData->addChild('IdPaese', GeneralFields::getGeneralInvoiceOptionCountryState());
            $vatData->addChild('IdCodice', GeneralFields::getGeneralInvoiceOptionVatNumber());
            $identity = $personalData->addChild('Anagrafica');

            if (GeneralFields::getGeneralInvoiceOptionCompanyName()) {
                $identity->addChild('Denominazione', GeneralFields::getGeneralInvoiceOptionCompanyName());
            }
            if (! GeneralFields::getGeneralInvoiceOptionCompanyName()) {
                $identity->addChild('Nome', GeneralFields::getGeneralInvoiceOptionName());
                $identity->addChild('Cognome', GeneralFields::getGeneralInvoiceOptionSurname());
            }

            $personalData->addChild('RegimeFiscale', GeneralFields::getGeneralInvoiceOptionTaxRegime());
            $venue = $transferLender->addChild('Sede');
            $venue->addChild('Indirizzo', GeneralFields::getGeneralInvoiceOptionAddress());
            $venue->addChild('CAP', GeneralFields::getGeneralInvoiceOptionPostCode());
            $venue->addChild('Comune', GeneralFields::getGeneralInvoiceOptionCity());
            $venue->addChild('Provincia', GeneralFields::getGeneralInvoiceOptionCountryProvince());
            $venue->addChild('Nazione', GeneralFields::getGeneralInvoiceOptionCountryState());

            // #1.2.4
            $reaOffice = GeneralFields::getGeneralInvoiceOptionProvinceOfficeBusinessRegister();
            $reaNumber = GeneralFields::getGeneralInvoiceOptionReaNumber();
            $reaStatus = GeneralFields::getGeneralInvoiceOptionLiquidationStatus();
            if ($reaOffice && $reaNumber && $reaStatus) {
                $rea = $transferLender->addChild('IscrizioneREA');
                $rea->addChild('Ufficio', $reaOffice);
                $rea->addChild('NumeroREA', $reaNumber);
                $rea->addChild('StatoLiquidazione', $reaStatus);
            }

            // 1.2.5
            if (GeneralFields::getGeneralInvoiceOptionPhoneNumber() || GeneralFields::getGeneralInvoiceOptionEmailAddress()) {
                $contact = $transferLender->addChild('Contatti');
                if (GeneralFields::getGeneralInvoiceOptionPhoneNumber()) {
                    $contact->addChild('Telefono', GeneralFields::getGeneralInvoiceOptionPhoneNumber());
                }
                if (GeneralFields::getGeneralInvoiceOptionEmailAddress()) {
                    $contact->addChild('Email', GeneralFields::getGeneralInvoiceOptionEmailAddress());
                }
            }

            // #1.4
            $customerAssignee = $header->addChild('CessionarioCommittente');
            $personalData     = $customerAssignee->addChild('DatiAnagrafici');
            if ('private' !== $ordersData[0]->invoice_type) {
                // No private customer conditions

                if (! in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true)) {
                    // No UE vat
                    $vatData = $personalData->addChild('IdFiscaleIVA');
                    $vatData->addChild('IdPaese', $this->customerCountry($ordersData));
                    if ('' !== $this->customerVatNumber($ordersData)) {
                        $vatData->addChild('IdCodice', $this->customerVatNumber($ordersData));
                    } else {
                        $vatData->addChild('IdCodice', '9999999999');
                    }
                } elseif (in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true) &&
                          $this->customerCountry($ordersData) !== 'IT'
                ) {
                    // UE no IT vat
                    $vatData = $personalData->addChild('IdFiscaleIVA');
                    $vatData->addChild('IdPaese', $this->customerCountry($ordersData));
                    if ('' !== $this->customerVatNumber($ordersData)) {
                        $vatData->addChild('IdCodice', $this->customerVatNumber($ordersData));
                    } else {
                        $vatData->addChild('IdCodice', '9999999999');
                    }
                } else {
                    // IT vat
                    $vatData = $personalData->addChild('IdFiscaleIVA');
                    $vatData->addChild('IdPaese', $this->customerCountry($ordersData));
                    $vatData->addChild('IdCodice', $this->customerVatNumber($ordersData));
                    if ('' !== $ordersData[0]->tax_code) {
                        $personalData->addChild('CodiceFiscale', $this->customerTaxCodeNumber($ordersData));
                    }
                }
            } else {
                // Private customer conditions

                if (in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true) &&
                    'IT' === $this->customerCountry($ordersData) &&
                    '' !== $this->customerTaxCodeNumber($ordersData)
                ) {
                    // IT tax code
                    $personalData->addChild('CodiceFiscale', $this->customerTaxCodeNumber($ordersData));
                } elseif (in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true) &&
                          'IT' !== $this->customerCountry($ordersData)
                ) {
                    // UE no IT tax code
                    $vatData = $personalData->addChild('IdFiscaleIVA');
                    $vatData->addChild('IdPaese', $this->customerCountry($ordersData));
                    $vatData->addChild('IdCodice', '0000000');
                } elseif (! in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true) &&
                          $this->customerCountry($ordersData) !== 'IT'
                ) {
                    // No UE tax code
                    $vatData = $personalData->addChild('IdFiscaleIVA');
                    $vatData->addChild('IdPaese', $this->customerCountry($ordersData));
                    $vatData->addChild('IdCodice', '0000000');
                }
            }

            $identity = $personalData->addChild('Anagrafica');
            if ($this->customerCompany($ordersData)) {
                $identity->addChild('Denominazione', $this->customerCompany($ordersData));
            } else {
                $identity->addChild('Nome', $this->customerName($ordersData));
                $identity->addChild('Cognome', $this->customerLastName($ordersData));
            }

            $venue = $customerAssignee->addChild('Sede');
            $venue->addChild('Indirizzo', $this->customerAddress($ordersData));
            $venue->addChild('CAP', $this->customerPostCode($ordersData));
            $venue->addChild('Comune', $this->customerCity($ordersData));
            if ('IT' === $this->customerCountry($ordersData)) {
                $venue->addChild('Provincia', $this->customerState($ordersData));
            }
            $venue->addChild('Nazione', $this->customerCountry($ordersData));

            // ****************************************************************************************************** //
            // End Invoice Header
            // ****************************************************************************************************** //

            /**
             * Filter Data after header zone XML
             *
             * @since 1.0.0
             */
            $this->xml = apply_filters(
                'wc_el_inv-created_xml_after_header_zone',
                $this->xml,
                $header,
                $ordersData
            );

            // ****************************************************************************************************** //
            // Start Invoice Body
            // ****************************************************************************************************** //

            foreach ($ordersData as $order) {

                /**
                 * Filter XML order
                 *
                 * @since 4.1.5
                 */
                $order = apply_filters('wc_el_inv-xml_order', $order);

                // Disabled
                $nature  = null;
                $refNorm = null;

                // #2
                $body = $this->xml->addChild('FatturaElettronicaBody');

                // ************************************************************************************************** //
                // Start General data line
                // ************************************************************************************************** //

                // #2.1
                $dataBody        = $body->addChild('DatiGenerali');
                $generalDataBody = $dataBody->addChild('DatiGeneraliDocumento');
                $generalDataBody->addChild('TipoDocumento', $this->documentType($order));
                $generalDataBody->addChild('Divisa', apply_filters('wc_el_inv-xml_currency', $order->currency));
                // Date invoice
                $generalDataBody->addChild('Data', $this->dateInvoice($order));
                $generalDataBody->addChild('Numero', $this->invoiceNumber($order));
                $generalDataBody->addChild('ImportoTotaleDocumento', $this->numberFormat($this->total($order)));

                // Refund document
                if ('shop_order_refund' === $order->order_type) {
                    $dataOrder = $dataBody->addChild('DatiFattureCollegate');
                    $dataOrder->addChild('IdDocumento', "{$this->docID($order)}");
                    // Date order "created"
                    $dataOrder->addChild('Data', $this->dateCompleted($order, true));
                } else {
                    $dataOrder = $dataBody->addChild('DatiOrdineAcquisto');
                    $dataOrder->addChild('IdDocumento', "#{$this->docID($order)}");
                    // Date order "created"
                    $dataOrder->addChild('Data', $this->dateCompleted($order));
                }

                // Virtual Duty 2.1.1.6
                if ('shop_order' === $order->order_type && ! wc_tax_enabled()) {
                    $addDuty = OptionPage::init()->getOptions('add_stamp_duty');
                    if ('on' === $addDuty) {
                        if (isset($ordersData[0]->total_tax) && 0 === (int)$ordersData[0]->total_tax) {
                            if (floatval($ordersData[0]->total) > floatval('77.47')) {
                                $dutyData = $generalDataBody->addChild('DatiBollo');
                                $dutyData->addChild('BolloVirtuale', 'SI');
                                $dutyData->addChild('ImportoBollo', floatval('2.00'));
                            }
                        }
                    }
                }

                // ************************************************************************************************** //
                // End General data line
                // ************************************************************************************************** //

                /**
                 * Filter Data after general data line zone XML
                 *
                 * @since 1.0.0
                 */
                $this->xml = apply_filters(
                    'wc_el_inv-created_xml_after_general_data_line_zone',
                    $this->xml,
                    $dataBody,
                    $ordersData
                );

                // ************************************************************************************************** //
                // Start Detail product line
                // ************************************************************************************************** //

                /**
                 * Get Vies data for check
                 */
                $invoiceViesCheck = OptionPage::init()->getOptions('invoice_vies_check');
                $vies             = false;
                if ('on' === $invoiceViesCheck) {
                    $vies = InvoiceFields::viesCheck(
                        $this->customerVatNumber($ordersData),
                        $this->customerCountry($ordersData), true
                    );

                    // Exception
                    if (! empty($vies) && is_array($vies) && isset($vies['code'])) {
                        $vies = false;
                    }
                }

                // #2.2
                $dataProduct = $body->addChild('DatiBeniServizi');
                // Shop Order
                if (! empty($order->items) && 'shop_order' === $order->order_type) {
                    $i              = 1;
                    $checkSentOrder = get_post_meta($order->id, '_invoice_sent', true);
                    if ('sent' !== $checkSentOrder) {
                        // Recalculate if refund item
                        if (! empty($order->items_refunded)) {
                            foreach ($order->items_refunded as $index => $itemRefund) {
                                foreach ($order->items as $key => $lineItem) {
                                    if ($lineItem['product_id'] === $itemRefund['product_id']) {
                                        $newQty      = abs($lineItem['quantity']) - abs($itemRefund['qty_refunded_for_item']);
                                        $newSubTotal = ($lineItem['subtotal'] / $lineItem['quantity']);
                                        $newSubTotal = ($newSubTotal * $newQty);
                                        $newTotal    = ($newSubTotal * $newQty);

                                        $newSubTotalTax                          = ($lineItem['subtotal_tax'] / $lineItem['quantity']);
                                        $newSubTotalTax                          = ($newSubTotalTax * $newQty);
                                        $newTotalTax                             = ($newSubTotalTax * $newQty);
                                        $order->items[$key]['quantity']          = "{$newQty}";
                                        $order->items[$key]['subtotal']          = "{$newSubTotal}";
                                        $order->items[$key]['total']             = "{$newTotal}";
                                        $order->items[$key]['subtotal_tax']      = "{$newSubTotalTax}";
                                        $order->items[$key]['total_tax']         = "{$newTotalTax}";
                                        $order->items[$key]['taxes']['total']    = "{$newTotalTax}";
                                        $order->items[$key]['taxes']['subtotal'] = "{$newSubTotalTax}";
                                    }
                                }
                            }
                        }
                    }

                    // Shop order items
                    foreach ($order->items as $item) {
                        $id      = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
                        $product = wc_get_product(intval($id));
                        // product code
                        $code = isset($id) ? "{$id}" : '';

                        // Total item refund
                        if (0 === intval($item['quantity'])) {
                            continue;
                        }

                        // #2.2.1
                        $detailLine = $dataProduct->addChild('DettaglioLinee');
                        $detailLine->addChild('NumeroLinea', $i);
                        $productCode = $detailLine->addChild('CodiceArticolo');
                        $productCode->addChild('CodiceTipo', self::CODE_TYPE);
                        $productCode->addChild('CodiceValore', $code);
                        $detailLine->addChild('Descrizione', $this->productDescription($item));
                        $detailLine->addChild('Quantita', $this->numberFormat($item['quantity']));
                        $detailLine->addChild('UnitaMisura', 'N.');

                        // Set discount unit and total
                        $discountUnit  = $this->numberFormat((($item['subtotal'] - $item['total']) / abs($item['quantity'])),
                            6);
                        $discountTotal = $this->numberFormat((($item['subtotal'] - $item['total'])), 6);

                        // Set Unit Price if have discount or not
                        $unitPrice = $this->numberFormat($this->calcUnitPrice($item, $ordersData, $vies), 6);
                        if ('0' !== $order->discount_total && $this->numberFormat($item['subtotal']) > $this->numberFormat($item['total'])) {
                            // Unit Price
                            $detailLine->addChild('PrezzoUnitario', $unitPrice);

                            // Set Total based on XML tag type
                            if ('pop' === $referer &&
                                '0' !== $order->discount_total &&
                                $this->numberFormat($item['subtotal']) > $this->numberFormat($item['total'])
                            ) {
                                // Total
                                $total = ($unitPrice * abs($item['quantity'])) - $discountTotal;
                                $detailLine->addChild('PrezzoTotale', $this->numberFormat($total, 6));
                            } else {
                                // Total
                                $total = ($unitPrice * abs($item['quantity']));
                                $detailLine->addChild('PrezzoTotale', $this->numberFormat($total, 6));
                            }
                        } else {
                            // Unit Price
                            $detailLine->addChild('PrezzoUnitario', $unitPrice);
                            // Total
                            $total = ($unitPrice * abs($item['quantity']));
                            $detailLine->addChild('PrezzoTotale', $this->numberFormat($total, 6));
                        }

                        /**
                         * 1. Company UE and valid VIES
                         * 2. Company and Private extra UE
                         * 3. All other cases (including EU companies with invalid VIES)
                         */
                        // Regime forfettario
                        if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                            $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                            $detailLine->addChild('Natura', 'N2.2');
                        } else {
                            if ($nature && true === $vies &&
                                in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true) &&
                                'IT' !== $this->customerCountry($ordersData)
                            ) {
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                $detailLine->addChild('Natura', $nature);
                            } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                    GeneralFields::getEuCountries(), true)) {
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                $detailLine->addChild('Natura', $nature);
                            } else {
                                // Zero rate if total tax is zero
                                if ((floatval(0) === floatval($item['total_tax']))) {
                                    $rate = 0;
                                } else {
                                    $rate = $this->taxRate($item);
                                }
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat($rate));
                                if ($product instanceof \WC_Product && 'none' === $product->get_tax_status() ||
                                    (floatval(0) === floatval($item['total_tax']))
                                ) {
                                    $detailLine->addChild('Natura', 'N4');
                                } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                        GeneralFields::getEuCountries(), true)) {
                                    $detailLine->addChild('Natura', $nature);
                                }
                            }
                        }
                        $i++;

                        // Any discount or increase applied to the unit price (the multiplicity N of the block
                        // allows you to manage the presence of multiple discounts or 'cascade' increases)
                        // #2.2.1.10
                        if ('0' !== $order->discount_total &&
                                  $this->numberFormat($item['subtotal']) > $this->numberFormat($item['total'])
                        ) {
                            // Zero rate if total tax is zero
                            if ((floatval(0) === floatval($item['total_tax']))) {
                                $rate = 0;
                            } else {
                                $rate = $this->taxRate($item);
                            }

                            // Discount line
                            // #2.2.1.10
                            $detailLine = $dataProduct->addChild('DettaglioLinee');
                            $detailLine->addChild('NumeroLinea', $i);
                            $detailLine->addChild('TipoCessionePrestazione', 'SC');
                            $detailLine->addChild('Descrizione', sprintf('%s %s&#37;',
                                    esc_html__('Sconto su prodotto'),
                                    $this->numberFormat($this->percentageDiscount($item['subtotal'], $item['total']))
                                )
                            );
                            $detailLine->addChild('Quantita', $this->numberFormat(1));
                            $detailLine->addChild('UnitaMisura', 'N.');
                            // Unit Price
                            $detailLine->addChild('PrezzoUnitario', "-{$this->numberFormat($discountTotal, 6)}");
                            $detailLine->addChild('PrezzoTotale', "-{$this->numberFormat($discountTotal, 6)}");

                            /**
                             * 1. Company UE and valid VIES
                             * 2. Company and Private extra UE
                             * 3. All other cases (including EU companies with invalid VIES)
                             */
                            // Regime forfettario
                            if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                $detailLine->addChild('Natura', 'N2.2');
                            } else {
                                if ($nature && true === $vies &&
                                    in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(),
                                        true) &&
                                    'IT' !== $this->customerCountry($ordersData)
                                ) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                        GeneralFields::getEuCountries(),
                                        true)) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } else {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat($rate));
                                    if (floatval(0) === $rate) {
                                        $detailLine->addChild('Natura', 'N4');
                                    } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                            GeneralFields::getEuCountries(), true)) {
                                        $detailLine->addChild('Natura', $nature);
                                    }
                                }
                            }
                            $i++;
                        }
                    }

                    // ********************************************************************************************** //
                    // Not expected in the xml file, we manage it as a product!
                    // Shipping rate tax
                    // Shipping line
                    if ('disabled' !== GeneralFields::getGeneralShippingLocation()) {
                        if ($this->numberFormat(0) !== $this->numberFormat($order->shipping_total)) {
                            // Check if is refund total shipping
                            $orderForShipping    = wc_get_order($order->id);
                            $totalShipping       = floatval($orderForShipping->get_shipping_total());
                            $totalShippingTax    = floatval($orderForShipping->get_shipping_tax());
                            $shippingTotalRefund = false;
                            $refunded            = $refundedTax = 0;
                            $taxRates            = array();
                            foreach ($orderForShipping->get_items('shipping') as $itemID => $item) {
                                // Get tax by country
                                $country = $this->customerCountry($ordersData);
                                $taxes   = \WC_Tax::get_rates_for_tax_class($item->get_tax_class());
                                if (! empty($taxes)) {
                                    foreach ($taxes as $tax) {
                                        if ($tax->tax_rate_country === $country) {
                                            $taxRates = $tax;
                                            break;
                                        }
                                    }
                                }

                                if (! empty($taxRates) && is_object($taxRates)) {
                                    $id = $taxRates->tax_rate_id;
                                    // Refund total
                                    $refunded = floatval($orderForShipping->get_total_refunded_for_item($itemID,
                                        'shipping'));
                                    // Refund tax
                                    if (1 === intval($taxRates->tax_rate_shipping)) {
                                        $refundedTax = floatval($orderForShipping->get_tax_refunded_for_item($itemID,
                                            $id[0],
                                            'shipping'));
                                    }
                                }

                                if ($refunded === $totalShipping && $refundedTax === $totalShippingTax) {
                                    $shippingTotalRefund = true;
                                    // Reset shipping total data
                                    $order->shipping_total = $this->numberFormat(0);
                                } else {

                                    if (isset($order->items_shipping[1]) && is_array($order->items_shipping[1])) {
                                        $dataShippingPartialRefund = $order->items_shipping[1]['refund_shipping'];
                                        $totalShipping             = $orderForShipping->get_shipping_total() - $dataShippingPartialRefund['total'];
                                        $totalShippingTax          = $orderForShipping->get_shipping_tax() - $dataShippingPartialRefund['total_tax'];
                                    }
                                }
                            }
                            if (! $shippingTotalRefund) {

                                // Rate based local tax
                                if (! empty($taxRates) && is_object($taxRates) && floatval(0) !== floatval($totalShippingTax)) {
                                    $rate = $this->numberFormat($taxRates->tax_rate, 0);
                                } elseif (floatval(0) === floatval($totalShippingTax)) {
                                    // Zero rate if total tax is zero
                                    $rate = 0;
                                } else {
                                    $rate = $this->shippingRate();
                                }

                                // #2.2.1
                                $detailLine = $dataProduct->addChild('DettaglioLinee');
                                $detailLine->addChild('NumeroLinea', $i);
                                $productCode = $detailLine->addChild('CodiceArticolo');
                                $productCode->addChild('CodiceTipo', self::CODE_TYPE);
                                $productCode->addChild('CodiceValore', self::CODE_SHIPPING);
                                $detailLine->addChild('Descrizione', 'Spedizione');
                                $detailLine->addChild('Quantita', $this->numberFormat('1'));
                                $detailLine->addChild('UnitaMisura', 'N.');
                                $detailLine->addChild('PrezzoUnitario', $this->numberFormat($totalShipping, 6));
                                $detailLine->addChild('PrezzoTotale', $this->numberFormat($totalShipping, 6));

                                /**
                                 * 1. Company UE and valid VIES
                                 * 2. Company and Private extra UE
                                 * 3. All other cases (including EU companies with invalid VIES)
                                 */
                                // Regime forfettario
                                if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', 'N2.2');
                                } else {
                                    if ($nature && true === $vies &&
                                        in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(),
                                            true) &&
                                        'IT' !== $this->customerCountry($ordersData)
                                    ) {
                                        $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                        $detailLine->addChild('Natura', $nature);
                                    } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                            GeneralFields::getEuCountries(), true)) {
                                        $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                        $detailLine->addChild('Natura', $nature);
                                    } else {
                                        $detailLine->addChild('AliquotaIVA', $this->numberFormat($rate));
                                        if (floatval(0) === $totalShippingTax) {
                                            $detailLine->addChild('Natura', 'N4');
                                        } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                                GeneralFields::getEuCountries(), true)) {
                                            $detailLine->addChild('Natura', $nature);
                                        }
                                    }
                                }
                            }
                            // ********************************************************************************************** //
                        }
                    }

                    // Add Fee
                    if (! empty($order->items_fee)) {
                        $itemsFees    = $order->items_fee;
                        $itemFeeTotal = $itemFeeTotalTax = $refundFee = $rate = $itemFeeName = null;
                        // Set data fee
                        foreach ($itemsFees as $key => $itemFees) {
                            if (! isset($itemFees['refund_fee'])) {
                                $itemFeeName = isset($itemFees['name']) ? trim($itemFees['name']) : '';
                                // Get tax by country
                                $country  = $this->customerCountry($ordersData);
                                $taxRates = \WC_Tax::get_rates_for_tax_class($itemFees['tax_class']);
                                foreach ($taxRates as $tax) {
                                    if ($tax->tax_rate_country === $country) {
                                        $taxRates = $tax;
                                        break;
                                    }
                                }

                                if (empty($taxRates)) {
                                    $taxRates = \WC_Tax::get_base_tax_rates();
                                } elseif (! empty($taxRates) && is_object($taxRates)) {
                                    $rate = $this->numberFormat($taxRates->tax_rate, 0);
                                } else {
                                    $rate = 0;
                                }

                                $itemFeeTotal    = isset($itemsFees[$key]['total']) ? $itemsFees[$key]['total'] : null;
                                $itemFeeTotalTax = isset($itemsFees[$key]['total_tax']) ? $itemsFees[$key]['total_tax'] : null;

                                // Zero rate if total tax is zero
                                if (floatval(0) === floatval($itemFeeTotalTax)) {
                                    $rate = 0;
                                }
                            }
                            $refundFee = isset($itemFees['refund_fee']) ? $itemFees['refund_fee'] : null;

                            // Refund fee - recalculate fee total and total tax
                            if (! empty($refundFee)) {
                                $itemFeeTotal    = floatval($itemFeeTotal) - floatval($refundFee['total']);
                                $itemFeeTotalTax = floatval($itemFeeTotalTax) - floatval($refundFee['total_tax']);
                            }
                        }

                        if (floatval(0) !== $itemFeeTotal || floatval(0) !== $itemFeeTotalTax) {
                            // #2.2.1
                            $detailLine = $dataProduct->addChild('DettaglioLinee');
                            $detailLine->addChild('NumeroLinea', $i + 1);
                            $productCode = $detailLine->addChild('CodiceArticolo');
                            $productCode->addChild('CodiceTipo', self::CODE_TYPE);
                            $productCode->addChild('CodiceValore', self::CODE_FEE);
                            $detailLine->addChild('Descrizione', $itemFeeName);
                            $detailLine->addChild('Quantita', $this->numberFormat('1'));
                            $detailLine->addChild('UnitaMisura', 'N.');
                            $detailLine->addChild('PrezzoUnitario', $this->numberFormat($itemFeeTotal, 6));
                            $detailLine->addChild('PrezzoTotale', $this->numberFormat($itemFeeTotal, 6));

                            /**
                             * 1. Company UE and valid VIES
                             * 2. Company and Private extra UE
                             * 3. All other cases (including EU companies with invalid VIES)
                             */
                            // Regime forfettario
                            if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                $detailLine->addChild('Natura', 'N2.2');
                            } else {
                                if ($nature && true === $vies &&
                                    in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(),
                                        true) &&
                                    'IT' !== $this->customerCountry($ordersData)
                                ) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                        GeneralFields::getEuCountries(),
                                        true)) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } else {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat($rate));
                                    if (floatval(0) === $itemFeeTotalTax) {
                                        $detailLine->addChild('Natura', 'N4');
                                    } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                            GeneralFields::getEuCountries(), true)) {
                                        $detailLine->addChild('Natura', $nature);
                                    }
                                }
                            }
                        }
                    }
                    // Refund order
                } elseif (! empty($order->current_refund_items) && 'shop_order_refund' === $order->order_type) {
                    $i = 1;
                    // Refund items

                    // Set refund item meta data for product
                    $refundItemMeta = array();
                    if (! empty($order->items)) {
                        foreach ($order->items as $item) {
                            $id                  = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $item['product_id'];
                            $refundItemMeta[$id] = $item['meta_data'];
                        }
                    }

                    foreach ($order->current_refund_items as $item) {
                        // product code
                        $productID = isset($item['product_id']) ? $item['product_id'] : null;
                        $id        = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $productID;
                        $code      = isset($id) ? "{$id}" : '';
                        $product   = wc_get_product($code);
                        // Refund product
                        if ($product) {

                            // Zero rate if total tax is zero
                            if ((floatval(0) === floatval($item['total_tax']))) {
                                $rate = 0;
                            } else {
                                $rate = $this->taxRate($item);
                            }

                            // Set item meta data
                            $item['meta_data'] = $refundItemMeta[$id];

                            // Unit price
                            $unitPrice = $this->numberFormat($this->calcUnitPrice($item, $ordersData, $vies), 6);

                            // #2.2.1
                            $detailLine = $dataProduct->addChild('DettaglioLinee');
                            $detailLine->addChild('NumeroLinea', $i);
                            $productCode = $detailLine->addChild('CodiceArticolo');
                            $productCode->addChild('CodiceTipo', self::CODE_TYPE);
                            $productCode->addChild('CodiceValore', $code);
                            $detailLine->addChild('Descrizione', $this->productDescription($item, 'refund'));

                            $qty = abs($item['quantity']);
                            $detailLine->addChild('Quantita', $this->numberFormat($qty));
                            $detailLine->addChild('UnitaMisura', 'N.');
                            $detailLine->addChild('PrezzoUnitario', $unitPrice);
                            $detailLine->addChild('PrezzoTotale', $this->numberFormat(abs($item['total']), 6));

                            /**
                             * 1. Company UE and valid VIES
                             * 2. Company and Private extra UE
                             * 3. All other cases (including EU companies with invalid VIES)
                             */
                            // Regime forfettario
                            if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                $detailLine->addChild('Natura', 'N2.2');
                            } else {
                                if ($nature && true === $vies &&
                                    in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(),
                                        true) &&
                                    'IT' !== $this->customerCountry($ordersData)
                                ) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                        GeneralFields::getEuCountries(),
                                        true)) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } else {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat($rate));
                                    if ($product instanceof \WC_Product && 'none' === $product->get_tax_status() ||
                                        (floatval(0) === floatval($item['total_tax']) || floatval(0) === abs($item['total_tax']))
                                    ) {
                                        $detailLine->addChild('Natura', 'N4');
                                    } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                            GeneralFields::getEuCountries(), true)) {
                                        $detailLine->addChild('Natura', $nature);
                                    }
                                }
                            }
                            $i++;
                        }

                        // Shipping refund
                        if (isset($item['method_id']) && isset($item['refund_type']) && 'shipping' === $item['refund_type']) {
                            $total    = isset($item['total']) ? $item['total'] : 0;
                            $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                            // Recalculate totals
                            if (isset($order->items_shipping[1]) && is_array($order->items_shipping[1])) {
                                $refundShipping = $order->items_shipping[1];
                                $total          = $refundShipping['refund_shipping']['total'];
                                $totalTax       = $refundShipping['refund_shipping']['total_tax'];
                                if (0 === abs($totalTax)) {
                                    $totalTax = $item['total_tax'] - $refundShipping['refund_shipping']['total_tax'];
                                }
                            }

                            // Shipping tax
                            $taxRates         = array();
                            $orderForShipping = wc_get_order($order->id);
                            foreach ($orderForShipping->get_items('shipping') as $itemID => $shipItem) {
                                // Get tax by country
                                $country = $this->customerCountry($ordersData);
                                $taxes   = \WC_Tax::get_rates_for_tax_class($shipItem->get_tax_class());
                                if (! empty($taxes)) {
                                    foreach ($taxes as $tax) {
                                        if ($tax->tax_rate_country === $country) {
                                            $taxRates = $tax;
                                            break;
                                        }
                                    }
                                }
                            }

                            // Rate based local tax
                            if (! empty($taxRates) && is_object($taxRates) && floatval(0) !== floatval($totalTax)) {
                                $shippingRate = $this->numberFormat($taxRates->tax_rate, 0);
                            } elseif (floatval(0) === floatval($totalTax) || floatval(0) === abs($totalTax)) {
                                // Zero rate if total tax is zero
                                $shippingRate = 0;
                            } else {
                                $shippingRate = $this->shippingRate();
                            }

                            $detailLine = $dataProduct->addChild('DettaglioLinee');
                            $detailLine->addChild('NumeroLinea', $i);
                            $productCode = $detailLine->addChild('CodiceArticolo');
                            $productCode->addChild('CodiceTipo', self::CODE_TYPE);
                            $productCode->addChild('CodiceValore', self::CODE_SHIPPING);
                            $detailLine->addChild('Descrizione', 'Rimborso Spedizione');
                            $detailLine->addChild('Quantita', $this->numberFormat('1'));
                            $detailLine->addChild('UnitaMisura', 'N.');
                            $detailLine->addChild('PrezzoUnitario', $this->numberFormat($total, 6));
                            $detailLine->addChild('PrezzoTotale', $this->numberFormat($total), 6);

                            /**
                             * 1. Company UE and valid VIES
                             * 2. Company and Private extra UE
                             * 3. All other cases (including EU companies with invalid VIES)
                             */
                            // Regime forfettario
                            if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                $detailLine->addChild('Natura', 'N2.2');
                            } else {
                                if ($nature && true === $vies &&
                                    in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(),
                                        true) &&
                                    'IT' !== $this->customerCountry($ordersData)
                                ) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                        GeneralFields::getEuCountries(),
                                        true)) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } else {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat($shippingRate));
                                    if (floatval(0) === floatval($shippingRate)) {
                                        $detailLine->addChild('Natura', 'N4');
                                    } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                            GeneralFields::getEuCountries(), true)) {
                                        $detailLine->addChild('Natura', $nature);
                                    }
                                }
                            }
                        }

                        // Fee refund
                        if (isset($item['refund_type']) && 'fee' === $item['refund_type']) {

                            $total    = isset($item['total']) ? $item['total'] : 0;
                            $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                            // Fee rate
                            $orderForFee = wc_get_order($item['order_id']);
                            foreach ($orderForFee->get_items('fee') as $itemID => $feeItem) {
                                // Get tax by country
                                $country = $orderForFee->get_billing_country();
                                $taxes   = \WC_Tax::get_rates_for_tax_class($feeItem->get_tax_class());
                                if (! empty($taxes)) {
                                    foreach ($taxes as $tax) {
                                        if ($tax->tax_rate_country === $country) {
                                            $taxRates = $tax;
                                            break;
                                        }
                                    }
                                }
                            }

                            $itemsFees = $order->items_fee;
                            foreach ($itemsFees as $key => $itemFees) {
                                // Get fee data
                                if (! isset($itemFees['refund_fee'])) {
                                    $total    = isset($itemsFees[$key]['total']) ? $itemsFees[$key]['total'] : null;
                                    $totalTax = isset($itemsFees[$key]['total_tax']) ? $itemsFees[$key]['total_tax'] : null;
                                }
                            }

                            // Rate based local tax
                            if (! empty($taxRates) && is_object($taxRates) &&
                                (floatval(0) !== floatval($totalTax) || floatval(0) !== abs($totalTax))
                            ) {
                                $rate = $this->numberFormat($taxRates->tax_rate, 0);
                            } else {
                                $rate = $item['tax_rate'];
                            }
                            if (floatval(0) === floatval($totalTax) || floatval(0) === abs($totalTax)) {
                                // Zero rate if total tax is zero
                                $rate = 0;
                            }

                            $detailLine = $dataProduct->addChild('DettaglioLinee');
                            $detailLine->addChild('NumeroLinea', $i);
                            $productCode = $detailLine->addChild('CodiceArticolo');
                            $productCode->addChild('CodiceTipo', self::CODE_TYPE);
                            $productCode->addChild('CodiceValore', self::CODE_FEE);
                            $detailLine->addChild('Descrizione', "Rimborso {$item['name']}");
                            $detailLine->addChild('Quantita', $this->numberFormat('1'));
                            $detailLine->addChild('UnitaMisura', 'N.');
                            $detailLine->addChild('PrezzoUnitario', $this->numberFormat($item['total'], 6));
                            $detailLine->addChild('PrezzoTotale', $this->numberFormat($item['total'], 6));

                            /**
                             * 1. Company UE and valid VIES
                             * 2. Company and Private extra UE
                             * 3. All other cases (including EU companies with invalid VIES)
                             */
                            // Regime forfettario
                            if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                                $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                $detailLine->addChild('Natura', 'N2.2');
                            } else {
                                if ($nature && true === $vies &&
                                    in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(),
                                        true) &&
                                    'IT' !== $this->customerCountry($ordersData)
                                ) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                        GeneralFields::getEuCountries(),
                                        true)) {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat(0));
                                    $detailLine->addChild('Natura', $nature);
                                } else {
                                    $detailLine->addChild('AliquotaIVA', $this->numberFormat($rate));
                                    if (floatval(0) === floatval($rate) || floatval(0) === abs($rate)) {
                                        $detailLine->addChild('Natura', 'N4');
                                    } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                            GeneralFields::getEuCountries(), true)) {
                                        $detailLine->addChild('Natura', $nature);
                                    }
                                }
                            }
                        }
                    }
                }
                // No items refunded
                if ('shop_order_refund' === $order->order_type &&
                    empty($order->current_refund_items) &&
                    floatval(abs($order->amount)) === floatval(abs($order->refunded['total_refunded']))
                ) {
                    // #2.2.1
                    $detailLine = $dataProduct->addChild('DettaglioLinee');
                    $detailLine->addChild('NumeroLinea', 1);
                    $detailLine->addChild('Descrizione',
                        sprintf('%s %s', esc_html__('Refund: ', WC_EL_INV_TEXTDOMAIN), "{$order->reason}")
                    );

                    $detailLine->addChild('Quantita', $this->numberFormat(1));
                    $detailLine->addChild('UnitaMisura', 'N.');
                    $detailLine->addChild('PrezzoUnitario', $order->amount);
                    $detailLine->addChild('PrezzoTotale', $this->numberFormat(abs($order->total), 6));
                }

                // ************************************************************************************************** //
                // End Detail product line
                // ************************************************************************************************** //

                /**
                 * Filter Data after detail product line zone XML
                 *
                 * @since 1.0.0
                 */
                $this->xml = apply_filters(
                    'wc_el_inv-created_xml_after_detail_product_line_zone',
                    $this->xml,
                    $dataProduct,
                    $ordersData
                );

                // ************************************************************************************************** //
                // Start Summary data line
                // ************************************************************************************************** //

                $summaryData = null;
                $summaryRate = array();

                // Summary Shop Order
                if (! empty($order->items) && 'shop_order' === $order->order_type) {
                    $addShipping = false;
                    foreach ($order->items as $item) {
                        $productID = isset($item['product_id']) ? $item['product_id'] : null;
                        $id        = isset($item['variation_id']) && '0' !== $item['variation_id'] ? $item['variation_id'] : $productID;
                        $product   = wc_get_product($id);

                        // Discount total
                        $discountTotal = $this->numberFormat((($item['subtotal'] - $item['total'])), 6);
                        // Total price
                        $unitForTotal = $this->numberFormat($this->calcUnitPrice($item, $ordersData, $vies), 6);
                        $totalPrice   = $this->numberFormat(($unitForTotal * abs($item['quantity'])), 6);
                        // Summary for product
                        $summaryRate[$this->taxRate($item)][] = array(
                            'total'     => $this->numberFormat(($totalPrice - $discountTotal), 6),
                            'total_tax' => abs($item['total_tax']),
                        );

                        $totalShipping    = $order->shipping_total;
                        $totalShippingTax = $order->shipping_tax;

                        if (false === $addShipping &&
                            'disabled' !== GeneralFields::getGeneralShippingLocation() &&
                            $product instanceof \WC_Product &&
                            ! $product->is_virtual()
                        ) {
                            $addShipping = true;

                            if (isset($order->items_shipping[1]) && is_array($order->items_shipping[1])) {
                                $dataShippingPartialRefund = $order->items_shipping[1]['refund_shipping'];
                                $totalShipping             = $order->shipping_total - $dataShippingPartialRefund['total'];
                                $totalShippingTax          = $order->shipping_tax - $dataShippingPartialRefund['total_tax'];
                            }

                            // Shipping tax
                            $orderForShipping = wc_get_order($order->id);
                            foreach ($orderForShipping->get_items('shipping') as $itemID => $shipItem) {
                                // Get tax by country
                                $country = $this->customerCountry($ordersData);
                                $taxes   = \WC_Tax::get_rates_for_tax_class($shipItem->get_tax_class());
                                if (! empty($taxes)) {
                                    foreach ($taxes as $tax) {
                                        if ($tax->tax_rate_country === $country) {
                                            $taxRates = $tax;
                                            break;
                                        }
                                    }
                                }
                            }

                            // Rate based local tax
                            if (! empty($taxRates) && is_object($taxRates) && floatval(0) !== floatval($totalShippingTax)) {
                                $shippingRate = $this->numberFormat($taxRates->tax_rate, 0);
                            } elseif (floatval(0) === floatval($totalShippingTax)) {
                                // Zero rate if total tax is zero
                                $shippingRate = 0;
                            } else {
                                $shippingRate = $this->shippingRate();
                            }

                            // Add if have shipping cost
                            if ((floatval(0) !== floatval($totalShipping))) {
                                $summaryRate[$shippingRate][] = array(
                                    'total'     => $this->numberFormat($totalShipping, 6),
                                    'total_tax' => '0' !== $totalShippingTax ? abs($totalShippingTax) : 0,
                                );
                            }
                        }
                    }
                    // Summary Refund Order
                } elseif (! empty($order->current_refund_items) && 'shop_order_refund' === $order->order_type) {
                    foreach ($order->current_refund_items as $item) {
                        $total    = isset($item['total']) ? abs($this->calcUnitPrice($item, $ordersData,
                                $vies) * abs($item['quantity'])) : 0;
                        $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                        // Set product summary
                        if (isset($item['product_id'])) {
                            $summaryRate[$this->taxRate($item)][] = array(
                                'total'     => $this->numberFormat($total, 6),
                                'total_tax' => abs($totalTax),
                            );
                        }

                        // Shipping
                        // Recalculate totals
                        if (isset($order->items_shipping[1]) && is_array($order->items_shipping[1])) {
                            $refundShipping = $order->items_shipping[1];
                            $total          = $refundShipping['refund_shipping']['total'];
                            $totalTax       = $refundShipping['refund_shipping']['total_tax'];
                            if (0 === abs($totalTax)) {
                                $totalTax = $item['total_tax'] - $refundShipping['refund_shipping']['total_tax'];
                            }
                        }

                        if (isset($item['method_id']) && isset($item['refund_type']) && 'shipping' === $item['refund_type']) {
                            // Shipping tax
                            $total    = isset($item['total']) ? $item['total'] : 0;
                            $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                            $orderForShipping = wc_get_order($order->id);
                            foreach ($orderForShipping->get_items('shipping') as $itemID => $shipItem) {
                                // Get tax by country
                                $country = $this->customerCountry($ordersData);
                                $taxes   = \WC_Tax::get_rates_for_tax_class($shipItem->get_tax_class());
                                if (! empty($taxes)) {
                                    foreach ($taxes as $tax) {
                                        if ($tax->tax_rate_country === $country) {
                                            $taxRates = $tax;
                                            break;
                                        }
                                    }
                                }
                            }
                            // Rate based local tax
                            if (! empty($taxRates) && is_object($taxRates) && floatval(0) !== floatval($totalTax)) {
                                $shippingRate = $this->numberFormat($taxRates->tax_rate, 0);
                            } elseif (floatval(0) === floatval($totalTax)) {
                                // Zero rate if total tax is zero
                                $shippingRate = 0;
                            } else {
                                $shippingRate = $this->shippingRate();
                            }

                            $summaryRate[$shippingRate][] = array(
                                'total'     => $this->numberFormat($total, 6),
                                'total_tax' => abs($totalTax),
                            );
                        }

                        if (isset($item['refund_type']) && 'fee' === $item['refund_type']) {

                            $total    = isset($item['total']) ? $item['total'] : 0;
                            $totalTax = isset($item['total_tax']) ? $item['total_tax'] : 0;

                            // Fee rate
                            $orderForFee = wc_get_order($item['order_id']);
                            foreach ($orderForFee->get_items('fee') as $itemID => $feeItem) {
                                // Get tax by country
                                $country = $orderForFee->get_billing_country();
                                $taxes   = \WC_Tax::get_rates_for_tax_class($feeItem->get_tax_class());
                                if (! empty($taxes)) {
                                    foreach ($taxes as $tax) {
                                        if ($tax->tax_rate_country === $country) {
                                            $taxRates = $tax;
                                            break;
                                        }
                                    }
                                }
                            }

                            $itemsFees = $order->items_fee;
                            foreach ($itemsFees as $key => $itemFees) {
                                // Get fee data
                                if (! isset($itemFees['refund_fee'])) {
                                    $total    = isset($itemsFees[$key]['total']) ? $itemsFees[$key]['total'] : null;
                                    $totalTax = isset($itemsFees[$key]['total_tax']) ? $itemsFees[$key]['total_tax'] : null;
                                }
                            }

                            // Rate based local tax
                            if (! empty($taxRates) && is_object($taxRates) &&
                                (floatval(0) !== floatval($totalTax) || floatval(0) !== abs($totalTax))
                            ) {
                                $rate = $this->numberFormat($taxRates->tax_rate, 0);
                            } else {
                                $rate = $item['tax_rate'];
                            }
                            if (floatval(0) === floatval($totalTax) || floatval(0) === abs($totalTax)) {
                                // Zero rate if total tax is zero
                                $rate = 0;
                            }

                            $summaryRate[$rate][] = array(
                                'total'     => $this->numberFormat($item['total'], 6),
                                'total_tax' => abs($totalTax),
                            );
                        }
                    }
                }

                // Add fee
                if (! empty($order->items_fee)) {
                    $itemsFees    = $order->items_fee;
                    $itemFeeTotal = $itemFeeTotalTax = $refundFee = $rate = null;
                    foreach ($itemsFees as $key => $itemFees) {
                        // Get tax by country
                        $country  = $this->customerCountry($ordersData);
                        $taxClass = $itemFees['tax_class'] ?? null;
                        $taxRates = \WC_Tax::get_rates_for_tax_class($taxClass);
                        foreach ($taxRates as $tax) {
                            if ($tax->tax_rate_country === $country) {
                                $taxRates = $tax;
                                break;
                            }
                        }

                        if (empty($taxRates)) {
                            $taxRates = \WC_Tax::get_base_tax_rates();
                        } elseif (! empty($taxRates) && is_object($taxRates)) {
                            $rate = $this->numberFormat($taxRates->tax_rate, 0);
                        } else {
                            $rate = 0;
                        }

                        if (! isset($itemFees['refund_fee'])) {
                            $itemFeeTotal    = isset($itemsFees[$key]['total']) ? $itemsFees[$key]['total'] : null;
                            $itemFeeTotalTax = isset($itemsFees[$key]['total_tax']) ? $itemsFees[$key]['total_tax'] : null;
                        }

                        $refundFee = isset($itemFees['refund_fee']) ? $itemFees['refund_fee'] : null;
                    }

                    // Refund fee
                    if (! empty($refundFee)) {
                        $itemFeeTotal    = floatval($itemFeeTotal) - floatval($refundFee['total']);
                        $itemFeeTotalTax = floatval($itemFeeTotalTax) - floatval($refundFee['total_tax']);
                    }
                    if (floatval(0) !== floatval($itemFeeTotalTax)) {
                        $feeRate = $rate;
                    } else {
                        $feeRate = 0;
                    }

                    if ((floatval(0) !== floatval($itemFeeTotal))) {
                        if ('shop_order' === $order->order_type) {
                            $summaryRate[$feeRate][] = array(
                                'total'     => $this->numberFormat($itemFeeTotal, 6),
                                'total_tax' => abs($itemFeeTotalTax),
                            );
                        }
                    }
                }

                // #2.2.2
                if (! empty($summaryRate)) {
                    $total          = 0;
                    $totalTax       = 0;
                    $totalFloat2Dec = 0;
                    foreach ($summaryRate as $rate => $data) {
                        $summaryData = $dataProduct->addChild('DatiRiepilogo');
                        foreach ($data as $dataItem) {
                            // Sum of totals to 2 decimal places
                            $total += floatval($this->numberFormat($dataItem['total'], 2));
                            // Sum total tax
                            $totalTax += $dataItem['total_tax'];
                            // Sum of totals to 6 decimal places
                            $totalFloat2Dec += floatval($this->numberFormat($dataItem['total'], 6));
                        }

                        /**
                         * 1. Company UE and valid VIES
                         * 2. Company and Private extra UE
                         * 3. All other cases (including EU companies with invalid VIES)
                         */
                        // Regime forfettario
                        if ('RF19' === GeneralFields::getGeneralInvoiceOptionTaxRegime()) {
                            $summaryData->addChild('AliquotaIVA', $this->numberFormat(0));
                            $summaryData->addChild('Natura', 'N2.2');
                        } else {
                            if ($nature && true === $vies &&
                                in_array($this->customerCountry($ordersData), GeneralFields::getEuCountries(), true) &&
                                'IT' !== $this->customerCountry($ordersData)
                            ) {
                                $summaryData->addChild('AliquotaIVA', $this->numberFormat(0));
                                $summaryData->addChild('Natura', $nature);
                            } elseif ($nature && ! in_array($this->customerCountry($ordersData),
                                    GeneralFields::getEuCountries(), true)) {
                                $summaryData->addChild('AliquotaIVA', $this->numberFormat(0));
                                $summaryData->addChild('Natura', $nature);
                            } else {
                                $summaryData->addChild('AliquotaIVA', $this->numberFormat($rate));
                                if (floatval(0) === floatval($totalTax)) {
                                    $summaryData->addChild('Natura', 'N4');
                                } elseif ($nature && $refNorm && in_array($this->customerCountry($ordersData),
                                        GeneralFields::getEuCountries(), true)) {
                                    $summaryData->addChild('Natura', $nature);
                                }
                            }
                        }

                        // Round
                        // Calculation of the rounding between the total to 2 decimal places and the total to 6 decimal places
                        $round = $this->numberFormat(
                            $this->numberFormat($totalFloat2Dec) - $this->numberFormat($totalFloat2Dec, 6),
                            4,
                            false
                        );
                        $summaryData->addChild('Arrotondamento', $round);
                        $summaryData->addChild('ImponibileImporto', $this->numberFormat($totalFloat2Dec));
                        $summaryData->addChild('Imposta', $this->numberFormat($totalTax));

                        // 2.2.2.8
                        if ($refNorm && 'IT' !== $this->customerCountry($ordersData)) {
                            $summaryData->addChild('RiferimentoNormativo', $refNorm);
                        }

                        // Reset total
                        $total = $totalTax = $totalFloat2Dec = 0;
                    }
                }
                // ************************************************************************************************** //
                // End Summary data line
                // ************************************************************************************************** //

                /**
                 * Filter Data after detail product line zone XML
                 *
                 * @since 1.0.0
                 */
                $this->xml = apply_filters(
                    'wc_el_inv-created_xml_after_summary_data_line_zone',
                    $this->xml,
                    $summaryData,
                    $ordersData
                );

                // ************************************************************************************************** //
                // Start Payment line
                // ************************************************************************************************** //

                // #2.4
                $dataPayment = $body->addChild('DatiPagamento');
                // #2.4.1
                $dataPayment->addChild('CondizioniPagamento', 'TP02');
                // #2.4.2
                $detailPayment = $dataPayment->addChild('DettaglioPagamento');
                $detailPayment->addChild('ModalitaPagamento', $this->paymentMethod($order));

                // Total Payments
                if ('shop_order_refund' === $order->order_type &&
                    ! empty($order->refunded) &&
                    '0' !== $order->refunded['total_refunded'] &&
                    0 < (floatval(abs($order->total)) - floatval(abs($order->refunded['total_refunded'])))
                ) {
                    $detailPayment->addChild('ImportoPagamento',
                        $this->numberFormat($order->refunded['remaining_amount'])
                    );
                } else {
                    $detailPayment->addChild('ImportoPagamento',
                        $this->numberFormat($this->total($order, $summaryRate))
                    );
                }
                // ************************************************************************************************** //
                // End Payment line
                // ************************************************************************************************** //

                /**
                 * Filter Data after detail payment line zone XML
                 *
                 * @since 1.0.0
                 */
                $this->xml = apply_filters(
                    'wc_el_inv-created_xml_after_payment_line_zone',
                    $this->xml,
                    $dataPayment,
                    $ordersData
                );
            }

            // ****************************************************************************************************** //
            // End Invoice Body
            // ****************************************************************************************************** //
        }

        /**
         * Filter XML file data
         *
         * @since 1.0.0
         */
        $xmlFile = apply_filters(
            'wc_el_inv-created_xml',
            $this->xml->asXML()
        );

        // Stupid SimpleXMLElement!! replace "xmlns:p:FatturaElettronica" in "p:FatturaElettronica"
        $xmlFile = preg_replace('/xmlns:p:FatturaElettronica/', 'p:FatturaElettronica', $xmlFile);

        /**
         * Filter wc_el_inv-xml_file
         * - filter xml string file
         *
         * @since ${SINCE}
         */
        $xmlFile = apply_filters(
            'wc_el_inv-xml_file',
            $xmlFile,
            $this->xml
        );

        if (isset($saveBulk) && 'true' === $saveBulk) {
            // Save files in temp dir
            // return the file path
            return $this->saveXmlFiles($fileName);
        } elseif (isset($customerID)) {

            // Loop xml
            echo $xmlFile;
        } else {

            // Single xml
            echo $xmlFile;
            die;
        }
        // *********************************** //
        // End XML
        // *********************************** //
    }
}
