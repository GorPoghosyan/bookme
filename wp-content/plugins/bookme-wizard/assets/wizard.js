(function($){
// If wizard meta form exists, copy its hidden inputs into the actual add-to-cart form
    $(document).on('ready ajaxComplete', function(){
        var $meta = $('.bookme-hidden-meta');
        var $form = $('form.cart');
        if ($meta.length && $form.length) {
            $meta.find('input[type=hidden]').each(function(){
                if (!$form.find('input[name="' + this.name + '"]').length) {
                    $('<input/>', { type:'hidden', name:this.name, value:this.value }).appendTo($form);
                }
            });
        }
    });


// OPTIONAL: If a Resource dropdown exists, try to auto-select a resource whose text includes the staff name from URL
    $(function(){
        const params = new URLSearchParams(window.location.search);
        const staffName = $('.bookme-card-title').first().text(); // not ideal; you can pass it via URL if needed
        var staffId = params.get('staff_id');
        var $resource = $('#wc_bookings_field_resource, select[name=wc_bookings_field_resource]');
        if ($resource.length && staffId) {
// Prefer matching by data attribute if your site adds it
            var matched = false;
            $resource.find('option').each(function(){
                var t = $(this).text().toLowerCase();
                if (t.indexOf('#'+staffId) !== -1) { $(this).prop('selected', true); matched = true; return false; }
            });
            if (matched) $resource.trigger('change');
        }
    });
})(jQuery);