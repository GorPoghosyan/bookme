jQuery(function($){
    // helper format based on passed currency settings
    function formatCurrency(value) {
        var sym = buckets_builder.currency_symbol || '$';
        var pos = buckets_builder.currency_pos || 'left';
        var num = Number(value).toFixed(2);
        if (pos === 'right') return num + ' ' + sym;
        return sym + ' ' + num;
    }

    function parseNumber(v){
        var n = parseFloat(v);
        return isNaN(n) ? 0 : n;
    }

    function updateTotalAndUI(){
        var total = 0;
        $(".bb-item").each(function(){
            var price = parseNumber($(this).data('price'));
            var qty = parseInt($(this).find('.bb-qty-input').val()) || 0;
            total += price * qty;
        });
        $("#bucket-total").text(formatCurrency(total));
        return total;
    }

    // plus/minus
    $(document).on('click', '.bb-plus', function(){
        var $input = $(this).siblings('.bb-qty-input');
        var v = parseInt($input.val()) || 0;
        $input.val(v + 1);
        updateTotalAndUI();
        updateFlowersInVase();
    });

    $(document).on('click', '.bb-minus', function(){
        var $input = $(this).siblings('.bb-qty-input');
        var v = parseInt($input.val()) || 0;
        if (v > 0) {
            $input.val(v - 1);
            updateFlowersInVase();
        }
        updateTotalAndUI();
    });

    $(document).on('click', '.bb-clear-all', function(){
        $("#bb-flowers-in-vase").empty();
        $(".bb-item").each(function(i) {
            $(this).find('.bb-qty-input').val(0);
        });
        updateTotalAndUI();
    });

    function updateFlowersInVase() {
        const container = $("#bb-flowers-in-vase").empty();
        const positions = []; // keep used positions

        $(".bb-item").each(function(i){
            const qty = parseInt($(this).find('.bb-qty-input').val()) || 0;
            const flowerName = $(this).data('flower');
            if (!flowerName) return;

            for (let j = 0; j < qty; j++) {
                // Avoid overlapping: generate random top/left not already used
                let top, left, attempts = 0;
                do {
                    top = Math.floor(10 - Math.random() * 60);
                    left = Math.floor(60 + Math.random() * 90);
                    attempts++;
                } while (positions.find(p => Math.abs(p.top - top) < 20 && Math.abs(p.left - left) < 20) && attempts < 20);

                positions.push({top, left});

                const img = $("<img>")
                    .attr("src", flowerName)
                    .addClass("bb-flower-in-vase")
                    .css({
                        top: top + "px",
                        left: left + "px",
                        transform: "rotate(" + Math.floor(Math.random() * 360) + "deg)",
                        zIndex: 10 + j
                    });
                container.append(img);
            }
        });
    }

    $(document).on('click', '.bb-subtab', function(){
        var sub = $(this).data('sub');
        $('.bb-subtab').removeClass('active');
        $(this).addClass('active');

        $('.bb-subcontent').removeClass('active');
        $('.bb-subcontent-' + sub).addClass('active');
    });

    // Tabs clickable
    // $(document).on('click', '.bb-tab', function(){
    //     var step = $(this).data('step');
    //     $('.bb-tab').removeClass('active');
    //     $(this).addClass('active');
    //     $('.bb-step').removeClass('active');
    //     $('.bb-step-' + step).addClass('active');
    // });

    // Next button -> fill review table and switch to step 2
    $('#bucket-next').on('click', function(e){
        e.preventDefault();
        var total = updateTotalAndUI();

        if (total === 0) {
            alert('Խնդրում ենք ընտրել առնվազն մեկ ապրանք։');
            return;
        }

        var $list = $('#bucket-list').empty();
        $(".bb-item").each(function(){
            var name = $(this).find('.bb-name').text();
            var qty = parseInt($(this).find('.bb-qty-input').val()) || 0;
            var price = parseNumber($(this).data('price'));
            if (qty > 0) {
                var subtotal = (price * qty).toFixed(2);
                $list.append('<tr><td>' + $('<div>').text(name).html() + '</td><td>' + qty + '</td><td>' + formatCurrency(subtotal) + '</td></tr>');
            }
        });
        $('#bucket-total-final').text(formatCurrency(total));
        // show step 2
        $('.bb-tab').removeClass('active');
        $('.bb-tab[data-step=2]').addClass('active');
        $('.bb-step').removeClass('active');
        $('.bb-step-2').addClass('active');
        // scroll to top of builder
        $('html,body').animate({ scrollTop: $('#bucket-builder').offset().top }, 300);
    });

    // Back
    $('#bucket-back').on('click', function(){
        $('.bb-tab').removeClass('active');
        $('.bb-tab[data-step=1]').addClass('active');
        $('.bb-step').removeClass('active');
        $('.bb-step-1').addClass('active');
        $('html,body').animate({ scrollTop: $('#bucket-builder').offset().top }, 300);
    });

    // Add to cart (send AJAX)
    $('#bucket-add-to-cart').on('click', function(){
        var items = [];
        $(".bb-item").each(function(){
            var id = $(this).data('id');
            var qty = parseInt($(this).find('.bb-qty-input').val()) || 0;
            if (qty > 0) items.push({ id: id, qty: qty });
        });

        if (items.length === 0) {
            alert('Please choose at least one product.');
            return;
        }

        var postData = {
            action: 'buckets_add_to_cart',
            items: JSON.stringify(items),
            nonce: buckets_builder.nonce
        };

        // disable button
        $('#bucket-add-to-cart').prop('disabled', true).text('Adding...');

        $.post(buckets_builder.ajax_url, postData)
            .done(function(resp){
                if (resp.success) {
                    // redirect to cart
                    window.location.href = resp.data.cart_url || buckets_builder.cart_url || '/cart';
                } else {
                    alert(resp.data && resp.data.message ? resp.data.message : 'Error adding to cart');
                    $('#bucket-add-to-cart').prop('disabled', false).text('ADD TO CART');
                }
            })
            .fail(function(){
                alert('AJAX error. Please try again.');
                $('#bucket-add-to-cart').prop('disabled', false).text('ADD TO CART');
            });
    });

    // initial total
    updateTotalAndUI();
});
