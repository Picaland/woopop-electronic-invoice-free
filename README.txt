=== WooPop -> (Fattura Elettronica) ===
Contributors: Picaland
Tags: fatturazione elettronica, fattura pdf, fattura elettronica, fattura xml
Requires at least: 4.6
Tested up to: 6.0
Stable tag: 3.0.3
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Con il plugin WooPOP generi la fattura elettronica in formato XML direttamente dal tuo WooCommerce.

= FUNZIONALITÀ: =
* Imposta i tuoi dati fiscali per la fatturazione elettronica nelle opzioni generali di Woocommerce
* Visualizza in fase di acquisto i campi per selezionare il tipo di cliente, P.IVA, Codice Fiscale, Codice Univoco o Email PEC.
* Guarda la lista dei file XML generati (fino a 5 nella versione gratuita).

= VERSIONE PREMIUM E ADD-ON: =
* <a href="https://woopop.it/?ref=1&wp_free_plugin">woopop.it</a>
* <a href="https://woopop.it/woocommerce-fattureincloud-plugin/?ref=1&wp_free_plugin">Fatture in Cloud Plugin</a>
* <a href="https://woopop.it/woocommerce-fatture-aruba-plugin//?ref=1&wp_free_plugin">Fatture Aruba Premium Plugin</a>

= OPZIONI FATTURA: =
* Prefisso per il numero di fattura.
* Numero di zeri da inserire nel numero di fattura
* Numeratore automatico progressivo fattura
* Suffisso fattura
* Abilita/disabilita il campo PEC/Codice Univoco (solo per Azienda e Persona Fisica titolare di P.IVA)
* Abilita/disabilita il Codice fiscale (solo per Azienda e Persona Fisica titolare di P.IVA)
* Attiva l'invio della fattura PDF via mail ad ordine completato
* Visualizzazione fattura in HTML

= FUNZIONALITÀ PREVISTE NELLA VERSIONE PREMIUM =
1. Scaricare le fatture in formato XML senza alcun limite.
2. Generare la fattura elettronica nella sezione "Fatture XML" e in ogni singolo ordine.
3. Scaricare le fatture sul tuo computer singolarmente o in formato .zip
4. Attivare il controllo VIES per i clienti dell'Unione Europea (non Italiani).
5. Inviare le fatture allo SDI direttamente da WooCommerce tramite add-on per Fatture in cloud

= TESTED UP TO/TESTATO FINO ALLE VERSIONI: =
* WooCommerce v. 6.9.x

== Installation ==

Questa sezione descrive come installare il plugin e farlo funzionare.

1. Carica la cartella 'woopop-electronic-invoice-free' nella directory /wp-content/plugins/
2. Attiva <strong>WooPop -> (Fattura Elettronica)</strong> dalla pagina ‘Plugins’ di WordPress.

== Screenshots ==

== Requirements ==

Php: >= 5.6
WordPress: >= 4.6

== Changelog ==

= 3.0.3 - 14/09/2022 =
* Add: support for WooCommerce 6.9.x
* Add: information and controls for the main options to configure

= 3.0.2 - 24/07/2022 =
* Fix: create xml query bug
* Add: support for WooCommerce 6.7.x

= 3.0.1 - 15/07/2022 =
* Fix: create pdf
* Fix: create pdf generate limit

= 3.0.0 - 24/06/2022 =
* Add: support for WordPress 6.0.x
* Add: support for WooCommerce 6.6.x
* Add: Download of XML invoices for the last 5 orders
* Fix: various style fix

= 2.0.4 - 25/05/2022 =
* Fix: list order XmlOrderListTable (unset order) if Invoice order not sent and order total is equal total refunded or order total is zero
* Add: support payment_method soisy
* Add: payment method info in the invoice table

= 2.0.3 - 24/05/2022 =
* Fix: filter_var support for PHP >= 8.1

= 2.0.2 - 22/05/2022 =
* Fix: optimization code and clear unnecessary
* Fix: filter_input, filter_var filter for PHP >= 8.1

= 2.0.1 - 21/05/2022 =
* Fix: Error due to missing file vendor

= 2.0.0 - 20/05/2022 =

* Dev: autoload psr-4
* Update: admin style
* Add: support for WooCommerce 6.5.x
* Add: support for WordPress 5.9.x

= 1.3.3 - 17/11/2021 =

* Fix: minor fix.
* Add: support for WooCommerce 5.9.x
* Add: support for WordPress 5.8.x

= 1.3.2 - 01/09/2021 =

* Add: support for WooCommerce 5.6.x
* Add: support for WordPress 5.8.x

= 1.3.1 - 12/05/2021 =

* Fix: minor fix and update description.

= 1.3.0 - 08/05/2021 =

* Add: support for WooCommerce 5.2.x
* Add: support for WordPress 5.7.x

= 1.2.0 - 30/03/2020 =

* Fix: support for WooCommerce 4.0.0

= 1.1.1 - 05/06/2019 =

* Fix: check on vat if you choose the receipt

= 1.1.0 - 08/05/2019 =

* Add: Receipt PDF template
* Add: Option to choose the type of document (invoice or receipt) in the checkout
* Tweak: Order/invoice list table layout

= 1.0.0 =
* Initial release
