<?php
/**
 * OptionPage.php
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

namespace WcElectronInvoiceFree\Admin\Settings;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

use WcElectronInvoiceFree\Admin\Settings\Fields\KeyField;

/**
 * Class OptionPage
 *
 * @since  1.0.0
 * @author alfiopiccione <alfio.piccione@gmail.com>
 */
final class OptionPage extends OptionFields
{
    /**
     * Instance
     *
     * @since 1.0.0
     *
     * @var object The instance of the class
     */
    private static $instance;

    /**
     * Update
     *
     * @since 1.0.0
     *
     * @var
     */
    private $update = false;

    /**
     * Holds the values to be used in the fields callbacks
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $options;

    /**
     * Option Key
     *
     * @since 1.0.0
     *
     * @var string
     */
    public static $optionKey = 'wc_el_inv-settings-';

    /**
     * The options group name
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $optionsName;

    /**
     * Sanitize Callback
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $sanitize;

    /**
     * Menu Page Title
     *
     * @since 1.0.0
     *
     * @var string The text to be displayed in the title tags of the page when the menu is selected
     */
    private $menuPageTitle;

    /**
     * Menu Title
     *
     * @since 1.0.0
     *
     * @var string The text to be used for the menu
     */
    private $menuTitle;

    /**
     * Capability
     *
     * @since 1.0.0
     *
     * @var string The capability required for this menu to be displayed to the user.
     */
    private $capability;

    /**
     * Menu Slug
     *
     * @since 1.0.0
     *
     * @var string The slug name to refer to this menu by
     */
    private $menuSlug;

    /**
     * Callback Function
     *
     * @since 1.0.0
     *
     * @var string The function to be called to output the content for this page.
     */
    private $callback;

    /**
     * Menu icon
     *
     * @since 1.0.0
     *
     * @var string The icon for this page.
     */
    private $icon;

    /**
     * Contains arguments for sub menu page
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $subPageArgs = array();

    /**
     * Contains arguments for tabs
     *
     * @since 1.0.0
     *
     * @var array
     */
    public $tabs = array();

    /**
     * Constructor
     *
     * @since 1.0.0
     */
    private function __construct()
    {
        // Setting data setup
        $this->setup();
        $this->options = $this->getOptions();
    }

    /**
     * Setting data setup
     *
     * @since 1.0.0
     */
    private function setup()
    {
        // Args for register settings
        $this->optionsName = 'wc_el_inv_options';
        $this->sanitize    = array($this, 'sanitizeOptions');

        // Args for menu page
        $this->menuPageTitle = esc_html__('Electronic Invoice - Settings', WC_EL_INV_FREE_TEXTDOMAIN);
        $this->menuTitle     = esc_html__('Electr. Invoice', WC_EL_INV_FREE_TEXTDOMAIN);
        $this->capability    = 'manage_options';
        $this->menuSlug      = 'wc_el_inv-options-page';
        $this->callback      = array($this, 'createPage');
        $this->icon          = '';

        // Args for sub menu pages
        $this->subPageArgs['sub_menu_page'] = array();

        $this->tabs = include \WcElectronInvoiceFree\Plugin::getPluginDirPath('/inc/settings/pageSettingsTabs.php');

        // parent setup
        parent::sectionFields();
    }

    /**
     * Admin bar link
     *
     * @since 1.0.0
     *
     * @param \WP_Admin_Bar $adminBar
     */
    public function adminToolbar(\WP_Admin_Bar $adminBar)
    {
        if (! $adminBar instanceof \WP_Admin_Bar) {
            return;
        }

        $adminBar->add_menu(array(
            'id'    => $this->optionsName,
            'title' => esc_html__($this->menuTitle, WC_EL_INV_FREE_TEXTDOMAIN),
            'href'  => esc_url(admin_url('admin.php?page=' . $this->menuSlug . '&tab=wc-checkout')),
            'meta'  => array(
                'class' => 'active',
            ),
        ));
        // Sum menu link
        $adminBar->add_menu(array(
            'parent' => $this->optionsName,
            'id'     => $this->optionsName . '-wc-checkout',
            'title'  => esc_html__('WooCommerce Integration', WC_EL_INV_FREE_TEXTDOMAIN),
            'href'   => esc_url(admin_url('admin.php?page=' . $this->menuSlug . '&tab=wc-checkout')),
        ));
        $adminBar->add_menu(array(
            'parent' => $this->optionsName,
            'id'     => $this->optionsName . '-invoice',
            'title'  => esc_html__('Invoice', WC_EL_INV_FREE_TEXTDOMAIN),
            'href'   => esc_url(admin_url('admin.php?page=' . $this->menuSlug . '&tab=invoice')),
        ));
        $adminBar->add_menu(array(
            'parent' => $this->optionsName,
            'id'     => $this->optionsName . '-json-order',
            'title'  => esc_html__('Json Order', WC_EL_INV_FREE_TEXTDOMAIN),
            'href'   => esc_url(admin_url('admin.php?page=' . $this->menuSlug . '&tab=json-order')),
        ));
        $adminBar->add_menu(array(
            'parent' => $this->optionsName,
            'id'     => $this->optionsName . '-json-product',
            'title'  => esc_html__('Json Product', WC_EL_INV_FREE_TEXTDOMAIN),
            'href'   => esc_url(admin_url('admin.php?page=' . $this->menuSlug . '&tab=json-product')),
        ));
        $adminBar->add_menu(array(
            'parent' => $this->optionsName,
            'id'     => $this->optionsName . '-xml',
            'title'  => esc_html__('Xml Invoice', WC_EL_INV_FREE_TEXTDOMAIN),
            'href'   => esc_url(admin_url('admin.php?page=' . $this->menuSlug . '&tab=xml')),
        ));
    }

