jQuery(document).ready(function ($) {

    var repeatCheck = $('input#repeat');
    var repeatIntervalSelect = $('select#repeat-interval');
    var repeatMonthlyTypeInput = $("input[name='repeat-monthly-type']");

    triggerRepeatFields();

    repeatCheck.on('change', function() {
        triggerRepeatFields();
    });

    repeatIntervalSelect.on('change', function() {
        triggerIntervalFields();
    });

    repeatMonthlyTypeInput.on('change', function() {
        triggerMonthlyTypeFields();
    });

    function triggerRepeatFields() {
        if (repeatCheck.is(':checked')) {
            $('div.repeat').slideDown();
            triggerIntervalFields();
        } else {
            $('div.repeat').slideUp();
        }
    }

    function triggerIntervalFields() {
        var repeatInterval = $('option:selected',repeatIntervalSelect).val();
        if (repeatInterval === 'week') {
            $('div.repeat-weekly').slideDown();
            $('div.repeat-monthly').slideUp();
        } else if (repeatInterval === 'month') {
            $('div.repeat-monthly').slideDown();
            $('div.repeat-weekly').slideUp();
            triggerMonthlyTypeFields();
        }
    }

    function triggerMonthlyTypeFields() {
        var repeatMonthlyType = $("input[name='repeat-monthly-type']:checked").val();
        if (typeof(repeatMonthlyType) == 'undefined') {
            $('div.repeat-monthly-date').hide();
            $('div.repeat-monthly-dow').hide();
        } else if (repeatMonthlyType === 'dow') {
            $('div.repeat-monthly-dow').slideDown();
            $('div.repeat-monthly-date').slideUp();
        } else if (repeatMonthlyType === 'date') {
            $('div.repeat-monthly-date').slideDown();
            $('div.repeat-monthly-dow').slideUp();
        }
    }
});

