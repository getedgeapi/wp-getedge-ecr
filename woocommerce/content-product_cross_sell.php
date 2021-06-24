<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce/Templates
 * @version 3.4.0
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
	return;
}
?>
<li <?php wc_product_class(); ?> style="width: 100%;">

	<h3 style="margin: 0"><?php echo $product->name; ?></h3>
	<div class="price">
		<?php echo wc_price($product->price);
		if (isset($product->regular_price) && $product->regular_price != $product->price) {
			echo ' <small>reduced from <del>' . wc_price($product->regular_price) . '</del></small>';
		} ?>
	</div>
	<div style="text-align: left"><small><a href="/cart/?add-to-cart=<?php echo $product->id; ?>" class="checkout-button button alt wc-forward">
				Add <?php echo $product->name; ?></a></small></div>
	<hr style="margin: 20px 0;">

</li>