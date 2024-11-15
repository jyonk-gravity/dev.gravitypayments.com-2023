=== ShortPixel Adaptive Images - WebP, AVIF, CDN, Image Optimization ===
Contributors: ShortPixel
Tags: image optimization, convert, webp, lazy load, avif
Requires at least: 4.7
Tested up to: 6.6
Requires PHP: 5.6.40
Stable tag: 3.9.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html


Start serving properly sized, smart cropped & optimized images, plus CSS, JS and fonts from our CDN with a click; Automatic AVIF & WebP support.

== Description ==

**An easy-to-use plugin that lets you solve any problems with images and improve your website's Core Web Vitals in a minute.**

Imagine if you could solve all your website's image-related problems and increase pagespeed and performance with a simple click, would not that be great?
Usually, images are the biggest resource on a web page. With just one click, ShortPixel Adaptive Images replaces all the pictures on your website with properly sized, smartly-cropped and optimized images and uploads them to ShortPixel's global CDN.
And for even more Google love, the plugin delivers next-gen WebP or AVIF images to the right browsers auto-magically! :-)

Using ShortPixel Adaptive Images also helps with <a href="https://web.dev/vitals/#core-web-vitals" target="_blank">Core Web Vitals (CWV)</a>'s Largest Contentful Paint (LCP), First Input Delay (FID) and Cumulative Layout Shift (CLS). 
This is an important SEO factor that Google uses to rank pages. The smaller the CWV values are, the better for your website.


= Do I need this plugin? =
If you have a WordPress website with images, the answer is most likely yes!
Have you ever tested your website with tools like PageSpeed Insights or GTmetrix and received complaints that the images are not the right size or are too big? Or that you should be using "next gen" images like WebP or AVIF? Or that the website should "defer offscreen images"?
ShortPixel Adaptive Images comes to the rescue and solves the problems with images on your website in no time.
In addition to images, CSS, JS and font files are also minimized and delivered from our global CDN.

= What are the benefits? =

<iframe src="https://player.vimeo.com/video/699250696?h=9fc61ec975&byline=0&portrait=0" width="640" height="360" frameborder="0" allow="autoplay; fullscreen; picture-in-picture" allowfullscreen></iframe>

= What are the features? =
* new! you can now set your custom domain to serve images or JSS/CSS files, e.g. cdn.example.com. <a href="https://shortpixel.com/knowledge-base/article/569-how-to-serve-the-images-from-a-custom-domain" target="_blank">Read more here</a>.
* new, lightweight, pure JavaScript Adaptive Images Engine (jQuery no longer required)
* same visual quality, but smaller images thanks to ShortPixel algorithms
* smart cropping - <a href="https://shortpixel.com/knowledge-base/article/182-what-is-smart-cropping" target="_blank">see an example</a>
* serve only images of appropriate size, depending on the viewport of the visitor
* lazy load support with adjustable threshold; browser native lazy loading support is also available
* automatically serves WebP and AVIF images to browsers that support this format. Animated GIFs are also supported and can will converted to animated WebP and animated AVIF!
* caching and serving from a global CDN for images as well as CSS, JS and fonts
* CSS/JS files are minimized automatically
* all major image galleries, sliders and page builders are supported
* onboarding wizard with a tool that suggests the best settings for each website
* support for low quality image placeholders (LQIP)
* support for JPEG, PNG, GIF, TIFF, BMP
* no need for a separate AVIF or WebP converter plugin, the original images are automatically converted to WebP or AVIF 
* adjustable size breakpoints when resizing images
* possibility to disable plugin functionality for logged in users
* multiple types of exclusions available, including and advanced Image Checker Tool
* Easy and Advanced mode for settings with a variety of settings for an increased flexibility of the plugin functionality


= Do I need an account to test this plugin? =
No, just install it and activate it on your WordPress website. You will then automatically receive 500 MB of CDN traffic every month. That's about 500 visits/month!

= How much does it cost? =
When using ShortPixel Adaptive Images, only CDN traffic <a href="https://shortpixel.com/knowledge-base/article/460-spai-new-how-are-the-credits-counted">is counted</a> if you choose to use our CDN. With the free plan, you get 100 credits for image optimization, which is equivalent to 500 MB of CDN traffic or about 500 visits/month. Paid plans start at $4.99 and are available as both <a href="https://shortpixel.com/pricing-one-time">one-time</a> and <a href="https://shortpixel.com/pricing">monthly</a> plans.
Even better, if you already use <a href="https://wordpress.org/plugins/shortpixel-image-optimiser/">ShortPixel Image Optimizer</a>, you can use the same credits for ShortPixel Adaptive Images!

= How does it work? =
Different visitors have different devices (laptop, mobile phone, tablet), each with its own screen resolution. ShortPixel AI takes into account the resolution of the device and then provides the right size image for each placeholder.
Let us take a web page with a single image of 640×480 pixels.
When viewed from a laptop, the image retains its 640×480 pixel size, but is optimized and delivered from our CDN.
When the same web page is viewed from a mobile phone, the image (for example) is resized to 300x225 pixels, optimized, and delivered via our CDN.
In this way, neither time nor bandwidth is wasted for visitors.

