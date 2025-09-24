<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$categories = [
    'flowers' => 'flowers for bucket',
    'leaves'  => 'leaves for bucket'
];

$products_by_category = [];

foreach ( $categories as $key => $cat_name ) {
    $args = [
        'post_type'      => 'product',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
        'tax_query'      => [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'name',
                'terms'    => $cat_name,
            ]
        ],
    ];

    $loop = new WP_Query( $args );
    $products_by_category[ $key ] = $loop->posts;
    wp_reset_postdata();
}
?>
<div class="buckets-container">
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

        <?php if ( empty( $products_by_category ) ) : ?>
            <p>No products selected in admin. Please choose products in Buckets settings.</p>
        <?php else : ?>

            <div class="bb-steps">

                <!-- Step 1 -->
                <div class="bb-step bb-step-1 active">
                    <div class="bb-subtabs">
                        <button class="bb-subtab active" data-sub="flowers">Flowers</button>
                        <button class="bb-subtab" data-sub="leaves">Leaves</button>
                    </div>

                    <div class="bb-subcontent bb-subcontent-flowers active">
                        <div class="bb-products">
                            <?php foreach ( $products_by_category['flowers'] as $product ) :
                                $pid = $product->ID;
                                $wc_product = wc_get_product( $pid );
                                if ( ! $wc_product ) continue;
                                $price = $wc_product->get_price();
                                $image_url = get_the_post_thumbnail_url( $pid, 'medium' );
                                ?>
                                <div class="bb-item flowers-items" data-id="<?= esc_attr( $pid ) ?>" data-price="<?= esc_attr( $price ) ?>" data-flower="<?= $image_url ?>">
                                    <div class="bb-thumb"><?= $wc_product->get_image( 'medium' ) ?></div>
                                    <div class="bb-info">
                                        <h4 class="bb-name"><?= esc_html( $wc_product->get_name() ) ?></h4>
                                        <div class="bb-price"><?= wp_kses_post( wc_price( $price ) ) ?></div>
                                        <div class="bb-qty">
                                            <button class="bb-minus" type="button">-</button>
                                            <input class="bb-qty-input" type="number" value="0" min="0" readonly>
                                            <button class="bb-plus" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bb-subcontent bb-subcontent-leaves">
                        <div class="bb-products">
                            <?php foreach ( $products_by_category['leaves'] as $product ) :
                                $pid = $product->ID;
                                $wc_product = wc_get_product( $pid );
                                if ( ! $wc_product ) continue;
                                $price = $wc_product->get_price();
                                $image_url = get_the_post_thumbnail_url( $pid, 'medium' );
                                ?>
                                <div class="bb-item leaves-items" data-id="<?= esc_attr( $pid ) ?>" data-price="<?= esc_attr( $price ) ?>" data-flower="<?= $image_url ?>">
                                    <div class="bb-thumb"><?= $wc_product->get_image( 'medium' ) ?></div>
                                    <div class="bb-info">
                                        <h4 class="bb-name"><?= esc_html( $wc_product->get_name() ) ?></h4>
                                        <div class="bb-price"><?= wp_kses_post( wc_price( $price ) ) ?></div>
                                        <div class="bb-qty">
                                            <button class="bb-minus" type="button">-</button>
                                            <input class="bb-qty-input" type="number" value="0" min="0" readonly>
                                            <button class="bb-plus" type="button">+</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="bb-btn-next-continer">
                        <button id="bucket-next" class="bb-btn bb-btn-next">Next</button>
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
    <div class="bb-summary-row">
        <div class="bb-summary-box">
            <div id="bb-vase-preview">
                <img src="/wp-content/plugins/buckets-builder/assets/img/vase.png" alt="Vase" class="vase-img" />
                <div id="bb-flowers-in-vase"></div>
            </div>
            <div class="bb-total-wrapper">
                <strong>Total:</strong>
                <span id="bucket-total">$0.00</span>
            </div>
            <div class="bb-clear-all">
                <a class="bb-clear" style="cursor: pointer"><span>clear</span></a>
            </div>
        </div>
    </div>
</div>
