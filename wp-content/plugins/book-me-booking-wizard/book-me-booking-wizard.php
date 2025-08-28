<?php
/**
 * Plugin Name: BookMe Booking Wizard
 * Description: Single-file plugin that provides a vendor-specific, ajax-powered multi-step booking wizard (select staff -> select service -> select time -> checkout). Designed to work with WCFM + WooCommerce Bookings. Read the comments and "Installation / Configuration" section below.
 * Version: 1.0
 * Author: ChatGPT (generated)
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class BookMe_Booking_Wizard {
    public function __construct() {
        add_shortcode( 'bookme_wizard', array( $this, 'shortcode' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
        add_action( 'wp_ajax_bookme_get_product_fragment', array( $this, 'ajax_get_product_fragment' ) );
        add_action( 'wp_ajax_nopriv_bookme_get_product_fragment', array( $this, 'ajax_get_product_fragment' ) );
    }

    public function enqueue_assets() {
        wp_register_script( 'bookme-wizard-js', plugins_url( 'bookme-wizard.js', __FILE__ ), array( 'jquery' ), '1.0', true );
        // we'll inline the JS below if the file isn't present to keep single-file plugin behavior
        wp_register_style( 'bookme-wizard-css', false );
        wp_enqueue_style( 'bookme-wizard-css' );
    }

    public function shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'vendor' => '', // vendor slug or id
        ), $atts, 'bookme_wizard' );

        // Basic container markup - navigation will be handled by JS
        ob_start();
        ?>
        <div id="bookme-wizard" data-vendor="<?php echo esc_attr( $atts['vendor'] ); ?>">
            <nav id="bookme-steps">
                <button data-step="master" class="bookme-step active">1. Select Staff</button>
                <button data-step="service" class="bookme-step" disabled>2. Select Service</button>
                <button data-step="time" class="bookme-step" disabled>3. Select Time</button>
                <button data-step="checkout" class="bookme-step" disabled>4. Checkout</button>
            </nav>

            <div id="bookme-stage">
                <div class="bookme-panel" data-stage="master">Loading staff...</div>
                <div class="bookme-panel" data-stage="service" style="display:none">Choose a service...</div>
                <div class="bookme-panel" data-stage="time" style="display:none">Loading availability...</div>
                <div class="bookme-panel" data-stage="checkout" style="display:none">Redirecting to checkout...</div>
            </div>

            <div id="bookme-errors" style="color:#b00;margin-top:10px"></div>
        </div>
        <?php

        // Inline minimal JS so plugin remains single-file. In production you can move this to a separate .js file.
        $nonce = wp_create_nonce( 'wp_rest' );
        $rest_base = esc_url_raw( rest_url( '/bookme/v1/' ) );
        ?>
        <script>
        (function(){
            const root = document.getElementById('bookme-wizard');
            if(!root) return;
            const vendor = root.dataset.vendor || '';
            const restBase = '<?php echo $rest_base; ?>';
            const wpnonce = '<?php echo $nonce; ?>';

            let state = {
                vendor: vendor,
                staff: null,
                service: null,
                product_id: null,
                booking_payload: null
            };

            const panels = {};
            document.querySelectorAll('#bookme-wizard .bookme-panel').forEach(el=>{ panels[el.dataset.stage]=el; });

            function setStage(stage){
                document.querySelectorAll('#bookme-wizard .bookme-panel').forEach(el=>el.style.display='none');
                panels[stage].style.display='block';
                document.querySelectorAll('#bookme-steps .bookme-step').forEach(b=>{
                    b.classList.toggle('active', b.dataset.step===stage);
                    b.disabled = (['master','service','time','checkout'].indexOf(b.dataset.step) > ['master','service','time','checkout'].indexOf(stage));
                });
                const url = '/company/'+encodeURIComponent(state.vendor)+'/personal/select-'+(stage==='master'?'master': stage==='service'?'service': stage==='time'?'time':'checkout');
                history.replaceState(state, '', url);
            }

            function showError(msg){ document.getElementById('bookme-errors').textContent = msg; }
            function clearError(){ document.getElementById('bookme-errors').textContent = ''; }

            // Step 1: load staff
            async function loadStaff(){
                setStage('master');
                panels.master.innerHTML = '<strong>Loading staff...</strong>';
                try{
                    const res = await fetch(restBase + 'staff?vendor=' + encodeURIComponent(state.vendor), {headers:{'X-WP-Nonce': wpnonce}});
                    if(!res.ok) throw new Error('Failed to fetch staff');
                    const data = await res.json();
                    state.staff = data;
                    renderStaffList(data);
                }catch(e){ showError(e.message); panels.master.innerHTML = 'Error loading staff.' }
            }

            function renderStaffList(list){
                if(!Array.isArray(list) || list.length===0){ panels.master.innerHTML = '<em>No staff found. Please ask vendor to add staff.</em>'; return; }
                const ul = document.createElement('div'); ul.className='bookme-staff-list';
                list.forEach(s=>{
                    const btn = document.createElement('button');
                    btn.type='button'; btn.className='bookme-staff';
                    btn.textContent = s.display_name || s.name || s.title;
                    btn.dataset.staffId = s.id;
                    btn.addEventListener('click', ()=>{ selectStaff(s); });
                    ul.appendChild(btn);
                });
                panels.master.innerHTML = '<h3>Select a staff</h3>';
                panels.master.appendChild(ul);
            }

            // Step 2: after selecting staff, load services
            async function selectStaff(s){
                clearError();
                state.selected_staff = s;
                setStage('service');
                panels.service.innerHTML = '<strong>Loading services...</strong>';
                try{
                    const res = await fetch(restBase + 'services?vendor=' + encodeURIComponent(state.vendor) + '&staff_id=' + encodeURIComponent(s.id), {headers:{'X-WP-Nonce': wpnonce}});
                    if(!res.ok) throw new Error('Failed to fetch services');
                    const services = await res.json();
                    renderServicesList(services);
                }catch(e){ showError(e.message); panels.service.innerHTML = 'Error loading services.' }
            }

            function renderServicesList(list){
                if(!Array.isArray(list) || list.length===0){ panels.service.innerHTML = '<em>No services found for selected staff.</em>'; return; }
                panels.service.innerHTML = '<h3>Select a service</h3>';
                const container = document.createElement('div');
                list.forEach(p=>{
                    const b = document.createElement('button');
                    b.type='button'; b.textContent = p.title + (p.price?(' — '+p.price):'');
                    b.dataset.productId = p.id;
                    b.addEventListener('click', ()=>{ selectService(p); });
                    container.appendChild(b);
                });
                panels.service.appendChild(container);
            }

            // Step 3: load native booking form for this product and inject into panel
            async function selectService(p){
                clearError();
                state.selected_service = p;
                setStage('time');
                panels.time.innerHTML = '<strong>Loading calendar...</strong>';
                try{
                    // Ask server to return product's booking form HTML fragment
                    const formRes = await fetch(ajaxurlBase() + '?action=bookme_get_product_fragment&product_id='+encodeURIComponent(p.id), {credentials:'same-origin'});
                    if(!formRes.ok) throw new Error('Failed to load booking widget');
                    const html = await formRes.text();
                    // Insert HTML (it contains native booking calendar + form). We expect the form to POST to WC add-to-cart endpoint which will hold the slot in cart.
                    panels.time.innerHTML = html;

                    // If the product calendar uses JS that needs init, we preserve (many bookings scripts are inline). If needed, re-run scripts: but for simplicity we assume calendar JS runs when inserted.

                    // Attach listener to add-to-cart button inside injected content and intercept to push step to checkout after add
                    const addBtn = panels.time.querySelector('button.single_add_to_cart_button');
                    if(addBtn){
                        addBtn.addEventListener('click', (ev)=>{
                            // small delay to let WC AJAX add to cart run; then navigate to checkout
                            setTimeout(()=>{
                                setStage('checkout');
                                // go to checkout page
                                window.location = wc_checkout_url();
                            }, 800);
                        });
                    }

                }catch(e){ showError(e.message); panels.time.innerHTML = 'Error loading native booking form.' }
            }

            // Helpers
            function wc_checkout_url(){ return (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.checkout_url) ? wc_checkout_params.checkout_url : ('/checkout/'); }
            function ajaxurlBase(){ return '<?php echo admin_url('admin-ajax.php'); ?>'; }

            // init
            loadStaff();

            // handle back/forward
            window.addEventListener('popstate', function(e){ if(e.state && e.state.selected_service){ state = e.state; selectService(state.selected_service); } else if(e.state && e.state.selected_staff){ state = e.state; selectStaff(state.selected_staff); } else { loadStaff(); } });

        })();
        </script>
        <style>
        #bookme-wizard{border:1px solid #eee;padding:16px;border-radius:8px;background:#fff}
        #bookme-steps{display:flex;gap:8px;margin-bottom:12px}
        .bookme-step{padding:6px 10px;border-radius:6px;border:1px solid #ddd;background:#fafafa}
        .bookme-step.active{background:#e6f7ff;border-color:#91d5ff}
        .bookme-staff, .bookme-services button{display:inline-block;margin:6px;padding:10px;border-radius:6px}
        </style>
        <?php

        return ob_get_clean();
    }

    public function register_rest_routes(){
        register_rest_route( 'bookme/v1', '/staff', array(
            'methods' => 'GET',
            'callback' => array( $this, 'rest_get_staff' ),
            'permission_callback' => '__return_true',
        ) );

        register_rest_route( 'bookme/v1', '/services', array(
            'methods' => 'GET',
            'callback' => array( $this, 'rest_get_services' ),
            'permission_callback' => '__return_true',
        ) );
    }

    /**
     * REST: return staff for a vendor.
     * This function tries to be flexible: it supports multiple ways WCFM may store staff.
     * If your WCFM uses a custom post type for staff (common), that will be used.
     * Otherwise it will look for users with role 'vendor_staff' or users having meta 'wcfm_vendor' equal to vendor id.
     */
    public function rest_get_staff( WP_REST_Request $req ){
        $vendor = $req->get_param('vendor');
        if ( ! $vendor ) return new WP_REST_Response( array(), 200 );

        $vendor_id = $this->resolve_vendor_id( $vendor );
        if ( ! $vendor_id ) return new WP_REST_Response( array(), 200 );

        $results = array();

        // 1) If CPT 'wcfm_staff' exists - query it
        if ( post_type_exists( 'wcfm_staff' ) ){
            $q = new WP_Query(array('post_type'=>'wcfm_staff','posts_per_page'=>-1,'meta_query'=>array(array('key'=>'vendor_id','value'=>$vendor_id))));
            foreach($q->posts as $p){ $results[] = array('id'=>$p->ID, 'title'=>get_the_title($p->ID)); }
            return rest_ensure_response($results);
        }

        // 2) fallback: users with role 'vendor_staff' or 'shop_manager' with meta linking to vendor
        $args = array('role__in'=>array('vendor_staff','shop_staff','shop_manager','wcfm_vendor_staff'),'number'=>-1);
        $users = get_users($args);
        foreach($users as $u){
            $ok = false;
            // check common wcfm usermeta keys that may store vendor id
            $meta_keys = array('wcfm_vendor','vendor_id','store_id','wcfm_owner');
            foreach($meta_keys as $mk){
                if(get_user_meta($u->ID,$mk,true) && (string) get_user_meta($u->ID,$mk,true) === (string)$vendor_id){ $ok = true; break; }
            }
            if($ok) $results[] = array('id'=>$u->ID, 'display_name'=>$u->display_name, 'email'=>$u->user_email);
        }

        return rest_ensure_response($results);
    }

    protected function resolve_vendor_id( $vendor ){
        // vendor can be slug or numeric id
        if ( is_numeric( $vendor ) ) return intval( $vendor );
        // try to find vendor user by nicename/slug
        $user = get_user_by( 'slug', $vendor );
        if ( $user ) return $user->ID;
        // try by nicename via user_nicename
        $u = get_users(array('search'=>$vendor,'search_columns'=>array('user_login','user_nicename'),'number'=>1));
        if(!empty($u)) return $u[0]->ID;
        return false;
    }

    /**
     * REST: return bookable services for vendor, optionally filtered by staff id.
     * We search for 'product' post_type authored by vendor_id and of type 'booking'.
     * To connect a product (service) to staff, we rely on product meta 'bookme_assigned_staff' (CSV or array of user IDs).
     * You must create this meta for each service (via WCFM product edit custom field). See plugin README below for steps.
     */
    public function rest_get_services( WP_REST_Request $req ){
        $vendor = $req->get_param('vendor');
        $staff_id = $req->get_param('staff_id');
        if ( ! $vendor ) return rest_ensure_response(array());
        $vendor_id = $this->resolve_vendor_id( $vendor );
        if ( ! $vendor_id ) return rest_ensure_response(array());

        $args = array(
            'post_type' => 'product',
            'post_status' => 'publish',
            'author' => $vendor_id,
            'posts_per_page' => -1,
            'meta_query' => array(
                array('key' => '_virtual','compare' => 'EXISTS') // superficial placeholder so meta_query exists if needed
            )
        );

        $q = new WP_Query( $args );
        $out = array();
        foreach( $q->posts as $p ){
            $prod = wc_get_product( $p->ID );
            if( ! $prod ) continue;
            if( $prod->get_type() !== 'booking' ) continue; // only bookable products

            // Check mapping to staff
            $assigned = get_post_meta( $p->ID, 'bookme_assigned_staff', true );
            if( $assigned ){
                if( is_string($assigned) ) $assigned = array_map('trim', explode(',', $assigned));
                if( !empty($staff_id) && !in_array( (string)$staff_id, array_map('strval',$assigned), true ) ){
                    continue; // not assigned to selected staff
                }
            } else {
                // if no mapping exists, assume available to all staff
            }

            $out[] = array('id'=>$p->ID, 'title'=>get_the_title($p->ID), 'price'=> wc_price( $prod->get_price() ) );
        }

        return rest_ensure_response( $out );
    }

    /**
     * Ajax: return product's booking form fragment (native booking calendar + add-to-cart form):
     * This uses the product global and loads the single-product template part for bookings.
     * NOTE: depending on your theme and booking template structure this may need adjustments.
     */
    public function ajax_get_product_fragment(){
        if( empty($_GET['product_id']) ) { wp_die('Missing'); }
        $pid = intval( $_GET['product_id'] );
        $product = wc_get_product( $pid );
        if( ! $product ) { wp_die('Invalid product'); }

        // We will render the `single-product/add-to-cart/booking.php` template part if exists.
        // Use output buffering to capture HTML.
        ob_start();

        // Temporarily set global post/product
        global $post, $product;
        $post = get_post( $pid ); setup_postdata( $post );
        $product = wc_get_product( $pid );

        // Attempt to load the booking form template. This mirrors the single product behavior.
        // We call the action that usually outputs content for bookable product form.
        // Many themes print more markup; the goal here is to return the core booking form.

        // The bookings plugin uses template located at 'single-product/add-to-cart/booking.php'
        wc_get_template( 'single-product/add-to-cart/booking.php', array( 'product' => $product ) );

        wp_reset_postdata();
        $html = ob_get_clean();

        echo $html;
        wp_die();
    }
}

