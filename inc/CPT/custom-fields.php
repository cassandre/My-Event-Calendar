<?php

add_filter( 'cmb2_render_select_organizer', 'mec_renderSelectOrganizerField', 10, 5 );
function mec_renderSelectOrganizerField( $field, $value, $object_id, $object_type, $field_type ) {
    $organizers = get_posts([
        'post_type' => 'organizer',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    if (empty($organizers)) {
        echo __('No organizers found. Please enter organizers first.', 'my-event-calendar');
    } else {
        $options = '<option value="">' . __('Please select', 'my-event-calendar') . '</option>';
        foreach($organizers as $organizer) {
            $options .= '<option value="' . $organizer->ID . '">' . $organizer->post_title . '</option>';
        }
        ?>
        <?php echo $field_type->select( array(
            'name'    => $field_type->_name(),
            'id'      => $field_type->_id(),
            'options' => $options,
            'desc'    => '',
        ) ); ?>
        <?php
    }
}

add_filter( 'cmb2_render_select_location', 'mec_renderSelectLocationField', 10, 5 );
function mec_renderSelectLocationField( $field, $value, $object_id, $object_type, $field_type ) {
    $locations = get_posts([
        'post_type' => 'location',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    if (empty($locations)) {
        echo __('No location found. Please enter location first.', 'my-event-calendar');
    } else {
        $options = '<option value="">' . __('Please select', 'my-event-calendar') . '</option>';
        foreach($locations as $location) {
            $options .= '<option value="' . $location->ID . '">' . $location->post_title . '</option>';
        }
        ?>
        <?php echo $field_type->select( array(
            'name'    => $field_type->_name(),
            'id'      => $field_type->_id(),
            'options' => $options,
            'desc'    => '',
        ) ); ?>
        <?php
    }
}

add_filter( 'cmb2_sanitize_select_location', 'mec_sanitizeSelectID', 10, 2 );
add_filter( 'cmb2_sanitize_select_event_series', 'mec_sanitizeSelectID', 10, 2 );
function mec_sanitizeSelectEventSeriesField( $override_value, $value) {
    // not an email?
    if ( ! is_numeric( $value ) ) {
        // Empty the value
        $value = '';
    }
    return $value;
}

add_filter( 'cmb2_render_select_weekdayofmonth', 'mec_renderMonthDayField', 10, 5 );
function mec_renderMonthDayField( $field, $value, $object_id, $object_type, $field_type ) {
    $value = wp_parse_args($value, array(
        'day' => '',
        'daycount' => '',
    ));
    $daycount = [
        '1' => __('first', 'my-event-calendar'),
        '2' => __('second', 'my-event-calendar'),
        '3' => __('third', 'my-event-calendar'),
        '4' => __('fourth', 'my-event-calendar'),
        '5' => __('fifth', 'my-event-calendar'),
    ];
    $optionsDaycount = '';
    foreach ($daycount as $k => $v) {
        $optionsDaycount .= '<option value="'.$k.'" ' . selected($k, $value['daycount'], false) . '>'.$v.'</option>';
    }
    $weekdays = [
        'mon' => __('Monday', 'my-event-calendar'),
        'tue' => __('Tuesday', 'my-event-calendar'),
        'wed' => __('Wednesday', 'my-event-calendar'),
        'thu' => __('Thursday', 'my-event-calendar'),
        'fri' => __('Friday', 'my-event-calendar'),
        'sat' => __('Saturday', 'my-event-calendar'),
        'sun' => __('Sunday', 'my-event-calendar'),
    ];
    $optionsWeekdays = '';
    foreach ($weekdays as $k => $v) {
        $optionsWeekdays .= '<option value="'.$k.'" ' . selected($k, $value['day'], false) . '>'.$v.'</option>';
    }
    ?>
    <label for="<?php echo $field_type->_id( '_daycount' ); ?>'" class="screen-reader-text">Tageszähler</label>
    <?php echo $field_type->select( array(
        'name'    => $field_type->_name( '[daycount]' ),
        'id'      => $field_type->_id( '_daycount' ),
        'options' => $optionsDaycount,
        'desc'    => '',
    ) ); ?>
    </>
    <label for="<?php echo $field_type->_id( '_day' ); ?>'" class="screen-reader-text">Wochentag</label>
    <?php echo $field_type->select( array(
        'name'    => $field_type->_name( '[day]' ),
        'id'      => $field_type->_id( '_day' ),
        'options' => $optionsWeekdays,
        'desc'    => '',
    ) ); ?>
    <?php
}

add_filter( 'cmb2_render_event_items', 'mec_renderEventItemsField', 10, 5 );
function mec_renderEventItemsField( $field, $value, $object_id, $object_type, $field_type ) {
    $eventItems = get_post_meta($object_id, 'event-items', true);
    if (is_array($eventItems)) {
        echo '<ul style="columns: 4 150px;">';
        foreach ($eventItems as $TSstart_ID => $TSend) {
            $start = explode('#', $TSstart_ID)[0];
            echo '<li>' . date_i18n(get_option('date_format'), $start) . '</li>';
            //echo '<li>' . date('Y-m-d', $start) . '</li>';
        }
        echo '</ul>';
        echo '<p class="description">' . __('Only event items of one year are listed, even if no end date is set.', 'my-event-calendar') . '</p>';
    } else {
        _e('No events found.', 'my-event-calendar');
    }
}