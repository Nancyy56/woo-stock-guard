<?php
/**
 * One-off store setup: branding, header, footer, homepage and shop template.
 * Run: ./wp.sh eval-file - < setup-design.php
 */

$theme = get_stylesheet();
$hero  = wp_get_attachment_url(22);
$shop  = get_permalink(wc_get_page_id('shop'));
$acct  = get_permalink(wc_get_page_id('myaccount'));

update_option('blogname', 'Lumen Supply');
update_option('blogdescription', 'Everyday essentials, thoughtfully made.');

// Remove default demo content.
foreach (['sample-page' => 'page', 'hello-world' => 'post'] as $slug => $type) {
    $p = get_page_by_path($slug, OBJECT, $type);
    if ($p) wp_delete_post($p->ID, true);
}

function store_upsert($type, $slug, $title, $content, $theme, $area = null) {
    $existing = get_posts(['post_type' => $type, 'name' => $slug, 'post_status' => 'any', 'numberposts' => 1,
        'tax_query' => $type === 'page' ? [] : [['taxonomy' => 'wp_theme', 'field' => 'name', 'terms' => $theme]]]);
    $data = ['post_type' => $type, 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content, 'post_status' => 'publish'];
    if ($existing) { $data['ID'] = $existing[0]->ID; }
    $id = wp_insert_post(wp_slash($data), true);
    if (is_wp_error($id)) { WP_CLI::error($id); }
    if ($type !== 'page') wp_set_object_terms($id, $theme, 'wp_theme');
    if ($area) wp_set_object_terms($id, $area, 'wp_template_part_area');
    WP_CLI::log("$type/$slug => $id");
    return $id;
}

// Navigation menu.
$nav = get_posts(['post_type' => 'wp_navigation', 'numberposts' => 1, 'post_status' => 'publish']);
$nav_content = sprintf(
    '<!-- wp:navigation-link {"label":"Home","url":"%s","kind":"custom"} /-->' .
    '<!-- wp:navigation-link {"label":"Shop","url":"%s","kind":"custom"} /-->' .
    '<!-- wp:navigation-link {"label":"Tech","url":"%s","kind":"custom"} /-->' .
    '<!-- wp:navigation-link {"label":"Accessories","url":"%s","kind":"custom"} /-->' .
    '<!-- wp:navigation-link {"label":"Account","url":"%s","kind":"custom"} /-->',
    home_url('/'), $shop, get_term_link('tech', 'product_cat'), get_term_link('accessories', 'product_cat'), $acct
);
if ($nav) { wp_update_post(['ID' => $nav[0]->ID, 'post_content' => $nav_content]); $nav_id = $nav[0]->ID; }
else { $nav_id = wp_insert_post(['post_type' => 'wp_navigation', 'post_title' => 'Main', 'post_status' => 'publish', 'post_content' => $nav_content]); }

// Header.
store_upsert('wp_template_part', 'header', 'Header', <<<HTML
<!-- wp:group {"align":"full","className":"store-header","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull store-header">
<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"18px","bottom":"18px"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide" style="padding-top:18px;padding-bottom:18px">
<!-- wp:site-title {"level":0} /-->
<!-- wp:group {"style":{"spacing":{"blockGap":"28px"}},"layout":{"type":"flex","flexWrap":"nowrap"}} -->
<div class="wp-block-group">
<!-- wp:navigation {"ref":$nav_id,"overlayBackgroundColor":"base","overlayTextColor":"contrast","layout":{"type":"flex","justifyContent":"right"},"style":{"spacing":{"blockGap":"28px"}}} /-->
<!-- wp:woocommerce/mini-cart {"hasHiddenPrice":true} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
HTML, $theme, 'header');

// Footer.
$year = date('Y');
store_upsert('wp_template_part', 'footer', 'Footer', <<<HTML
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"40px"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull" style="padding-top:64px;padding-bottom:40px">
<!-- wp:columns {"align":"wide"} -->
<div class="wp-block-columns alignwide">
<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%">
<!-- wp:paragraph {"className":"store-footer-title"} --><p class="store-footer-title">Lumen Supply</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Everyday essentials, thoughtfully made. Designed to last, priced fairly and shipped fast.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"store-footer-heading"} --><p class="store-footer-heading">Shop</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p><a href="$shop">All products</a><br><a href="{$shop}?product_cat=tech">Tech</a><br><a href="{$shop}?product_cat=accessories">Accessories</a><br><a href="{$shop}?product_cat=apparel">Apparel</a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"store-footer-heading"} --><p class="store-footer-heading">Help</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>Shipping &amp; delivery<br>Returns &amp; exchanges<br>Order tracking<br><a href="$acct">My account</a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
<!-- wp:column -->
<div class="wp-block-column">
<!-- wp:paragraph {"className":"store-footer-heading"} --><p class="store-footer-heading">Contact</p><!-- /wp:paragraph -->
<!-- wp:paragraph --><p>hello@lumensupply.example<br>Mon–Fri, 9am–6pm</p><!-- /wp:paragraph -->
</div>
<!-- /wp:column -->
</div>
<!-- /wp:columns -->
<!-- wp:separator {"align":"wide","style":{"color":{"background":"#374151"}}} --><hr class="wp-block-separator alignwide has-text-color has-alpha-channel-opacity has-background" style="background-color:#374151;color:#374151"/><!-- /wp:separator -->
<!-- wp:paragraph {"align":"wide","className":"store-footer-small"} --><p class="alignwide store-footer-small">© $year Lumen Supply. Demo store for local testing.</p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
HTML, $theme, 'footer');