    /**
     * Return Options
     *
     * @since 1.0.0
     *
     * @param string $singleKey
     * @param bool   $allowedHtml
     *
     * @return array|bool|mixed|string $option The value of the option retrieved from the options array / single key
     *                                 value or false
     */
    public function getOptions($singleKey = '', $allowedHtml = false)
    {
        $options = get_option($this->optionsName);

        if (! empty($options) && is_array($options) && $options) {
            if ('' === $singleKey) {
                return (array)$options;
            } else {
                if (isset($options[self::$optionKey . $singleKey]) && ! $allowedHtml) {
                    return \WcElectronInvoiceFree\Functions\sanitize($options[self::$optionKey . $singleKey]);
                } else {
                    return $options[self::$optionKey . $singleKey];
                }
            }
        }

        return false;
    }

    /**
     * Set Option
     *
     * @since 1.0.0
     *
     * @param $key
     * @param $value
     */
    public function setOption($key, $value)
    {
        $options = get_option($this->optionsName);

        if (! empty($options) && is_array($options) && $options) {
            if ('' !== $key && array_key_exists(self::$optionKey . $key, $options)) {
                // Old value
                $oldValue = $this->getOptions($key);

                // Standardize the values for the control.
                if (is_int($value)) {
                    $oldValue = (int)$oldValue;
                } elseif (is_string($value)) {
                    $oldValue = (string)$oldValue;
                }

                if ($value !== $oldValue) {
                    $currentValue                     = \WcElectronInvoiceFree\Functions\sanitize($value);
                    $options[self::$optionKey . $key] = $currentValue;
                    update_option($this->optionsName, $options);
                }
            }
        }
    }

    /**
     * Register and add settings
     *
     * @since 1.0.0
     */
    public function pageOptionsInit()
    {
        register_setting(
        // A settings group name. Should correspond to a whitelisted option key name.
            $this->optionsName . '_group',
            // The name of an option to sanitize and save.
            $this->optionsName,
            // A callback function that sanitizes the option's value.
            $this->sanitize
        );

        /**
         * Add option and fields
         *
         * @since 1.0.0
         */
        $this->setAllSectionAndFields();
    }

    /**
     * Add options page
     *
     * @see   https://codex.wordpress.org/Function_Reference/add_menu_page
     *
     * @since 1.0.0
     */
    public function addPluginPage()
    {
        // This is the page "Settings".
        add_menu_page(
        // The text to be displayed in the title tags of the page when the menu is selected.
            esc_html__($this->menuPageTitle, WC_EL_INV_FREE_TEXTDOMAIN),
            // The text to be used for the menu.
            esc_html__($this->menuTitle, WC_EL_INV_FREE_TEXTDOMAIN),
            // The capability required for this menu to be displayed to the user.
            $this->capability,
            // The slug name to refer to this menu by (should be unique for this menu).
            $this->menuSlug,
            // The function to be called to output the content for this page.
            $this->callback,
            // The url to the icon to be used for this menu.
            $this->icon
        );

        /**
         * Add sub menu pages
         *
         * @since 1.0.0
         */
        $this->submenuPage();
    }

