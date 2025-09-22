<?php
$products = get_option('buckets_products', []);
if (!$products) {
    echo "<p>No products selected in admin.</p>";
    return;
}
?>
<div id="bucket-builder">
    <div class="bucket-products">
        <?php foreach ($products as $pid):
            $product = wc_get_product($pid);
            if (!$product) continue;
            ?>
            <div class="bucket-item" data-id="<?php echo $pid; ?>" data-price="<?php echo $product->get_price(); ?>">
                <?php echo $product->get_image(); ?>
                <h4><?php echo $product->get_name(); ?></h4>
                <p><?php echo wc_price($product->get_price()); ?></p>
                <div class="qty-control">
                    <button class="minus">-</button>
                    <input type="number" value="0" min="0" readonly>
                    <button class="plus">+</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bucket-summary">
        <h3>Total: <span id="bucket-total">0</span></h3>
        <button id="bucket-next">Next</button>
        <div id="bucket-review" style="display:none;">
            <h4>Review Selection</h4>
            <ul id="bucket-list"></ul>
            <button id="bucket-add-to-cart">Add to Cart</button>
        </div>
    </div>
</div>