new BookMe_Booking_Wizard();

/**
 * === Installation / Configuration (important) ===
 * 1) Upload this file as `bookme-wizard.php` into a folder `/wp-content/plugins/bookme-wizard/` and activate it.
 * 2) Place the shortcode on a page that will act as your wizard root. Example content:
 *     [bookme_wizard vendor="anahitbeauty"]
 *    Replace vendor value with vendor's user nicename (slug) or numeric ID. You can create one page and pass vendor dynamically via URL rewrite or use many pages.
 *
 * 3) Mapping Services -> Staff (required):
 *    WCFM does not expose the "assigned staff" product meta to our wizard automatically in every installation. To make services show only for assigned staff you must set (per service) the product meta key `bookme_assigned_staff` which should be a comma-separated list of staff user IDs. Example: `123,124`
 *    How to set `bookme_assigned_staff`:
 *      - Option A (recommended, non-code): Use WCFM's product custom fields feature or Toolset/ACF to add a product meta field (multi-select) that stores staff IDs. Configure vendors to choose staff when editing the bookable product.
 *      - Option B (quick manual): Edit the product in the admin and use "Custom Fields" (enable from Screen Options) and add a key `bookme_assigned_staff` with value `123,124`.
 *
 * 4) Native booking calendar:
 *    The plugin injects the native booking product form into the wizard (so the booking availability, rules and in-cart holding are preserved by WooCommerce Bookings). The code uses wc_get_template('single-product/add-to-cart/booking.php') to get the form. Many themes override templates and some heavy customizations might require adjusting the ajax_get_product_fragment handler.
 *
 * 5) Checkout:
 *    The plugin relies on the normal WooCommerce add-to-cart behavior from the injected booking form. After the user clicks "Book now" (add to cart) we capture that and redirect user to the checkout page. This keeps the native workflow, payments and booking 'in-cart' behavior intact.
 *
 * 6) Vendor pages & URLs:
 *    For friendly URLs like /company/{vendor}/personal/select-master you can create a page and use the shortcode, then create rewrite rules (or rely on the JS history.replaceState that we already use). If you want a per-vendor page URL such as /company/anahitbeauty/personal/select-master create one WP Page per vendor or add rewrite rules in your theme that set the vendor slug as a query var and then render the shortcode with vendor attribute.
 *
 * 7) Notes & Troubleshooting:
 *    - If the calendar doesn't display after injection, confirm your theme doesn't require additional JS initialization. Some themes enqueue scripts that run only on full page load. If so, you may need a developer to re-initialize the booking JS after insertion.
 *    - If services don't filter by staff, ensure `bookme_assigned_staff` meta is set on products.
 *    - If WooCommerce Bookings templates are heavily overridden, the path used in ajax_get_product_fragment may need to change to match your theme's structure.
 *
 * === Security & Performance ===
 * - REST endpoints are public read-only; they only serve vendor-owned public data. If you want to restrict access further, change the permission_callback to check capability or nonce.
 * - This plugin injects native product form HTML; for high-scale you should move the JS into a proper external file and cache REST responses.
 *
 * === Extending / Developer Handoff ===
 * If you want me (or your developer) to make the glue seamless (auto map WCFM staff to products from WCFM's internal meta, re-init booking JS after injection, or create rewrite endpoints that generate per-vendor pages automatically), I can provide the next version. Ask for: "Make wizard auto-detect WCFM staff & map to products".
 */