    /**
     * Add sub menu pages
     *
     * @since 1.0.0
     *
     * @param $subPageArgs
     */
    private function addPluginSubmenuPage($subPageArgs)
    {
        if (empty($subPageArgs) || sizeof($subPageArgs) == 0) {
            return;
        }

        // This is the Sub page "Settings"
        foreach ($subPageArgs as $args) {
            add_submenu_page(
            // The slug name for the parent menu (or the file name of a standard WordPress admin page).
                $args['parent_slug'],
                // The text to be displayed in the title tags of the page when the menu is selected.
                esc_html__($args['page_title'], WC_EL_INV_FREE_TEXTDOMAIN),
                // The text to be used for the menu.
                esc_html__($args['menuTitle'], WC_EL_INV_FREE_TEXTDOMAIN),
                // The capability required for this menu to be displayed to the user.
                $args['capability'],
                // The slug name to refer to this menu by (should be unique for this menu).
                $args['menuSlug'],
                // The function that displays the page content for the menu page.
                array($this, $args['function'])
            );
        }
    }

    /**
     * Add sub menu page
     *
     * @since 1.0.0
     *
     * @return bool
     */
    public function submenuPage()
    {
        if (empty($this->subPageArgs) || sizeof($this->subPageArgs) == 0) {
            return false;
        }

        // Add sub menu pages
        $this->addPluginSubmenuPage($this->subPageArgs['sub_menu_page']);
    }

    /**
     * Setting all section and fields
     *
     * @since 1.0.0
     */
    public function setAllSectionAndFields()
    {
        // Add section and fields
        $this->addSectionAndFields(
            $this->sectionAndFieldsOptions()
        );
    }

    /**
     * Add sections and fields in the settings
     *
     * @since 1.0.0
     *
     * @param array $sectionFields
     *
     * @return bool
     */
    private function addSectionAndFields(array $sectionFields)
    {
        if (empty($sectionFields) || count($sectionFields) === 0) {
            return false;
        }

        // Add the section settings.
        foreach ($sectionFields['section'] as $key => $section) {

            // Check section title structure.
            if (is_array($sectionFields['section'][$key]['section_title'])) {
                $sectionTitle = sprintf(
                    $sectionFields['section'][$key]['section_title'][0],
                    __($sectionFields['section'][$key]['section_title'][1], WC_EL_INV_FREE_TEXTDOMAIN)
                );
            } else {
                $sectionTitle = __($sectionFields['section'][$key]['section_title'], WC_EL_INV_FREE_TEXTDOMAIN);
            }

            add_settings_section(
            // Slug-name to identify the section. Used in the 'id' attribute of tags.
                $sectionFields['section'][$key]['section_id'],
                // Formatted title of the section. Shown as the heading for the section.
                $sectionTitle,
                // Function that echos out any content at the top of the section (between heading and fields).
                $sectionFields['section'][$key]['section_callback'],
                // The slug-name of the settings page on which to show the section.
                $sectionFields['section'][$key]['section_page']
            );
        }

        // Add the fields
        foreach ($sectionFields['fields'] as $fields) {
            add_settings_field(
            // Slug-name to identify the field. Used in the 'id' attribute of tags.
                $fields['field_id'],
                // Formatted title of the field. Shown as the label for the field during output.
                esc_html__($fields['field_title'], WC_EL_INV_FREE_TEXTDOMAIN),
                // Function that fills the field with the desired form inputs. The function should echo its output.
                $fields['field_callback'],
                // The slug-name of the settings page on which to show the section.
                $fields['field_page'],
                // The slug-name of the section of the settings page.
                $fields['field_section']
            );
        }
    }

    /**
     * Check Actiore tab.
     *
     * @since 1.0.0
     *
     * @param $tabs
     *
     * @return string The active tab
     */
    public function checkPageTab($tabs)
    {
        $tab = \WcElectronInvoiceFree\Functions\filterInput($_GET, 'tab', FILTER_SANITIZE_STRING);
        // Default active tab
        $activeTab = 'general';
        if ($tab && is_array($tabs)) {
            if (array_key_exists($tab, $tabs)) {
                $activeTab = $tab;
            }
        }

        return $activeTab;
    }

    /**
     * Page tabs
     *
     * @since 1.0.0
     */
    public function pageTab()
    {
        $active = $this->checkPageTab($this->tabs);
        $output = '';

        if (! empty($this->tabs)) {
            $output = '<ul class="wc_el_inv__tabs">';
            foreach ($this->tabs as $key => $tab) {
                if (isset($tab['header'])) {
                    $output .= sprintf('<li class="wc_el_inv__tabs--item"><a class="%s" href="%s">%s</a></li>',
                        $key === $active ? 'active' : '',
                        "admin.php?page={$this->menuSlug}&tab={$key}",
                        esc_html__($tab['header'], WC_EL_INV_FREE_TEXTDOMAIN)
                    );
                }
            }
            $output .= '</ul>';
        }

        echo $output;
    }

