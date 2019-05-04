=== WooPop -> (Fattura Elettronica) ===
Contributors: Picaland
Tags: piva, codice fiscale, codice univoco, fattura pdf, fattura elettronica, fattura xml, woocommerce
Requires at least: 4.6
Tested up to: 5.1.1
Stable tag: 1.0.0
Requires PHP: 5.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Integrato con WooCommerce, aggiunge il necessario per un'ecommerce italiano, si occupa di salvare tutti i dati per la Fatturazione Elettronica e crea il PDF della fattura / nota di credito.
Nella pagina di checkout vengono aggiunti i campi per selezionare il tipo di cliente, P.IVA, Codice Fiscale, Codice Univoco o Email PEC.

= Opzioni Impostazioni generali di integrazione con WooCommerce: =
1. Nelle opzioni generali di WooCommerce vengono aggiunti altri campi realtivi alla fattura.
2. E' possibile impostare come richiesti i campi P.IVA e Codice Fiscale (Per IT sempre richiesti)
3. Nascondere i campi P.IVA e Codice Fiscale se lo stato del cliente non è nell'Unione Europea.

= Opzioni Fattura: =
1. Prefisso per il numero di fattura.
2. Numero di zeri da inserire nel numero di fattura
3. Numeratore automatico progressivo fattura
4. Suffisso fattura
5. E' possibile disabilitare il campo PEC/Codice Univoco (solo per Azienda e Persona Fisica titolare di P.IVA),
in tal caso il Codice univoco viene valorizzato con "0000000"
6. E' possibile disabilitare Codice fiscale (solo per Azienda e Persona Fisica titolare di P.IVA)
8. Link per stampare la fattura nella lista ordini.
9. Attivare l'invio della fattura PDF via mail ad ordine completato
10. Visualizzazione fattura in HTML
11. E' possibile impostare la url del logo da mostrare in fattura
12. Testo per il footer della fatture PDF.

= Tabella Fatture: =
In questa vengono visulizzati tutti gli ordini in lavorazione / completati con tutti i dati relativi alla fattura.
1. E' possibile filtrare le fatture per data
2. E' possibile salvare il file PDF della fattura.

= Altre funzionalità è opzioni sono previste nella versione PREMIUM =
* Generare le fatture XML nella sezione "Fatture XML" e in ogni singolo ordine.
* Scaricare le fatture sul tuo computer singolarmente o in formato .zip
* Attivare il controllo VIES per i clienti dell'Unione Europea (non Italiani).
* Marcare come inviate le fatture per una migliore gestione ed evitare errori.

E molto altro ancora...

= Tested up to/Testato fino alle versioni: =
* WooCommerce v. 3.6.2

= Links =

* VERSIONE PREMIUM: <a href="https://woopop.it/">woopop.it</a>

== Installation ==

Questa sezione descrive come installare il plugin e farlo funzionare.

1. Carica la cartella 'woopop-electronic-invoice-free' nella directory /wp-content/plugins/
2. Attiva <strong>WooPop -> (Fattura Elettronica)</strong> dalla pagina ‘Plugins’ di WordPress.

== Requirements ==

Php: >= 5.6
WordPress: >= 4.6

== Changelog ==

= 1.0.0 =
* Initial release