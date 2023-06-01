"use strict";

jQuery(document).ready(function($){
    var $loading = $('#loading').hide();
    $(document)
        .ajaxStart(function () {
            $loading.show();
        })
        .ajaxStop(function () {
            $loading.hide();
        });

    $('div.mec-calendar').on('click', '.calendar-pager a', function(e) {
        e.preventDefault();
        var calendar = $('div.calendar-wrapper');
        var period = calendar.data('period');
        var layout = calendar.data('layout');
        var direction = $(this).data('direction');
        $.post(mec_ajax.ajax_url, {         //POST request
            _ajax_nonce: mec_ajax.nonce,     //nonce
            action: "UpdateCalendar",            //action
            period: period ,                  //data
            layout: layout,
            direction: direction,
        }, function(result) {                 //callback
            calendar.remove();
            $('div.mec-calendar').append(result);
        });
    });

});