**Other plugins by [ShortPixel](https://shortpixel.com):**

* [FastPixel Caching](https://wordpress.org/plugins/fastpixel-website-accelerator/) - WP Optimization made easy
* [ShortPixel Image Optimizer](https://wordpress.org/plugins/shortpixel-image-optimiser/) - Image optimization & compression for all the images on your website, including WebP & AVIF delivery
* [Enable Media Replace](https://wordpress.org/plugins/enable-media-replace/) - Easily replace images or files in Media Library
* [reGenerate Thumbnails Advanced](https://wordpress.org/plugins/regenerate-thumbnails-advanced/) - Easily regenerate thumbnails
* [Resize Image After Upload](https://wordpress.org/plugins/resize-image-after-upload/) - Automatically resize each uploaded image
* [WP SVG Images](https://wordpress.org/plugins/wp-svg-images/) - Secure upload of SVG files to Media Library 


**Get in touch!**

* Email <a href="https://shortpixel.com/contact" target="_blank">https://shortpixel.com/contact</a>
* Twitter <a href="https://twitter.com/shortpixel" target="_blank">https://twitter.com/shortpixel</a>

== Frequently Asked Questions ==

= What happens when the quota is exceeded? =

In your WP dashboard you will be warned when your quota is about to be exhausted and also when it has been exceeded. When the quota is exhausted, the plugin will simply serve the original images from your server, without compression or resizing, until the quota resets.

= What Content Delivery Network (CDN) do you use? =

ShortPixel Adaptive Images uses <a href="https://bunny.net/" target="_blank">bunny.net</a> to offload the images - a global CDN with over <a href="https://bunny.net/network" target="_blank">90 edge locations</a> around the world.
Both the free and paid plans use the same CDN with the same number of locations.
You can check for yourself how the bunny.net CDN compares to other CDN providers - <a href="https://www.cdnperf.com/">here</a> (worldwide) and <a href="https://www.cdnperf.com/#!performance,North%20America">here</a> (North America).


= Can I use a different CDN? =

Sure. <a href="https://shortpixel.com/knowledge-base/article/180-can-i-use-a-different-cdn-with-shortpixel-adaptive-images">Here</a> is how to configure it with Cloudflare and <a href="https://shortpixel.com/knowledge-base/article/200-setup-your-stackpath-account-so-that-it-can-work-with-shortpixel-adaptive-images-api">here</a>’ is how to configure it with STACKPATH. Please note that if you use another CDN instead of CDN traffic, image credits will be consumed.
If you need further help, please <a href="https://shortpixel.com/contact">contact us</a>.


= What happens if I deactivate the plugin? =
You can stop using SPAI at any time, but that means your website will suddenly slow down.
Basically, your website will revert to the original, unoptimized images delivered by your server

= Are there different image optimization levels available? =
Yes, you can compress images as Lossy, Glossy or Lossless.
You can learn more about the different optimization levels <a href="https://shortpixel.com/knowledge-base/article/11-lossy-glossy-or-lossless-which-one-is-the-best-for-me">here</a> or run some free image optimization tests <a href="https://shortpixel.com/online-image-compression">here</a>.

= I already used ShortPixel Image Optimizer, can I also use this? =
Certainly!

= What is the difference between this plugin and ShortPixel Image Optimizer? =
You can see <a href="https://shortpixel.com/knowledge-base/article/179-shortpixel-adaptive-images-vs-shortpixel-image-optimizer">here</a> the differences between the two services.

= Where can I optimize my images? There's nothing in my admin panel. =
SPAI works differently than a normal image optimizer. <a href="https://shortpixel.com/knowledge-base/article/132-how-shortpixel-adaptive-images-work" target="_blank">Here is</a> what it does.

= How can I make sure that the plugin is working well? =
You have more information about this <a href="https://shortpixel.com/knowledge-base/article/240-is-shortpixel-adaptive-images-working-well-on-my-website" target="_blank">here</a>.

= I want to use the plugin, what do I have to do? =
You can find the detailed instructions <a href="https://shortpixel.com/knowledge-base/article/231-step-by-step-guide-to-install-and-use-shortpixel-adaptive-images-spai" target="_blank">here</a>. 

= My images are getting redirected from cdn.shortpixel.ai, why? =
Have a look at <a href="https://shortpixel.com/knowledge-base/article/148-why-are-my-images-redirected-from-cdn-shortpixel-ai" target="_blank">this article</a>.

= Where do I report security bugs found in this plugin? =
Please report security bugs found in the source code of the ShortPixel Adaptive Images plugin through the [Patchstack Vulnerability Disclosure Program](https://patchstack.com/database/vdp/shortpixel-adaptive-images). The Patchstack team will assist you with verification, CVE assignment, and notify the developers of this plugin.

= SPAI does not work well, I have some problems. =
Please check the following things: 
1) Make sure your domain <a href="https://shortpixel.com/knowledge-base/article/94-how-to-associate-a-domain-to-my-account" target="_blank">is associated to your account</a>;
2) Make sure you have enough credits available in your account;
3) Take a look at <a href="https://shortpixel.com/knowledge-base/article/220-i-installed-shortpixel-adaptive-images-but-it-doesnt-seem-to-work" target="_blank">this article</a>;
4) Take a look at <a href="https://shortpixel.com/knowledge-base/category/307-shortpixel-adaptive-images" target="_blank">our knowledge base</a>.



If nothing seems to work, please <a href="https://shortpixel.com/contact" target="_blank">contact us</a>.

== WP CLI commands ==

Use the following WP CLI commands to clear the CSS cache and the Low Quality Image Placeholders:
    `wp shortpixel clear_css`
    `wp shortpixel clear_lqips`

== For developers ==

If there are main images in the Media Library that end with the usual thumbnail size suffix (e.g. -100x100), please set this in wp-config.php: 

    define('SPAI_FILENAME_RESOLUTION_UNSAFE', true);

If you need to do post-processing in JavaScript after the image/tag has been updated by ShortPixel AI, you can add a callback like this:

    jQuery( document ).ready(function() {
        ShortPixelAI.registerCallback('element-updated', function(elm){
            // elm is the jQuery object, elm[0] is the tag
            console.log("element updated: " + elm.prop('nodeName'));
            });
    });

To change the original URL of the image that is detected by ShortPixel, use this filter that receives the original URL:

    add_filter('shortpixel/ai/originalUrl', 'my_function');

Sometimes when the option to crop images is enabled, SPAI thinks it is not safe to crop an image, but you want to crop it anyway. Please add this attribute to force cropping:

    <img data-spai-crop="true" ....

ShortPixel Adaptive Images triggers a JS event after the initial processing of the HTML page in the browser: spai-body-handled, an event after each processed DOM mutation when at least one URL has been replaced: spai-block-handled and an event after the URL of each element has its URL updated lazily ( entering the viewport): spai-element-handled

To exclude certain images, you can also add the following attributes within the 'IMG' tag to the markup:

    `<img data-spai-excluded="true" ...>` --> this will completely exclude from processing the image which has this attribute;
    `<img data-spai-eager="true" ...>` --> this will exclude the image from being lazy-loaded by the plugin;
    `<img data-spai-noresize="true" ...>` --> this will prevent the image from being resized by the plugin.

For adding custom replacement rules use:

    add_filter('shortpixel/ai/customRules', 'my_function');

The function is given an array and should append to that array elements with the following structure: ['tagName', 'attrToBeChecked', 'classFilter', 'attributeFilter', false(reserved), 'attributeValueFilter', isEager(bool)]. As of version 3.0, you can also append ShortPixel\AI\TagRule instances, something like this.
A real-world example of custom image attributes, a custom srcset, and a custom JSON data attribute:

`add_filter('shortpixel/ai/customRules', 'spai_to_iconic');
function spai_to_iconic($regexItems) {
    //lazy-loaded data-iconic-woothumbs-src attribute
    $regexItems[] = new ShortPixel\AI\TagRule('img', 'data-iconic-woothumbs-src');
    //eager attribute
    $regexItems[] = new ShortPixel\AI\TagRule('img', 'data-large_image', false, false, false, false, true);
    //lazy srcset style attribute.
    $regexItems[] = new ShortPixel\AI\TagRule('img', 'srcset', false, false, false, false, false,
                    'srcset', 'replace_custom_srcset');
    $regexItems[] = new ShortPixel\AI\TagRule('div', 'data-default', 'iconic-woothumbs-all-images-wrap', false, false, false, false,
                        'srcset', 'replace_custom_json_attr');
    return $regexItems;
}`

The parameters of the rule are, in this order:
* tagName - the tag name
* attribute to be replaced
* classFilter - only elements having the class, default false
* attrFilter - only elements having the attribute, default false
* attrValFilter only elements having the attribute with the specified value, default false
* mergeAttr - advanced usage (see code), default false
* eager - if true the image is replaced server-side, otherwise is lazy-loaded
* type - advanced usage (see code), default is 'url', can also be 'srcset' if it has a srcset or json structure
* callback - advanced usage (see code), default false. Needs to be 'replace_custom_srcset' if the type is srcset, or 'replace_custom_json_attr' if the type is json
* quickMatch - advanced usage (see code), default false
* frontEager -advanced usage (see code), default false

In the same manner if you need a rule to be applied only on the front-end (javascript) you can use the following filter:

    add_filter('shortpixel/ai/customFrontendRules', 'my_function');

This rule will only be applied by the New JS Engine (so you need to have the option enabled) and is useful if you have content that is rendered by JavaScript and you need the replacement to be made after the content is rendered.

== Screenshots ==

1. Example site metrics on PageSpeed Insights before: Low

2. Example site metrics on PageSpeed Insights after: Good

3. Example site metrics on GTMetrix before: F score

4. Example site metrics on GTMetrix after: B score

5. Main settings page

6. Advanced settings page

== Changelog ==

= 3.9.4 =
Release date: October 1, 2024
* Fix: No longer add the `loading=lazy` attribute for images marked as eager (for example, with `data-perfmatters-preload`);
* Fix: Some deprecation warnings were displayed in various situations;
* Fix: Make sure the plugin uses the correct ajax_url when changing the default WordPress paths;
* Fix: Updated error message when trying to use the API key from SPIO and it is invalid;
* Language: 2 new strings added, 0 updated, 0 fuzzed and 0 deprecated.

= 3.9.3 =
Release date: July 25, 2024
* Fix: Some notifications could no longer be dismissed;
* Fix: Some PHP deprecation notices on PHP 8.3 have been fixed;
* Fix: The "Forget" button that allows you to remove the API key from the settings did not work;
* Fix: The "Clear LQIP cache" action from the admin bar menu was missing a nonce;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.9.2 =
Release date: June 25, 2024
* Fix: Exclusions did not always work correctly since version 3.9.0;
* Fix: "Let ShortPixel check my web page" from the onboarding wizard now works correctly;
* Fix: Some deprecation warnings were displayed under PHP 8.3;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.9.1 =
Release date: June 7, 2024
* Fix: CSS parsing setting was not saved when Elementor was active;
* Fix: The "Clear CSS Cache" option in the admin bar now works correctly;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.9.0 =
Release date: May 22, 2024
* New: Added support for server-side class selectors used for exclusions without JS.
* New: Added support for `<img>` tags where only srcset is present;
* Fix: CSS CORS issues when the original resource is not delivered with the correct Access-Control-Allow-Origin header are now fixed;
* Fix: The settings texts and the SPAI menu have been updated;
* Fix: Defer the front-end JS jquery-test.js when jQuery is used;
* Fix: The dynamic properties added to the options caused PHP deprecation warnings;
* Language: 2 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.8.4 =
Release date: April 22, 2024
* Fix: ShortPixel News now also works correctly with a missing featured image;
* Fix: A potential Server Side Request Forgery (SSRF) vulnerability found by the PatchStack team has been patched;
* Fix: A potential Cross Site Request Forgery (CSRF) vulnerability found by the PatckStack team has been patched;
* Language: 1 new strings added, 0 updated, 0 fuzzed, and 1 deprecated.

= 3.8.3 =
Release date: April 2, 2024
* Fix: Patched a Broken Access Control vulnerability, found by the PatchStack team (thanks!);
* Fix: Various PHP Warnings have been fixed to ensure full compatibility up to PHP 8.3;
* Fix: In certain cases, an error was generated by the LQIP functionality;
* Compat: Integration with Litespeed has been improved to avoid errors in certain cases;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.8.2 =
Release date: December 22, 2023
* Tweak: When available, retina images (with @2x in the filename) are now used when appropriate;
* Fix: Some PHP warnings were displayed on the plugin settings page in some cases;
* Fix: Eliminating exclusions with the Image Checker Tool did not work properly;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.8.1 =
Release date: December 7, 2023
* Fix: Display a notice if the CURL requests are timing out;
* Fix: The robot head was not displayed correctly for all notifications;
* Fix: Some warnings were displayed if the settings were not yet saved during the initial installation;
* Fix: Image detection was improved in some cases when the URLs contained unprocessable file extensions;
* Fix: The "Re-check" action for low credits notifications did not work correctly in all situations;
* Fix: The link "Refresh on CDN" no longer worked via the Image Checker Tool;
* Language: 2 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.8.0 =
Release date: August 7, 2023
* New: remove the `/http(s)://` inside the CDN URL, use https by default for the original URLs and use a parameter to specify http;
* Fix: Backgrounds of *mask-image CSS rules are now handled correctly;
* Fix: Do not use postmeta data for LQIPs when the option is disabled;
* Fix: Added automatic exclusion for WooCommerce product images, to avoid issues with not displaying zoomed images;
* Fix: Minor updates to the wording of the plugin;
* Language: 0 new strings added, 1 updated, 0 fuzzed, and 0 deprecated.

= 3.7.3 =
Release date: May 30, 2023
* Fix: correct handling of background images defined with `-webkit-image-set` attribute;
* Fix: correct handling of all cases with `<script type='text/template'>` blocks;
* Fix: New AI Engine: fix some missing backgrounds caused by inaccessible CSS content (blocked by CORS) from JS;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.7.2 =
Release date: May 8, 2023
* Fix: WordPress nonce checking has been added to all Ajax calls to increase security;
* Fix: if the onboarding wizard was not yet completed, the Image Checker Tool did not work on the frontend;
* Fix: in some cases, the `srcset` and `data-srcset` parameters were not handled correctly by the plugin;
* Language: 1 new string added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.7.1 =
Release date: April 21, 2023
* Tweak: added information about custom CDN domains in the settings, which can now be enabled from the ShortPixel account dashboard;
* Language: 0 new strings added, 1 updated, 0 fuzzed, and 0 deprecated.

= 3.7.0 =
Release date: April 17, 2023
* New: The CDN now waits for CSS and JS files to be parsed to avoid CORS problems due to redirects;
* New: An intelligent tool has been added to recommend the best plan when running out of traffic;
* New: Added frontend-only rules with the New AI Engine, using the `shortpixel/ai/customFrontendRules` filter;
* New: Added a notification for when there is a redirect to the origin;
* Fix: Added a permanent dismiss option to the lazy-loading notification;
* Fix: Some options in the settings weren't saved correctly;
* Fix: When `data-spai-eager` was present, the `srcset` was identical for all resolutions;
* Fix: In some cases, the `<input>` element was parsed incorrectly;
* FIx: In some cases, the "Sizes from postmeta" option wasn't taken into account when parsing CSS files;
* Fix: Incompatibility with WooCommerce variation buttons is now fixed;
* Compat: Added integration for Featherlight plugin;
* Compat: Added integration for Lightbox Photoswipe;
* Compat: Improved compatibility with PHP 8.1 and 8.2;
* Language: 6 new strings added, 1 updated, 0 fuzzed, and 0 deprecated.

= 3.6.3 =
Release date: February 3, 2023
* Fix: further security strenghtening for the debugging parameter used for the new AI Engine;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.6.2 =
Release date: January 29, 2023
* Fix: possible XSS injection via debugging parameter for new AI Engine;
* Fix: new AI Engine: reduced the number of loaded images on a WooCommerce product page to minimum;
* Compat: improved integration of Avia gallery lightbox;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.6.1 =
Release date: December 28, 2022
* Compat: added compatibility with the Avia Gallery module;
* Fix: replace CDATA markers as line comments with block comments that allow minification of JS;
* Fix: in some cases the background images from sliders were not being displayed;
* New: new AI Engine: trigger events when replacing images;
* Fix: new AI Engine: correct handling of pseudo-selectors with gradient background images;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 deprecated.

= 3.6.0 =
Release date: December 21, 2022
* New: new AI Engine: switched to using data attributes instead of classes to avoid possible conflicts with other plugins;
* New: new AI Engine: added the option to perform synchronous initial parsing to avoid conflicts with dynamic content rendered by JS;
* Compat: added the query paramenter `SPAIOpt=off` to disable only SPAI and not other plugins;
* Fix: added exception when background data viewport is not defined;
* Language: 2 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.5.5 =
Release date: November 18, 2022
* Compat: Elementor's gallery/carousel widgets, which have a lightbox effect, now work correctly;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.5.4 =
Release date: November 7, 2022
* Fix: in certain situations the `noscript` tag was not added;
* Compat: added another lazy-loading attribute handled by the plugin: `data-lazy`;
* Tweak: minor updates to the settings layout for new plans that will be introduced soon;
* Language: 1 new strings added, 1 updated, 0 fuzzed, and 0 deprecated.

= 3.5.3 =
Release date: October 20th, 2022
* Fix: an error was showing up on PHP 8 when trying to use the same API Key as for SPIO;
* Fix: in some specific cases, the onboarding wizard wasn't fully working on PHP 5.6;
* Fix: various CSS changes and fixes for the settings page;
* Tweak: `content: url()` CSS rules are now parsed and replaced;
* Tweak: updated the login URLs to the ShortPixel dashboard to work with the new website;
* Fix: new AI Engine: infinite recursion on mutationCallback was triggering other mutations in some circumstances;
* Fix: new AI Engine: relative URLs inside stylesheets were resolved to the stylesheet URL not the one of the page;
* Fix: new AI Engine: added a check for exclusions in replaceBackgroundPseudoSrcs;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.5.2 =
Release date: September 21st, 2022
* Fix: when more fonts were added from inline CSS, only the first font was properly replaced;
* Fix: removed the doubled request to the site that was done for logged-in visitors;
* Fix: various small CSS fixes and code cleanup;
* Tweak: the import/export buttons are now hidden on mobiles and small screens;
* Compat: excluded the Instagram feed added with Essential Grid;
* Compat: fixed the settings CSS when the SEOPress plugin is active;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.


= 3.5.1 =
Release date: August 17th, 2022
* Fix: the compression level can now be saved correctly in both easy and advanced mode (sorry!);
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.5.0 =
Release date: August 16th, 2022
* New: added Easy and Advanced Mode for settings, for a smoother experience;
* New: the New AI Engine is now set as the default engine for new plugin installations;
* New: added the possibility to exclude pages, besides the other existing exclusions;
* New: the plugin also serves the fonts declared in inline style blocks from the CDN;
* New: added a menu in the wp-admin bar for settings, clear cache and the image checker tool (when visiting the public site while logged in);
* New: added action buttons for the notifications that were dismissed and had buttons;
* New: added a news block in the settings;
* Fix: the LQIPs do not use the fade-in effect as it is not needed;
* Fix: the CDN traffic is now displayed correctly, even for old AppSumo codes;
* Language: 38 new strings added, 1 updated, 0 fuzzed, and 1 obsoleted.

= 3.4.2 =
Release date: June 22nd, 2022
* Fix: removed `loading=lazy` also for rules that specify the element as eager;
* Fix: updated the LQIP path to `/spai/` instead of `/client/`;
* Fix: identify as valid HTML the content starting with Zero-Width Non-breaking Space `\uFEFF`;
* Fix: removed a double slash when the API returns an empty string for `get_api_url`;
* Tweak: added LQIP logging flag;
* Compat: do not parse pages when the `DONOTCDN` constant is active;
* Compat: added lazy attributes support for Essential Grid;
* Compat: added integration with Jupiter Child themes;
* Compat: added an eager exclusion for the MK slider;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.4.1 =
Release date: May 25th, 2022
* Compat: make the image load eagerly if Perfmatters marked it with `data-perfmatters-preload`;
* Compat: if a Global Gallery is present, replace all image a hrefs' eagerly;
* Fix: Elementor `data-elementor-open-lightbox` not handled by JS, left as a placeholder to be handled when the lightbox is displayed;
* Fix: updated the minify library to fix isolated cases of JS errors due to minify bugs;
* Fix: increase the backtracking limit for PCRE regex, needed for certain complex pages;
* Fix: remove `loading=lazy` if the image will be lazy-loaded by SPAI;
* Fix: the LQIP width/height regex was matching other elements inside the `<svg>` width/height;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.4.0 =
Release date: April 21st, 2022
* New: added a settings import & export functionality;
* New: added a notification in case the MBString PHP library is missing;
* Compat: excluded MailPoet email previews from processing to prevent missing images;
* Compat: fixed the WP Rocket integration for sites without jQuery;
* Fix: do not add `spai-bg-prepared` for DOM modifications unless at least one of the lazy bakgrounds options is set;
* Fix: fixed potential security issue: in special conditions, low-privileged users could perform certain actions with the plugin;
* Fix: added some CSS tweaks for the exclusions tab;
* Fix: new AI Engine: fix typo that was throwing console errors in some cases;
* Fix: new AI Engine: properly handle background-images that are non-url(), are defined on dynamically added `<style>` blocks and used in dynamically added DOM documents;
* Language: 5 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.3.1 =
Release date: March 23rd, 2022
* Tweak: exclusions now work with attributes, server-side (e.g. `img[width=200]` excludes all images with the `width="200"` attribute;
* Fix: properly handle multiple WooCommerce variation forms on the same page;
* Fix: SPAI was not loading after dismissing the jQuery alert and then choosing the New AI Engine in the settings;
* Fix: new AI Engine: properly split selectors that contain `,` inside them, e.g. `div (li.class1, span.class2)`;
* Language: 0 new strings added, 1 updated, 0 fuzzed, and 0 obsoleted.

= 3.3.0 =
Release date: March 2nd, 2022
* New: added notification when changing the API URL, about the credits consumption when using another CDN;
* New: SPAI now deactivates/activates itself automatically when running out/topping up credits to avoid useless redirects;
* Compat: updated the library used to minify CSS/JS files;
* Fix: the notice that shows up when running out of credits wasn't always displayed;
* Fix: a warning was showing up next to the upgrade notice sometimes;
* Fix: parsing JS with `<script>` tags which would fail for `// --> </script>` old-style HTML comments for JS;
* Fix: updated various strings still containing credits instead of traffic;
* Fix: a PHP warning was showing up in some cases;
* Fix: new AI Engine: certain backgrounds that are dynamically added to the page were not correctly handled;
* Fix: new AI Engine: parsing styles that are not already loaded when the parseSelectors is fired;
* Language: 4 new strings added, 28 updated, 1 fuzzed, and 0 obsoleted.

= 3.2.2 =
Release date: February 16th, 2022
* Tweak: add param to take into account lazy-loaded images in style blocks (and resize even if the original width is not known);
* Fix: the backgrounds with gradient and background weren't working correctly after 3.2.1;
* Fix: new AI Engine: fix handling `<style>` blocks inside DOM modifications;
* Fix: new AI Engine: fix displaying backgrounds that are inline images but not from SPAI, inside DOM modifications;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.2.1 =
Release date: January 25th, 2022
* Tweak: improved the documentation for adding custom replacement rules;
* Tweak: replace the `<source src ..>` if it contains an image;
* Compat: added integration with the `data-big`/`smallimg` attributes of the WPZOOM themes;
* Fix: when purging the cache from the Litespeed cache plugin SPAI will also purge its CSS cache;
* Fix: replacing the src attribute for methods SRCSET and BOTH wasn't working correctly all the time;
* Fix: a tag can now be excluded by a simple selector;
* Fix: remove the hard-coded ajax url and use the `ajaxurl` constant instead to ensure full compatibility with WP installs in sub-folders (like /wp/wp-admin/);
* Fix: the ImageChecker Tool now works properly with backgrounds handled by the new AI Engine;
* Fix: new AI Engine: always add `h_` when it's an LQIP;
* Fix: new AI Engine: fix regex exclusions usage (remove the `/` at the beginning and at the end);
* Fix: new AI Engine: ignore empty `url("")` backgrounds;
* Fix: new AI Engine: fix path and domain exclude selectors that would match anything;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.2.0 =
Release date: December 22nd, 2021
* New: if the same image is present in different sizes on the same page, it will only be loaded once and reused;
* New: added `data-spai-crop` in the new AI Engine, which can override the crop settings for specific images where this attribute is present;
* New: when purging the caches in WP Rocket, Swift Performance and Litespeed Cache, the CSS served by SPAI will also be purged automatically; 
* Tweak: the account status in settings now takes into account the billing cycles and correctly displays the traffic information;
* Tweak: the tool-tips in the Settings look better and are more visible;
* Compat: fixed various compatibility issues with Internet Explorer in the new AI Engine;
* Fix: regex exclusions weren't properly working in some cases, with the new AI Engine;
* Fix: a fatal error was thrown in certain configurations when WP Rocket cache was purged;
* Fix: do not replace `data:image` inline placeholders that are not generated by SPAI;
* Fix: the special crop on background images wasn't properly working for retina displays;
* Fix: the new AI will load the original image if no LQIP is available;
* Fix: loading original URL for backgrounds that also have for ex. `background:transparent` in styles;
* Fix: elements with other inline images as backgrounds added later to the document via mutations;
* Fix: some of the settings suggested by the automatic settings tool weren't displayed correctly;
* Fix: background images having gradient an url() with no quotes on Chrome;
* Fix: the Image Checker Tool now works properly with backgrounds handled by the new AI Engine;
* Fix: issues with background-size: auto 100%;
* Fix: if the API key was incorrect in the on-boarding wizard, you couldn't enter it again;
* Language: 16 new strings added, 4 updated, 0 fuzzed, and 12 obsoleted.

= 3.1.3 =
Release date: November 24th, 2021
* Fix: Instagram galleries and feeds should be working properly now;
* Fix: the special crop parameter was multiplying the original resolution of the image on retina displays;
* Fix: AVIF was still served in some cases, even if the option was disabled;
* Fix: serve the new AI JS from the custom domain/CDN, if the JS serving from CDN is enabled;
* Fix: file type options were hidden in the settings when only AVIF was active (no WebP);
* Fix: make no resize exclusions remain lazy;
* Fix: when there were HTML comments before the DOCTYPE declaration, SPAI wasn't working properly;
* Language: 0 new strings added, 1 updated, 0 fuzzed, and 0 obsoleted.

= 3.1.2 =
Release date: November 17th, 2021
* Fix: added back the jQuery dependency for the old AI engine;
* Fix: PHP warning that was showing up in certain cases;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.1.1 =
Release date: November 12th, 2021
* Fix: deactivate unintentionally left active logging;
* Fix: undefined notice about video-placeholder;
* Fix: various small fixes to the new AI engine's JS processing;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 3.1.0 =
Release date: November 11th, 2021
* New: added Size Breakpoints option for resizing images, especially useful when the images resize a lot on various screen sizes;
* New: the necessary headers are now automatically added when using Apache, in order to avoid CORS issues with the new AI engine;
* New: added font preload support for the CDN (when a CSS file is parsed, the fonts will already be prepared for the CDN);
* New: added separate options for WebP and AVIF, for more control on these settings;
* New: added tooltips for the plugin settings for an easier understanding;
* Compat: added compatibility with the Agile Store Locator plugin;
* Compat: the placeholder image that WP Rocket uses for YouTube embeds is now automatically excluded from being processed;
* Fix: the plugin upgrade notice mechanism wasn't working anymore;
* Fix: the parsing works now in `<style>` blocks when the `background-image` is added right after the `{`;
* Fix: the image dimensions are now properly retrieved from SVG files, when they have it defined either as `viewBox` or as `width` and `height`;
* Fix: LQIP's were always displayed when using the new AI Engine, even if the option was disabled;
* Language: 18 new strings added, 0 updated, 0 fuzzed, and 6 obsoleted.

= 3.0.0 =
Release date: October 14th, 2021
* New: the new Adaptive Images engine (pure JavaScript based, no jQuery required) can now be enabled from the Behavior tab in Settings -> ShortPixel AI;
* New: option to serve the local JS files from the CDN;
* New: local fonts are now served as well from the CDN, when referred from parsed CSS files;
* Tweak: re-enabled the automatic delivery of AVIF for supporting browsers;
* Tweak: various re-wording and spelling corrections throughout the entire plugin strings;
* Tweak: the plugin is now fully tested with PHP8;
* Tweak: removed the option to revert to the 1.8.x settings upon deactivation;
* Fix: fixed various issues with different integrations (caching plugins, galleries etc.);
* Fix: when deactivating the delivery of next-generation images for certain image types, the entire option was wrongly deactivated;
* Language: 18 new strings added, 51 updated, 0 fuzzed, and 12 obsoleted.

= 2.3.3 =
Release date: June 30th, 2021
* Fix: issue with validating API key
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 2.3.2 =
Release date: June 29th, 2021
* Temporarily deactivate AVIF pending codec bug fix (https://github.com/xiph/rav1e/issues/2757);
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 2.3.1 =
Release date: June 28th, 2021
* New: Version the javascript in the file name in order to get around more stubborn caches;
* Fix: do not parse AJAX responses to uploads;
* Fix: nested element that has a different background - was taking the background of the parent element;
* Fix: notice in logs sometimes when domain info from server;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 2.3.0 =
Release date: June 17th, 2021
* New: images (including the ones from CSS files) are now served automatically in the new AVIF format to supporting browsers;
* New: moved the JS detection mechanism for WebP/AVIF support directly to the CDN level, so no JS is required anymore for this;
* Language: 0 new strings added, 6 updated, 0 fuzzed, and 0 obsoleted.

= 2.2.4 =
Release date: June 14th, 2021
* Compat: added a constant - `SPAI_ELEMENTOR_WORKAROUND` - to deactivate the parsing of Elementor modules that are resulting in critical errors;
* Compat: workaround for WP Rocket that calls in certain circumstances the filter `rocket_css_content` with only one parameter;
* Fix: some warnings when lqip queue is not array were showing up in some cases;
* Fix: wrong array key when the no background calculation can't determine crop size and returns just width and height;
* Fix: iPhone issues with parsing stylesheets while also improving page responsiveness while parsing them (async);
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 2.2.3 =
Release date: May 18th, 2021
* New: also parse inside `<script type="text/template">` blocks;
* Fix: the background crop resize wasn't working in several cases, which is now fixed;
* Fix: update the notification text about the next generation images served by SPIO;
* Fix: cases when a mutation has backgrounds from an existing CSS block are now properly handled;
* Fix: the special crop feature now handles correctly the situations when the width parameter isn't the first one;
* Fix: the inline background selector will handle situations with no space before the CSS class definition;
* Fix: remove the default values for JS parameters in order to support IE11;
* Fix: the images from `li` elements added with `data-thumb` are now replaced;
* Fix: the URL exclusions are checked when replacing inside JS blocks too;
* Language: 0 new strings added, 2 updated, 0 fuzzed, and 0 obsoleted.

= 2.2.2 =
Release date: April 29th, 2021
* Fix: the minified version of the plugin CSS files was bigger than the not minified one;
* Fix: find local file when URL contains a path element before wp-content, that is not present on disk;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 2.2.1 =
Release date: April 26th, 2021
* Compat: added integration with Real3D Flipbook;
* Fix: there was a "Class not found" error in some cases when purging LiteSpeed cache from our plugin;
* Fix: in some cases, the size of background images wasn't properly set;
* Fix: protection added for very large number of product variations; the plugin will now work properly in these cases;
* Language: 0 new strings added, 0 updated, 0 fuzzed, and 0 obsoleted.

= 2.2.0 =
Release date: April 20th, 2021
* New: added filter `shortpixel/ai/customRules` for custom replacement rules;
* New: added proper lazy loading for background images;
* New: take into account the `background-*` CSS styles: size, position, etc.;
* New: lazy load the images in the CSS blocks;
* New: handle correctly multiple URLs in the same `background-image:` declaration;
* New: when running out of credits you can now have an option to top-up directly from the plugin settings;
* Compat: added an integration with the Uncode theme and its iLightBox component;
* Compat: added integration with WPC Variations Table;
* Compat: added integration with Soliloquy Slider Plugin;
* Compat: also integrate properly with Divi child themes;
* Compat: improved the integration with Elementor, all images should now be properly replaced;
* Fix: WooCommerce product variations were broken if srcset was present, but false;
* Fix: in certain cases, background images with important CSS priority weren't properly handled;
* Fix: also remove the sizes attribute if we remove the srcset;
* Fix: replacement error when html attribute contains "<style>.." data;
* Fix: various small fixes to settings, fonts, debug messages, ShortPixel account login and lazy loading;
* Language: 7 new strings added, 2 updated, 0 fuzzed, and 3 obsoleted.

= EARLIER VERSIONS =
* please refer to the <a href="https://plugins.svn.wordpress.org/shortpixel-adaptive-images/trunk/changelog.txt" target="_blank">changelog.txt</a> file inside the plugin archive.

== Upgrade Notice ==

= 2.0 =
ShortPixel Adaptive Images version 2.0 is a major upgrade and it comes with some new tools that help you manage the settings and the optimized images. Please note that following this upgrade, the plugin settings will be stored in a different way, therefore please also make a full site backup, update your theme and extensions, and review update best practices before upgrading.
= 2.0:END =

= 2.1 =
ShortPixel Adaptive Images version 2.1 is a major upgrade and it comes with some new tools that help you manage the settings and the optimized images. Please note that following version 2.0, the plugin settings will be stored in a different way, therefore please also make a full site backup, update your theme and extensions, and review update best practices before upgrading.
= 2.1:END =

= 3.0.0 =
SPAI 3 is a major update and it comes with a completely new Adaptive Images engine based on pure JS (no jQuery required anymore). Enable it from the Behavior tab in Settings -> ShortPIxel AI.
= 3.0.0:END = 
