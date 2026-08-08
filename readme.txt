=== AutoPhotos for WooCommerce ===
Contributors: autophotosai
Tags: woocommerce, product photos, image editing, background removal, ai
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
WC requires at least: 7.0
WC tested up to: 11.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click connect your store to your AutoPhotos.ai account to process product photos from the AutoPhotos panel.

== Description ==

AutoPhotos for WooCommerce is a lightweight bridge between your WooCommerce
store and your [AutoPhotos.ai](https://autophotos.ai/) account.

Normally, connecting a store to AutoPhotos means going to WooCommerce ->
Settings -> Advanced -> REST API, generating a key by hand, and pasting it
into the AutoPhotos panel. This plugin does that step for you: generate a
one-time pairing code on your AutoPhotos account, paste it into this
plugin's settings page, and your store is connected — no manual key
creation.

All photo processing still happens in your AutoPhotos panel at
[autophotos.ai/integrations/woocommerce/](https://autophotos.ai/integrations/woocommerce/),
billed against your normal AutoPhotos credit balance. This plugin does not
process any images itself and does not add anything to your storefront —
it only manages the connection.

= What this plugin does =

* Generates a WooCommerce REST API key (read/write) for your store.
* Sends that key, together with your pairing code, to AutoPhotos.ai so your
  account and your store are linked.
* Lets you disconnect at any time from the plugin's settings page, which
  revokes the key it created.

= What this plugin does NOT do =

* It does not upload, store, or process any product images itself.
* It does not modify your products, theme, or storefront.
* It does not communicate with AutoPhotos.ai except during the one-time
  connect/disconnect action.

== Installation ==

1. Install and activate WooCommerce if you haven't already.
2. Install and activate this plugin.
3. Go to WooCommerce -> AutoPhotos.
4. Open [autophotos.ai/integrations/woocommerce/](https://autophotos.ai/integrations/woocommerce/)
   in another tab, sign in, and click "Generate pairing code".
5. Paste the code into the plugin's settings page and click Connect.
6. Process your product photos from the AutoPhotos panel.

== Frequently Asked Questions ==

= Do I need an AutoPhotos.ai account? =

Yes. Photo processing happens on autophotos.ai using your own AutoPhotos
credit balance — this plugin only connects your store to that account.

= What permissions does the generated API key have? =

Read/write, the same as if you created a key by hand under WooCommerce ->
Settings -> Advanced -> REST API. AutoPhotos uses it to read your product
photos and write processed results back to the product gallery.

= How do I disconnect? =

Go to WooCommerce -> AutoPhotos and click Disconnect. This revokes the
WooCommerce REST API key the plugin created.

== Screenshots ==

1. Paste the pairing code you generated on your AutoPhotos account.
2. Once connected, process product photos from your AutoPhotos panel — or disconnect any time.

== Changelog ==

= 1.0.0 =
* Initial release: pairing-code connect flow.
