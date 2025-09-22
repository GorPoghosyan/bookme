jQuery(function($){
    function updateTotal(){
        let total = 0;
        $(".bucket-item").each(function(){
            let price = parseFloat($(this).data('price'));
            let qty = parseInt($(this).find("input").val());
            total += price * qty;
        });
        $("#bucket-total").text(total.toFixed(2));
    }

    $(".plus").click(function(){
        let input = $(this).siblings("input");
        input.val(parseInt(input.val())+1);
        updateTotal();
    });

    $(".minus").click(function(){
        let input = $(this).siblings("input");
        let val = parseInt(input.val());
        if(val > 0) input.val(val-1);
        updateTotal();
    });

    $("#bucket-next").click(function(){
        let list = $("#bucket-list").empty();
        $(".bucket-item").each(function(){
            let name = $(this).find("h4").text();
            let qty = $(this).find("input").val();
            let price = $(this).data('price');
            if(qty > 0){
                list.append("<li>"+name+" x "+qty+" = "+(qty*price)+"</li>");
            }
        });
        $("#bucket-review").show();
    });

    $("#bucket-add-to-cart").click(function(){
        let items = [];
        $(".bucket-item").each(function(){
            let qty = parseInt($(this).find("input").val());
            if(qty>0){
                items.push({
                    id: $(this).data('id'),
                    qty: qty,
                    price: $(this).data('price')
                });
            }
        });

        $.post(wc_add_to_cart_params.ajax_url, {
            action: "buckets_add_to_cart",
            items: items
        }, function(){
            window.location.href = "/cart";
        });
    });
});
