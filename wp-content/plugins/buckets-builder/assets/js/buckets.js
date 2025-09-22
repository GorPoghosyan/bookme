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
    });

    $(document).on('click', '.bb-minus', function(){
        var $input = $(this).siblings('.bb-qty-input');
        var v = parseInt($input.val()) || 0;
        if (v > 0) $input.val(v - 1);
        updateTotalAndUI();
    });

    // Tabs clickable
    $(document).on('click', '.bb-tab', function(){
        var step = $(this).data('step');
        $('.bb-tab').removeClass('active');
        $(this).addClass('active');
        $('.bb-step').removeClass('active');
        $('.bb-step-' + step).addClass('active');
    });

    // Next button -> fill review table and switch to step 2
    $('#bucket-next').on('click', function(e){
        e.preventDefault();
        var total = updateTotalAndUI();
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
                    $('#bucket-add-to-cart').prop('disabled', false).text('SEND');
                }
            })
            .fail(function(){
                alert('AJAX error. Please try again.');
                $('#bucket-add-to-cart').prop('disabled', false).text('SEND');
            });
    });

    // initial total
    updateTotalAndUI();
});
