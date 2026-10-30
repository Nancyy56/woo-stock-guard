<?php
/**
 * Store look & feel for the local demo shop (Twenty Twenty-Five + WooCommerce blocks).
 */

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('store-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_register_style('store-design', false, [], '1.0');
    wp_enqueue_style('store-design');
    wp_add_inline_style('store-design', store_design_css());
}, 20);

// Slim announcement bar above the header.
add_action('wp_body_open', function () {
    echo '<div class="store-announcement">Free shipping on orders over $75 &nbsp;·&nbsp; 30-day free returns</div>';
});

function store_design_css() {
    return <<<'CSS'
:root {
  --s-ink: #111827;
  --s-muted: #6b7280;
  --s-line: #e7e5e4;
  --s-bg: #f7f6f3;
  --s-card: #ffffff;
  --s-accent: #1f3a33;
  --s-accent-hover: #2c5247;
  --s-sale: #b4532a;
  --s-radius: 14px;
}
body {
  background: var(--s-bg);
  color: var(--s-ink);
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  -webkit-font-smoothing: antialiased;
}
body, body * { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
h1, h2, h3, h4 { letter-spacing: -0.02em; color: var(--s-ink); }

/* Announcement + header */
.store-announcement {
  background: var(--s-accent); color: #fff; text-align: center;
  font-size: 13px; letter-spacing: .02em; padding: 9px 16px;
}
header.wp-block-template-part {
  background: rgba(255,255,255,.92); backdrop-filter: saturate(180%) blur(10px);
  border-bottom: 1px solid var(--s-line); position: sticky; top: 0; z-index: 50;
}
.store-header .wp-block-site-title { font-size: 22px; font-weight: 700; letter-spacing: -0.03em; }
.store-header .wp-block-site-title a { text-decoration: none; color: var(--s-ink); }
.store-header .wp-block-navigation { font-size: 15px; font-weight: 500; }
.store-header .wp-block-navigation a { text-decoration: none; color: var(--s-ink); }
.store-header .wp-block-navigation a:hover { color: var(--s-accent-hover); }
.store-header .wc-block-mini-cart__button { color: var(--s-ink); }

/* Buttons */
.wp-block-button__link, .wc-block-components-button:not(.is-link), button.single_add_to_cart_button {
  background: var(--s-accent) !important; color: #fff !important; border: 0 !important;
  border-radius: 999px !important; font-weight: 600 !important; font-size: 14px !important;
  padding: 12px 22px !important; transition: background .2s ease, transform .2s ease;
  text-decoration: none !important;
}
.wp-block-button__link:hover, .wc-block-components-button:not(.is-link):hover { background: var(--s-accent-hover) !important; transform: translateY(-1px); }
.is-style-outline .wp-block-button__link { background: transparent !important; color: #fff !important; box-shadow: inset 0 0 0 1.5px #fff; }

/* Shop banner */
.store-shop-banner { border-radius: 20px; overflow: hidden; }
.store-shop-banner h1 { color: #fff; }

/* Product grid cards */
.wc-block-product-template { gap: 28px !important; grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
.wc-block-product-template > li.wc-block-product {
  background: var(--s-card); border: 1px solid var(--s-line); border-radius: var(--s-radius);
  padding: 0 0 20px !important; overflow: hidden; text-align: left;
  transition: box-shadow .25s ease, transform .25s ease;
  display: flex; flex-direction: column;
}
.wc-block-product-template > li.wc-block-product:hover { transform: translateY(-4px); box-shadow: 0 18px 40px -18px rgba(17,24,39,.25); }
.wc-block-product-template .wc-block-components-product-image { margin: 0 0 16px !important; border-radius: 0; overflow: hidden; background: #f1f0ed; }
.wc-block-product-template .wc-block-components-product-image img {
  aspect-ratio: 1 / 1; object-fit: cover; width: 100%; height: auto; transition: transform .5s ease;
}
.wc-block-product-template li:hover .wc-block-components-product-image img { transform: scale(1.04); }
.wc-block-product-template .wp-block-post-terms { padding: 0 20px; margin: 0 0 4px; font-size: 12px; text-transform: uppercase; letter-spacing: .08em; color: var(--s-muted); }
.wc-block-product-template .wp-block-post-terms a { color: var(--s-muted); text-decoration: none; }
.wc-block-product-template .wp-block-post-title { padding: 0 20px; margin: 0 0 6px !important; font-size: 17px !important; font-weight: 600; text-align: left !important; }
.wc-block-product-template .wp-block-post-title a { text-decoration: none; color: var(--s-ink); }
.wc-block-product-template .wc-block-components-product-price { padding: 0 20px; margin: 0 0 16px !important; font-size: 15px !important; font-weight: 600; text-align: left !important; }
.wc-block-components-product-price del { color: var(--s-muted); font-weight: 400; margin-right: 6px; }
.wc-block-components-product-price ins { text-decoration: none; color: var(--s-sale); }
.wc-block-product-template .wc-block-components-product-button { padding: 0 20px; margin: auto 0 0 !important; align-items: stretch !important; }
.wc-block-product-template .wc-block-components-product-button .wp-block-button__link { width: 100%; text-align: center; }
.wc-block-components-product-sale-badge {
  background: var(--s-sale) !important; color: #fff !important; border: 0 !important;
  border-radius: 999px !important; font-size: 12px !important; font-weight: 600; padding: 4px 12px !important;
  margin: 14px !important; text-transform: uppercase; letter-spacing: .04em;
}

/* Toolbar */
.woocommerce-result-count, .wc-block-product-results-count { color: var(--s-muted); font-size: 14px; }
.woocommerce-ordering select, .wc-block-catalog-sorting select {
  border: 1px solid var(--s-line); border-radius: 999px; padding: 8px 14px; background: #fff; font-size: 14px;
}
.wc-block-breadcrumbs, .woocommerce-breadcrumb { font-size: 13px; color: var(--s-muted); }
.wc-block-breadcrumbs a, .woocommerce-breadcrumb a { color: var(--s-muted); }

/* Value props */
.store-values .wp-block-column { background: #fff; border: 1px solid var(--s-line); border-radius: var(--s-radius); padding: 28px; }
.store-values h3 { font-size: 17px; font-weight: 600; margin: 0 0 6px; }
main a[href$="/shop/"]:not(.wp-block-button__link) { font-size: 15px; font-weight: 600; color: var(--s-accent); text-decoration: none; }
main a[href$="/shop/"]:not(.wp-block-button__link):hover { text-decoration: underline; }
.store-values p { color: var(--s-muted); font-size: 14px; margin: 0; }

/* Single product */
.single-product .wp-block-woocommerce-product-image-gallery img,
.single-product .woocommerce-product-gallery img { border-radius: var(--s-radius); }
.single-product .wp-block-post-title { font-size: clamp(28px, 4vw, 40px); font-weight: 700; }

/* Footer */
footer.wp-block-template-part { background: #111827; color: #d1d5db; margin-top: 80px; }
footer.wp-block-template-part a { color: #d1d5db; text-decoration: none; }
footer.wp-block-template-part a:hover { color: #fff; }
footer .store-footer-title { color: #fff; font-weight: 700; font-size: 20px; }
footer .store-footer-heading { color: #fff; font-size: 13px; text-transform: uppercase; letter-spacing: .1em; font-weight: 600; }
footer .store-footer-small { color: #9ca3af; font-size: 13px; }
footer p { font-size: 14px; }

@media (max-width: 1000px) {
  .wc-block-product-template { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
}
@media (max-width: 600px) {
  .wc-block-product-template { gap: 16px !important; grid-template-columns: 1fr !important; }
  .store-shop-banner { padding-left: 24px !important; padding-right: 24px !important; }
}
CSS;
}
