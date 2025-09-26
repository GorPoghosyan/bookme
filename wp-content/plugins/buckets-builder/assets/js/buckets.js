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
        const $c = $("#bb-flowers-in-vase");
        if (!$c.length) return;
        if ($c.css("position") === "static") $c.css("position", "relative");

        const items = optionalItems || $(".bb-item").map((_, el) => {
            const $el = $(el), qty = +$el.find(".bb-qty-input").val() || 0;
            return Array(qty).fill({ src: $el.data("flower") });
        }).get().flat();

        if (!items.length) return $c.empty();

        const W = Math.max(200, $c.width()), H = Math.max(200, $c.height()),
            cx = W/2, cy = H*0.48, d = Math.min(W,H)*0.28,
            R0 = d*0.35, step = d*0.78, maxR = R0 + 20*step;
        let idx=0, placements=[];

        for (let layer=0; idx<items.length && layer<=20; layer++) {
            const R = R0 + layer*step,
                slots = Math.max(1, Math.floor((2*Math.PI*R)/(d*0.95))),
                start = layer%2?0:Math.random()*2*Math.PI;
            for (let s=0; s<slots && idx<items.length; s++,idx++) {
                const ang = start + s*(2*Math.PI/slots) + (Math.random()*2-1)*.12,
                    x = cx+R*Math.cos(ang)+(Math.random()*2-1)*d*.05,
                    y = cy+R*Math.sin(ang)+(Math.random()*2-1)*d*.05 - 12*(1-R/maxR),
                    sc=0.8+0.25*(1-R/maxR)+(Math.random()*.12-.06);
                placements.push({x,y,sc,rot:ang*180/Math.PI+(Math.random()*16-8),src:items[idx].src,R});
            }
        }

        // light overlap fix
        for (let it=0; it<4 && placements.length<=250; it++)
            for (let i=0;i<placements.length;i++) for (let j=i+1;j<placements.length;j++) {
                let a=placements[i], b=placements[j],
                    dx=a.x-b.x, dy=a.y-b.y, dist=Math.hypot(dx,dy)||.001,
                    o=(d*.5*a.sc + d*.5*b.sc - dist);
                if (o>0){ let ux=dx/dist, uy=dy/dist, sh=o*.52;
                    a.x+=ux*sh; a.y+=uy*sh; b.x-=ux*sh; b.y-=uy*sh;
                }
            }

        $c.empty()[0].append(...placements.map(p=>{
            let img=new Image();
            Object.assign(img.style,{
                position:"absolute",left:(p.x-d/2)+"px",top:(p.y-d/2)+"px",
                width:(d*p.sc)+"px",transform:`rotate(${p.rot}deg)`,zIndex:100+Math.round(p.R)
            });
            img.className="bb-flower-in-vase"; img.src=p.src;
            return img;
        }));
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