// Product grid markup reused on home and shop.
function store_product_grid($inherit, $per_page, $extra_query = '') {
    $inherit = $inherit ? 'true' : 'false';
    return <<<HTML
<!-- wp:woocommerce/product-collection {"queryId":1,"query":{"woocommerceAttributes":[],"woocommerceStockStatus":["instock","outofstock","onbackorder"],"taxQuery":{},"isProductCollectionBlock":true,"perPage":$per_page,"pages":0,"offset":0,"postType":"product","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":$inherit$extra_query},"tagName":"div","displayLayout":{"type":"flex","columns":3,"shrinkColumns":true},"queryContextIncludes":["collection"],"align":"wide"} -->
<div class="wp-block-woocommerce-product-collection alignwide">
<!-- wp:woocommerce/product-template -->
<!-- wp:woocommerce/product-image {"showSaleBadge":false,"imageSizing":"single","isDescendentOfQueryLoop":true} -->
<!-- wp:woocommerce/product-sale-badge {"isDescendentOfQueryLoop":true,"align":"left"} /-->
<!-- /wp:woocommerce/product-image -->
<!-- wp:post-terms {"term":"product_cat"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"__woocommerceNamespace":"woocommerce/product-collection/product-title"} /-->
<!-- wp:woocommerce/product-price {"isDescendentOfQueryLoop":true} /-->
<!-- wp:woocommerce/product-button {"isDescendentOfQueryLoop":true} /-->
<!-- /wp:woocommerce/product-template -->
<!-- wp:query-pagination {"layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next /-->
<!-- /wp:query-pagination -->
</div>
<!-- /wp:woocommerce/product-collection -->
HTML;
}

$values = <<<HTML
<!-- wp:columns {"align":"wide","className":"store-values","style":{"spacing":{"blockGap":{"left":"20px"}}}} -->
<div class="wp-block-columns alignwide store-values">
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Free shipping</h3><!-- /wp:heading --><!-- wp:paragraph --><p>On every order over \$75, delivered in 2–4 business days.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">30-day returns</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Not quite right? Send it back free within 30 days.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
<!-- wp:column --><div class="wp-block-column"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Secure checkout</h3><!-- /wp:heading --><!-- wp:paragraph --><p>Encrypted payments and real-time stock updates.</p><!-- /wp:paragraph --></div><!-- /wp:column -->
</div>
<!-- /wp:columns -->
HTML;

// Shop (product archive) template.
$grid = store_product_grid(true, 12);
store_upsert('wp_template', 'archive-product', 'Product Catalog', <<<HTML
<!-- wp:template-part {"slug":"header","tagName":"header"} /-->
<!-- wp:group {"tagName":"main","style":{"spacing":{"padding":{"top":"32px"}}},"layout":{"type":"constrained"}} -->
<main class="wp-block-group" style="padding-top:32px">
<!-- wp:cover {"url":"$hero","id":22,"dimRatio":55,"overlayColor":"contrast","minHeight":260,"align":"wide","className":"store-shop-banner","style":{"spacing":{"padding":{"left":"48px","right":"48px"}}}} -->
<div class="wp-block-cover alignwide store-shop-banner" style="padding-left:48px;padding-right:48px;min-height:260px"><span aria-hidden="true" class="wp-block-cover__background has-contrast-background-color has-background-dim-60 has-background-dim"></span><img class="wp-block-cover__image-background wp-image-22" alt="" src="$hero" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
<!-- wp:query-title {"type":"archive","showPrefix":false,"style":{"typography":{"fontSize":"44px","fontWeight":"700"}},"textColor":"base"} /-->
<!-- wp:paragraph {"textColor":"base"} --><p class="has-base-color has-text-color">Quality essentials for work, home and everything in between.</p><!-- /wp:paragraph -->
</div></div>
<!-- /wp:cover -->
<!-- wp:woocommerce/store-notices /-->
<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"32px","bottom":"8px"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between"}} -->
<div class="wp-block-group alignwide" style="margin-top:32px;margin-bottom:8px">
<!-- wp:woocommerce/product-results-count /-->
<!-- wp:woocommerce/catalog-sorting /-->
</div>
<!-- /wp:group -->
$grid
</main>
<!-- /wp:group -->
<!-- wp:template-part {"slug":"footer","tagName":"footer"} /-->
HTML, $theme);

// Category pages use the same layout.
store_upsert('wp_template', 'taxonomy-product_cat', 'Products by Category',
    get_posts(['post_type' => 'wp_template', 'name' => 'archive-product', 'numberposts' => 1])[0]->post_content, $theme);

// Homepage.
$home_grid = store_product_grid(false, 6);
$home_id = store_upsert('page', 'home', 'Home', <<<HTML
<!-- wp:cover {"url":"$hero","id":22,"dimRatio":50,"overlayColor":"contrast","minHeight":560,"align":"full","style":{"spacing":{"padding":{"left":"24px","right":"24px"}}}} -->
<div class="wp-block-cover alignfull" style="padding-left:24px;padding-right:24px;min-height:560px"><span aria-hidden="true" class="wp-block-cover__background has-contrast-background-color has-background-dim"></span><img class="wp-block-cover__image-background wp-image-22" alt="" src="$hero" data-object-fit="cover"/><div class="wp-block-cover__inner-container">
<!-- wp:group {"layout":{"type":"constrained","contentSize":"720px"}} -->
<div class="wp-block-group">
<!-- wp:paragraph {"align":"center","textColor":"base","style":{"typography":{"textTransform":"uppercase","letterSpacing":"0.18em","fontSize":"13px","fontWeight":"600"}}} --><p class="has-text-align-center has-base-color has-text-color" style="font-size:13px;font-weight:600;letter-spacing:0.18em;text-transform:uppercase">New season collection</p><!-- /wp:paragraph -->
<!-- wp:heading {"textAlign":"center","level":1,"textColor":"base","style":{"typography":{"fontSize":"clamp(40px, 6vw, 68px)","fontWeight":"700","lineHeight":"1.05"}}} --><h1 class="wp-block-heading has-text-align-center has-base-color has-text-color" style="font-size:clamp(40px, 6vw, 68px);font-weight:700;line-height:1.05">Everyday essentials, thoughtfully made.</h1><!-- /wp:heading -->
<!-- wp:paragraph {"align":"center","textColor":"base","style":{"typography":{"fontSize":"18px"}}} --><p class="has-text-align-center has-base-color has-text-color" style="font-size:18px">Tech, apparel and accessories designed to last — shipped free over \$75.</p><!-- /wp:paragraph -->
<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
<div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="$shop">Shop the collection</a></div><!-- /wp:button --></div>
<!-- /wp:buttons -->
</div>
<!-- /wp:group -->
</div></div>
<!-- /wp:cover -->
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"56px","left":"24px","right":"24px"}}},"layout":{"type":"constrained","contentSize":"1240px"}} -->
<div class="wp-block-group alignfull" style="padding-top:56px;padding-right:24px;padding-left:24px">
$values
<!-- wp:group {"align":"wide","style":{"spacing":{"margin":{"top":"64px","bottom":"8px"}}},"layout":{"type":"flex","flexWrap":"nowrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
<div class="wp-block-group alignwide" style="margin-top:64px;margin-bottom:8px">
<!-- wp:heading {"style":{"typography":{"fontSize":"34px","fontWeight":"700"}}} --><h2 class="wp-block-heading" style="font-size:34px;font-weight:700">Latest arrivals</h2><!-- /wp:heading -->
<!-- wp:paragraph --><p><a href="$shop">View all products →</a></p><!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
$home_grid
</div>
<!-- /wp:group -->
HTML, $theme);

update_option('show_on_front', 'page');
update_option('page_on_front', $home_id);
update_post_meta($home_id, '_wp_page_template', 'page-no-title');
WP_CLI::success('Store design applied.');
