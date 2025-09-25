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

    function updateFlowersInVase(optionalItems) {
        var res = optionalItems ? { items: optionalItems } : (function(){
            var items = [];
            $(".bb-item").each(function(){
                var qty = parseInt($(this).find('.bb-qty-input').val()) || 0;
                var src = $(this).data("flower");
                if (src && qty > 0) {
                    items.push({ src: src, qty: qty });
                }
            });
            return { items: items };
        })();

        var items = res.items || [];
        var $container = $("#bb-flowers-in-vase");
        if (!$container.length) return;

        // ensure relative positioning
        if ($container.css('position') === 'static') {
            $container.css('position','relative');
        }

        var containerW = Math.max(200, $container.width());
        var containerH = Math.max(200, $container.height());
        var cx = containerW / 2;
        var cy = containerH * 0.48;
        var baseImgSize = Math.round(Math.min(containerW, containerH) * 0.28);
        var avgDiameter = baseImgSize;
        var baseRadius = avgDiameter * 0.35;
        var radiusStep = avgDiameter * 0.78;
        var spacingFactor = 0.95;
        var angleJitter = 0.12;
        var posJitter = Math.max(4, Math.round(baseImgSize*0.05));
        var frag = document.createDocumentFragment();

        // expand items -> separate flowers
        var flowers = [];
        items.forEach(function(it){
            if (!it.src) return;
            for (var k=0;k<it.qty;k++) flowers.push({ src: it.src });
        });
        if (flowers.length === 0) {
            $container.empty();
            return;
        }

        // placement loop
        var placements = [];
        var idx = 0, layer = 0, maxLayers = 20;
        var maxR = baseRadius + maxLayers * radiusStep;
        while (idx < flowers.length && layer <= maxLayers) {
            var R = baseRadius + layer * radiusStep;
            var slots = Math.max(1, Math.floor((2*Math.PI*R) / (avgDiameter * spacingFactor)));
            var startAngle = (layer % 2 === 0) ? (Math.random() * Math.PI * 2) : 0;
            for (var s = 0; s < slots && idx < flowers.length; s++, idx++) {
                var angle = startAngle + s*(2*Math.PI/slots) + (Math.random()*2-1)*angleJitter;
                var x = cx + R*Math.cos(angle) + (Math.random()*2-1)*posJitter;
                var y = cy + R*Math.sin(angle) + (Math.random()*2-1)*posJitter - 12*(1 - R/maxR);
                var scale = 0.8 + 0.25*(1 - R/maxR) + (Math.random()*0.12-0.06);
                var rotate = (angle * 180/Math.PI) + (Math.random()*16 - 8);
                placements.push({ x, y, scale, rotate, src: flowers[idx].src, R: R });
            }
            layer++;
        }

        // overlap correction
        if (placements.length <= 250) {
            for (var it = 0; it < 4; it++) {
                for (var i = 0; i < placements.length; i++) {
                    for (var j = i+1; j < placements.length; j++) {
                        var a = placements[i], b = placements[j];
                        var dx = a.x - b.x, dy = a.y - b.y;
                        var dist = Math.sqrt(dx*dx + dy*dy) || 0.0001;
                        var ri = (avgDiameter*0.5) * a.scale;
                        var rj = (avgDiameter*0.5) * b.scale;
                        var overlap = ri + rj - dist;
                        if (overlap > 0) {
                            var ux = dx/dist, uy = dy/dist;
                            var shift = overlap * 0.52;
                            a.x += ux * shift; a.y += uy * shift;
                            b.x -= ux * shift; b.y -= uy * shift;
                        }
                    }
                }
            }
        }

        // render
        $container.empty();
        placements.forEach(function(p, i){
            var img = document.createElement("img");
            img.src = p.src;
            img.className = "bb-flower-in-vase";
            img.style.position = "absolute";
            img.style.left = (p.x - avgDiameter/2) + "px";
            img.style.top  = (p.y - avgDiameter/2) + "px";
            img.style.width = (avgDiameter * p.scale) + "px";
            img.style.transform = "rotate(" + p.rotate + "deg)";
            img.style.zIndex = 100 + Math.round(p.R);
            frag.appendChild(img);
        });
        $container[0].appendChild(frag);
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