    /**
     * Options page callback
     *
     * @since 1.0.0
     */
    public function createPage()
    {
        $active = $this->checkPageTab($this->tabs);
        $submit = isset($this->tabs[$active]['submit']) ? $this->tabs[$active]['submit'] : true; ?>

        <?php
        /**
         * Before Settings Wrapper
         *
         * @since 1.0.0
         */
        do_action('wc_el_inv-before_settings_wrapper');

        ?>

        <div class="wc_el_inv-wrapper no-js">
            <form class="wrap wc_el_inv-form" id="options_form" name="options_form" method="post" action="options.php">
                <input name="tab" type="hidden" value="<?php echo esc_attr($active); ?>">
                <header class="wc_el_inv__header">
                    <div id="icon-themes" class="icon32"></div>
                    <h2>
                        <?php echo esc_html__('Electronic Invoice - Settings', WC_EL_INV_FREE_TEXTDOMAIN); ?><br>
                        <small class="version">v:<?php echo WC_EL_INV_FREE_VERSION; ?></small>
                    </h2>
                </header>
                <?php $this->pageTab(); ?>
                <?php
                // This hidden setting fields.
                settings_fields(
                // A settings group name. This should match the group name used in register_setting().
                    $this->optionsName . '_group'
                );
                // Prints out all settings sections added to a particular settings page.
                do_settings_sections(
                // The slug name of the page whos settings sections you want to output.
                    $this->menuSlug
                );

                if ($submit) {
                    // A submit button.
                    submit_button(
                    // The text of the button (defaults to 'Save Changes').
                        esc_html__('Save data', WC_EL_INV_FREE_TEXTDOMAIN)
                    );
                }

                ?>
            </form>

            <?php
            /**
             * After Settings Form
             *
             * @since 1.0.0
             */
            do_action('wc_el_inv-after_settings_form');
            ?>
        </div>

        <?php
    }

    /**
     * Sub menu page
     *
     * @since 1.0.0
     */
    function subMenuPageCreate()
    {
    }

    /**
     * Sanitize each setting field as needed
     *
     * @since 1.0.0
     *
     * @param $input
     *
     * @return array|bool|string
     */
    public function sanitizeOptions($input)
    {
        // Sanitize input
        $sanitizedValue = \WcElectronInvoiceFree\Functions\sanitize($input);
        $tab            = \WcElectronInvoiceFree\Functions\filterInput($_POST, 'tab', FILTER_SANITIZE_STRING);

        // If is not options
        if (! $this->options) {
            $this->options = $input;
        }

        // Update old value
        foreach ($this->options as $key => $option) {
            if (isset($input[$key]) && $input[$key] !== $option) {
                $this->options[$key] = $input[$key];
            }

            // When input is checkbox if not isset value is null
            // Forced off value
            // !! Only invoice tab have checkbox !!
            if ('invoice' === $tab) {
                if (null === $input[$key] && ! array_key_exists($key, $input) && 'on' === $this->options[$key]) {
                    $this->options[$key] = 'off';
                }
            }
        }

        // Merge options
        if ($sanitizedValue) {
            $this->options = array_merge($sanitizedValue, $this->options);
        }

        $sanitizedValue = $this->options;

        // Save extra data.
        $this->saveExtraData();

        return $sanitizedValue;
    }

    /**
     * Save Extra Data.
     *
     * @since 1.0.0
     */
    public function saveExtraData()
    {
        $active = $this->checkPageTab($this->tabs);

        $args[$this->optionsName] = array(
            'filter' => array(FILTER_SANITIZE_STRING),
            'flags'  => FILTER_FORCE_ARRAY,
        );

        // Key data saved
        $data = filter_var_array($_POST, $args);
        $key  = $data[$this->optionsName][KeyField::$keyArgs['name']];

        $secretApiKey = null;
        if (isset($key) && '' !== $key) {
            $secretApiKey = base64_encode(md5($key));
        }

        if ('general' === $active && $data && array_key_exists('wc_el_inv-settings-key', $data[$this->optionsName])) {
            update_option('wc_el_inv-secret-api-key', $secretApiKey);

            // Delete license transient.
            delete_transient('wc_el_inv-license_integration_check');
            delete_transient('wc_el_inv-license_check');
        }
    }

    /**
     * Instance
     *
     * @since 1.0.0
     *
     * @param  null
     *
     * @return OptionPage|object The Class Instance
     */
    public static function init()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }
}
