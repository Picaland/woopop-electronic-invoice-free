<?php
/**
 * footer.php
 *
 * @since      1.0.0
 * @package    ${NAMESPACE}
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

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

$options = \WcElectronInvoiceFree\Admin\Settings\OptionPage::init();

?>
<div class="footer"
     style="margin-top:4em;padding:1rem 0;border-top:1px solid #ddd;border-bottom:1px solid #ddd;font-size:12px;">
    <?php echo $options->getOptions('invoice_pdf_footer', true); ?>
</div>

<?php if (! empty($data->billing) && 'IT' === $data->billing['country']) : ?>
    <p style="margin-top:2em;padding:5px;background:#ddd;font-size:12px;">
        <strong>
            <?php esc_html_e('This document does not constitute a valid invoice for the purposes of the DpR 633 26/10/1972 and subsequent amendments. The final invoice will be issued upon payment of the fee (article 6, paragraph 3, DpR 633/72).',
                WC_EL_INV_FREE_TEXTDOMAIN); ?>
        </strong>
    </p>
<?php endif; ?>
