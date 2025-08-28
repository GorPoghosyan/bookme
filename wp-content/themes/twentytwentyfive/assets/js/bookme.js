jQuery(document).ready(function($){
    
    function loadBookMeStep(step, extra){
        $.ajax({
            url: bookme_ajax.ajax_url, // we’ll define this in wp_localize_script
            type: 'POST',
            data: {
                action: 'bookme_load_step',
                bookme_step: step,
                extra: extra
            },
            beforeSend: function(){
                $('#bookme-step-content').html('Loading...');
            },
            success: function(response){
                $('#bookme-step-content').html(response);
            },
            error: function(){
                $('#bookme-step-content').html('Error loading step.');
            }
        });
    }

    // Bind click for next step buttons (staff, service, time)
    $(document).on('click', '.bookme-next-step', function(e){
        e.preventDefault();
        var $btn = $(this);
        var nextStep = $btn.data('next-step'); // must be select-time for service button
        var extra = {
            vendor_id: $btn.data('vendor-id'),
            staff_id: $btn.data('staff-id'),
            service_id: $btn.data('service-id'),
            date: $btn.data('date'),
            time: $btn.data('time')
        };

        console.log('Click extra:', extra, 'nextStep:', nextStep); // debug

        loadBookMeStep(nextStep, extra);
    });


});
