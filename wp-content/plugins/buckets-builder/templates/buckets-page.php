<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$products = get_option( 'buckets_products', [] );
?>
<div id="bucket-builder" class="bb-container">

    <h2 class="bb-title">FLOWER BOUQUET CONFIGURATOR</h2>

    <!-- Tabs / progress -->
    <div class="bb-tabs">
        <div class="bb-tab bb-tab-1 active" data-step="1">
            <span class="bb-tab-label">The choice of flowers and greenery</span>
        </div>
        <div class="bb-tab bb-tab-2" data-step="2">
            <span class="bb-tab-label">Total</span>
        </div>
    </div>

    <?php if ( empty( $products ) ) : ?>
        <p>No products selected in admin. Please choose products in Buckets settings.</p>
    <?php else : ?>

        <div class="bb-steps">

            <!-- Step 1 -->
            <div class="bb-step bb-step-1 active">
                <div class="bb-products">
                    <?php foreach ( $products as $pid ) :
                        $product = wc_get_product( $pid );
                        if ( ! $product ) continue;
                        $price = $product->get_price();
                        $image_url = get_the_post_thumbnail_url( $product->get_id() );
                        $image_name = basename( $image_url );
                        ?>
                        <div class="bb-item" data-id="<?php echo esc_attr( $pid ); ?>" data-price="<?php echo esc_attr( $price ); ?>" data-flower="<?php echo $image_name; ?>">
                            <div class="bb-thumb"><?php echo $product->get_image( 'medium' ); ?></div>
                            <div class="bb-info">
                                <h4 class="bb-name"><?php echo esc_html( $product->get_name() ); ?></h4>
                                <div class="bb-price"><?php echo wp_kses_post( wc_price( $price ) ); ?></div>
                                <div class="bb-qty">
                                    <button class="bb-minus" type="button">-</button>
                                    <input class="bb-qty-input" type="number" value="0" min="0" readonly>
                                    <button class="bb-plus" type="button">+</button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="bb-summary-row">
<!--                    <div class="bb-summary-total">Total: <span id="bucket-total">0</span></div>-->
                    <div class="bb-summary-box">
                        <div id="bb-vase-preview">
                            <img src="/wp-content/plugins/buckets-builder/assets/img/vase.png" alt="Vase" class="vase-img" />
                            <div id="bb-flowers-in-vase"></div>
                        </div>
                        <div class="bb-total-wrapper">
                            <strong>Total:</strong>
                            <span id="bucket-total">$0.00</span>
                        </div>
                    </div>
                    <div>
                        <button id="bucket-next" class="bb-btn bb-btn-next">Next</button>
                    </div>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="bb-step bb-step-2">
                <div class="bb-review">
                    <table class="bb-review-table">
                        <thead>
                        <tr>
                            <th>flowers and greenery</th>
                            <th>qty</th>
                            <th>price</th>
                        </tr>
                        </thead>
                        <tbody id="bucket-list"></tbody>
                    </table>

                    <div class="bb-service-note">
                        <p><em>* Service charge includes the cost of delivery, assembly of a bouquet and packaging.</em></p>
                    </div>

                    <div class="bb-final">
                        <div class="bb-final-total">Total <span id="bucket-total-final">0</span></div>
                        <div class="bb-actions">
                            <button id="bucket-back" class="bb-btn bb-btn-back">BACK</button>
                            <button id="bucket-add-to-cart" class="bb-btn bb-btn-send">ADD TO CART</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    <?php endif; ?>
</div>
