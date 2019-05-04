<?php
/**
 * taxRegime.php
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

return apply_filters('wc_el_inv-general_shop_tax_regime', array(
    'IT' => array(
        'RF01' => esc_html__('Ordinario', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF02' => esc_html__('Contribuenti minimi (art.1, c.96-117, L. 244/07)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF03' => esc_html__('Nuove iniziative produttive (art.13, L. 388/00)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF04' => esc_html__('Agricoltura e attività connesse e pesca (artt.34 e 34-bis, DPR 633/72)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF05' => esc_html__('Vendita sali e tabacchi (art.74, c.1, DPR. 633/72)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF06' => esc_html__('Commercio fiammiferi (art.74, c.1, DPR 633/72)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF07' => esc_html__('Editoria (art.74, c.1, DPR 633/72)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF08' => esc_html__('Gestione servizi telefonia pubblica (art.74, c.1, DPR 633/72) ', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF09' => esc_html__('Rivendita documenti di trasporto pubblico e di sosta (art.74, c.1, DPR 633/72', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF10' => esc_html__('Intrattenimenti, giochi e altre attività di cui alla tariffa allegata al DPR 640/72 (art.74, c.6, DPR 633/72)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF11' => esc_html__('Agenzie viaggi e turismo (art.74-ter, DPR 633/72)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF12' => esc_html__('Agriturismo (art.5, c.2, L. 413/91)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF13' => esc_html__('Vendite a domicilio (art.25-bis, c.6, DPR 600/73)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF14' => esc_html__('Rivendita beni usati, oggetti d’arte, d’antiquariato o da collezione (art.36, DL 41/95)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF15' => esc_html__('Agenzie di vendite all’asta di oggetti d’arte, antiquariato o da collezione (art.40-bis, DL 41/95', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF16' => esc_html__('IVA per cassa P.A. (art.6, c.5, DPR 633/72)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF17' => esc_html__('IVA per cassa (art. 32-bis, DL 83/2012)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF19' => esc_html__('Regime forfettario (art.1, c.54-89, L. 190/2014)', WC_EL_INV_FREE_TEXTDOMAIN),
        'RF18' => esc_html__('Altro', WC_EL_INV_FREE_TEXTDOMAIN),
    ),
));