<?php

require_once WP_CONTENT_DIR . '/plugins/my-event-calendar/vendor/CMB2/init.php';
require_once 'custom-fields.php';
require_once WP_CONTENT_DIR . '/plugins/my-event-calendar/inc/template-functions.php';

// Register Custom Post Type
add_action( 'init', 'mec_eventPostType', 0 );
function mec_eventPostType() {
    $labels = [
        'name'                  => _x('Events', 'Post type general name', 'my-event-calendar'),
        'singular_name'         => _x('Event', 'Post type singular name', 'my-event-calendar'),
        'menu_name'             => _x('Events', 'Admin Menu text', 'my-event-calendar'),
        'name_admin_bar'        => _x('Event', 'Add New on Toolbar', 'my-event-calendar'),
        'add_new'               => __('Add New Event', 'my-event-calendar'),
        'add_new_item'          => __('Add New Event', 'my-event-calendar'),
        'new_item'              => __('New Event', 'my-event-calendar'),
        'edit_item'             => __('Edit Event', 'my-event-calendar'),
        'view_item'             => __('View Event', 'my-event-calendar'),
        'all_items'             => __('All Events', 'my-event-calendar'),
        'search_items'          => __('Search Events', 'my-event-calendar'),
        'not_found'             => __('No Events found.', 'my-event-calendar'),
        'not_found_in_trash'    => __('No Events found in Trash.', 'my-event-calendar'),
        'featured_image'        => _x('Event Logo', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'set_featured_image'    => _x('Set event logo', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'remove_featured_image' => _x('Remove event logo', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'use_featured_image'    => _x('Use as event logo', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'archives'              => _x('Event archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-event-calendar'),
        'insert_into_item'      => _x('Insert into event', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-event-calendar'),
        'uploaded_to_this_item' => _x('Uploaded to this event', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-event-calendar'),
        'filter_items_list'     => _x('Filter events list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-event-calendar'),
        'items_list_navigation' => _x('Events list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-event-calendar'),
        'items_list'            => _x('Events list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-event-calendar'),
    ];

    //$capabilities = CPT::makeCapabilities('event', 'events');
    $args = [
        'label' => __('Event', 'my-event-calendar'),
        'description' => __('Add and edit event informations', 'my-event-calendar'),
        'labels' => $labels,
        'supports'                  => ['title', 'author', 'excerpt', 'thumbnail'],
        'hierarchical'              => true,
        'public'                    => true,
        'show_ui'                   => true,
        //'show_in_menu'              => 'edit.php?post_type=event',
        'show_in_menu'              => true,
        'show_in_nav_menus'         => true,
        'show_in_admin_bar'         => true,
        'menu_icon'                 => 'dashicons-calendar-alt',
        'can_export'                => true,
        'has_archive'               => false,
        'exclude_from_search'       => true,
        'publicly_queryable'        => true,
        'delete_with_user'          => false,
        'show_in_rest'              => false,
        //'capabilities'              => $capabilities,
        'capability_type'           => 'post',
        'map_meta_cap'              => true,
        'rewrite'                   => array( 'slug' => 'event')
    ];
    register_post_type('event', $args);
}

add_action('cmb2_admin_init', 'mec_eventFields');
function mec_eventFields() {
    $monthNamesShort = array(
        __('Jan', 'my-event-calendar'),
        __('Feb', 'my-event-calendar'),
        __('Mar', 'my-event-calendar'),
        __('Apr', 'my-event-calendar'),
        __('May', 'my-event-calendar'),
        __('Jun', 'my-event-calendar'),
        __('Jul', 'my-event-calendar'),
        __('Aug', 'my-event-calendar'),
        __('Sep', 'my-event-calendar'),
        __('Oct', 'my-event-calendar'),
        __('Nov', 'my-event-calendar'),
        __('Dec', 'my-event-calendar'),
    );
    $dayNames = array(
        __('Sunday', 'my-event-calendar'),
        __('Monday', 'my-event-calendar'),
        __('Tuesday', 'my-event-calendar'),
        __('Wednesday', 'my-event-calendar'),
        __('Thursday', 'my-event-calendar'),
        __('Friday', 'my-event-calendar'),
        __('Saturday', 'my-event-calendar')
    );
    $dayNamesMin = array(
        _x('Sun', 'Abbr. Sunday', 'my-event-calendar'),
        _x('Mon', 'Abbr. Monday', 'my-event-calendar'),
        _x('Tue', 'Abbr. Tuesday', 'my-event-calendar'),
        _x('Wed', 'Abbr. Wednesday', 'my-event-calendar'),
        _x('Thu', 'Abbr. Thursday', 'my-event-calendar'),
        _x('Fri', 'Abbr. Friday', 'my-event-calendar'),
        _x('Sat', 'Abbr. Saturday', 'my-event-calendar'),
    );
    // General Information
    $cmb_info = new_cmb2_box([
        'id' => 'my-event-calendar-event-info',
        'title' => __('General Information', 'my-event-calendar'),
        'object_types' => ['event'],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);
    $cmb_info->add_field( array(
        'name'    => esc_html__( 'Description', 'my-event-calendar' ),
        //'desc'    => __('', 'my-event-calendar'),
        'id'      => 'description',
        'type'    => 'wysiwyg',
        'options' => array(
            'textarea_rows' => get_option('default_post_edit_rows', 12),
        ),
    ) );
    $organizers = get_posts([
        'post_type' => 'organizer',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    $organizerOptions = [];
    foreach ($organizers as $organizer) {
         $organizerOptions[$organizer->ID] = $organizer->post_title;
    }
    $cmb_info->add_field([
        'name' => __('Organizer', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'organizer',
        'type' => 'multicheck',
        'options' => $organizerOptions,
    ]);
    $locations = get_posts([
        'post_type' => 'location',
        'posts_per_page' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ]);
    $locationOptions = [];
    foreach ($locations as $location) {
        $locationOptions[$location->ID] = $location->post_title;
    }
    $cmb_info->add_field([
        'name' => __('Location', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'location',
        'type' => 'multicheck',
        'options' => $locationOptions,
    ]);
    $cmb_info->add_field( array(
        'name' => esc_html__( 'VC URL', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'vc-url',
        'type' => 'text_url',
    ) );
    $cmb_info->add_field([
        'name' => __('Prices', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'prices',
        'type'    => 'wysiwyg',
        'options' => array(
            'teeny' => true,
            'textarea_rows' => get_option('default_post_edit_rows', 5),
            'media_buttons' => false,
        ),
    ]);
    $cmb_info->add_field( array(
        'name' => esc_html__( 'Tickets URL', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'tickets-url',
        'type' => 'text_url',
    ) );
    $cmb_info->add_field( array(
        'name'    => __('Downloads', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id'      => 'downloads',
        'type'    => 'file_list',
        // Optional:
        'options' => array(
            'url' => false, // Hide the text input for the url
        ),
        'text'    => array(
            'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
        ),
        'query_args' => array(
            'type' => array(
                'application/pdf',
            ),
        ),
        'preview_size' => 'large', // Image size to use when previewing in the admin.
    ) );
    $cmb_info->add_field([
        'name' => __('Featured Event', 'my-event-calendar'),
        //'desc'    => __('Show event on home page', 'my-event-calendar'),
        'id' => 'featured',
        'type' => 'checkbox',
    ]);

    // Schedule
    $cmb_schedule = new_cmb2_box([
        'id' => 'my-event-calendar-event-schedule',
        'title' => __('Schedule', 'my-event-calendar'),
        'object_types' => ['event'],
        'context' => 'normal',
        'priority' => 'high',
        'show_names' => true,
    ]);
    $cmb_schedule->add_field([
        'name' => __('Start', 'my-event-calendar'),
        //'desc'    => __('Date / Time', 'my-event-calendar'),
        'id' => 'start',
        'type' => 'text_datetime_timestamp',
        'date_format' => 'd.m.Y',
        'time_format' => 'H:i',
        'attributes' => array(
            // CMB2 checks for datepicker override data here:
            'data-datepicker' => json_encode( array(
                'firstDay' => 1,
                'dayNames' => $dayNames,
                'dayNamesMin' => $dayNamesMin,
                'monthNamesShort' => $monthNamesShort,
                'yearRange' => '-1:+10',
                'dateFormat'=> 'dd.mm.yy',
            ) ),
            'data-timepicker' => json_encode( array(
                'timeFormat' => 'HH:mm',
            ) ),
        ),
    ]);
    $cmb_schedule->add_field([
        'name' => __('End', 'my-event-calendar'),
        //'desc'    => __('Date / Time', 'my-event-calendar'),
        'id' => 'end',
        'type' => 'text_datetime_timestamp',
        'date_format' => 'd.m.Y',
        'time_format' => 'H:i',
        'attributes' => array(
            // CMB2 checks for datepicker override data here:
            'data-datepicker' => json_encode( array(
                'firstDay' => 1,
                'dayNames' => $dayNames,
                'dayNamesMin' => $dayNamesMin,
                'monthNamesShort' => $monthNamesShort,
                'yearRange' => '-1:+10',
                'dateFormat'=> 'dd.mm.yy',
            ) ),
            'data-timepicker' => json_encode( array(
                'timeFormat' => 'HH:mm',
            ) ),
        ),
    ]);

    $cmb_schedule->add_field([
        'name' => __('Repeat', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat',
        'type' => 'checkbox',
    ]);
    $cmb_schedule->add_field([
        'name' => __('Repeat until', 'my-event-calendar'),
        'desc'    => __('(optional)', 'my-event-calendar'),
        'id' => 'repeat-lastdate',
        'type' => 'text_date_timestamp',
        'date_format' => 'd.m.Y',
        'attributes' => array(
            // CMB2 checks for datepicker override data here:
            'data-datepicker' => json_encode( array(
                'firstDay' => 1,
                'dayNames' => $dayNames,
                'dayNamesMin' => $dayNamesMin,
                'monthNamesShort' => $monthNamesShort,
                'yearRange' => '-1:+10',
            ) ),
        ),
        'classes'   => ['repeat'],
    ]);
    $cmb_schedule->add_field([
        'name' => __('Repeat Interval', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat-interval',
        'type' => 'select',
        'default'          => 'week',
        'options'          => [
            'week'   => __( 'Weekly', 'my-event-calendar' ),
            'month'     => __( 'Monthly', 'my-event-calendar' ),
        ],
        'classes'   => ['repeat'],
    ]);
    // repeat weekly
    $cmb_schedule->add_field([
        'name' => __('Repeats', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat-weekly-interval',
        'type' => 'text_small',
        'attributes' => [
            'type' => 'number',
            'min' => '1',
        ],
        'before_field' => __('every', 'my-event-calendar') . ' ',
        'after_field' =>  ' ' . __('week(s)', 'my-event-calendar'),
        'classes'   => ['repeat', 'repeat-weekly'],
    ]);
    $cmb_schedule->add_field([
        'name' => __('Repeats on', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat-weekly-day',
        'type' => 'multicheck_inline',
        'options' => [
            'monday' => __('Mon', 'my-event-calendar'),
            'tuesday' => __('Tue', 'my-event-calendar'),
            'wednesday' => __('Wed', 'my-event-calendar'),
            'thursday' => __('Thu', 'my-event-calendar'),
            'friday' => __('Fri', 'my-event-calendar'),
            'saturday' => __('Sat', 'my-event-calendar'),
            'sunday' => __('Sun', 'my-event-calendar'),
        ],
        'classes'   => ['repeat', 'repeat-weekly'],
    ]);
    $cmb_schedule->add_field([
        'name' => __('Exceptions', 'my-event-calendar'),
        'desc' => __('Hier können Sie Tage angeben, an denen die Veranstaltung ausnahmsweise ausfällt (Format YYYY-MM-DD, z.B. 2023-12-31). Ein Datum pro Zeile.', 'my-event-calendar'),
        'id' => 'exceptions',
        'type' => 'textarea_small',
        'classes'   => ['repeat', 'repeat-weekly'],
    ]);
    // repeat monthly
    //TODO: jeden 1. /jeden Sonntag...
    $cmb_schedule->add_field([
        'name' => __('Each', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat-monthly-type',
        'type' => 'radio',
        'options' => [
            'date' => __('Date', 'my-event-calendar'),
            'dow' => __('Weekday', 'my-event-calendar'),
        ],
        'classes'   => ['repeat', 'repeat-monthly'],
    ]);
    $monthdays = [];
    for ($i = 1; $i <= 31; $i++) {
        $monthdays[$i] = $i.'.';
    }
    $cmb_schedule->add_field([
        'name' => __('Repeats on', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat-monthly-type-date',
        'type' => 'select',
        'options' => $monthdays,
        'before_field' => __('each', 'my-event-calendar') . ' ',
        'after_field' =>  ' ' . __('of month', 'my-event-calendar'),
        'classes'   => ['repeat', 'repeat-monthly', 'repeat-monthly-date'],
    ]);
    $cmb_schedule->add_field([
        'name' => __('Repeats on', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat-monthly-type-dow',
        'type' => 'select_weekdayofmonth',
        'before_field' => __('each', 'my-event-calendar') . ' ',
        'after_field' =>  ' ' . __('of month', 'my-event-calendar'),
        'classes'   => ['repeat', 'repeat-monthly', 'repeat-monthly-dow'],
    ]);
    $cmb_schedule->add_field([
        'name' => __('Repeats on', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'repeat-monthly-month',
        'type' => 'multicheck_inline',
        'options' => [
            'jan' => __('Jan', 'my-event-calendar'),
            'feb' => __('Feb', 'my-event-calendar'),
            'mar' => __('Mar', 'my-event-calendar'),
            'apr' => __('Apr', 'my-event-calendar'),
            'may' => __('May', 'my-event-calendar'),
            'jun' => __('Jun', 'my-event-calendar'),
            'jul' => __('Jul', 'my-event-calendar'),
            'aug' => __('Aug', 'my-event-calendar'),
            'sep' => __('Sep', 'my-event-calendar'),
            'oct' => __('Oct', 'my-event-calendar'),
            'nov' => __('Nov', 'my-event-calendar'),
            'dec' => __('Dec', 'my-event-calendar'),
        ],
        'classes'   => ['repeat', 'repeat-monthly'],
    ]);
    $cmb_schedule->add_field([
        'name' => __('Upcoming Event Items', 'my-event-calendar'),
        //'desc'    => __('', 'my-event-calendar'),
        'id' => 'event-items',
        'type' => 'event_items',
        'classes'   => ['repeat'],
    ]);

}

add_action( 'save_post', 'mec_saveEvent' );
function mec_saveEvent( $post_id ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || wp_is_post_revision( $post_id ) || ! ( get_post_type( $post_id ) === 'event' ) ) {
        return $post_id;
    }

    $eventList = mec_build_events_list([get_post($post_id)], true);
    //var_dump($eventList);
    update_post_meta($post_id, 'event-items', $eventList);

    // unhook this function to prevent infinite looping
   /* remove_action( 'save_post', 'mec_saveEvent' );

    $slugRaw = get_the_title($post_id);
    $eventDate = get_post_meta($post_id, 'my-event-calendar-event-date', true);
    if ($eventDate != '') {
        $slugRaw .= '-' . date('Y-m-d', $eventDate);
    }
    $slugRaw = sanitize_title($slugRaw);
    $postStatus = get_post_status($post_id);

    $slug = wp_unique_post_slug($slugRaw, $post_id, $postStatus, 'event', 0);
    // update the post slug
    wp_update_post( array(
        'ID' => $post_id,
        'post_name' => $slug // do your thing here
    ));

    // re-hook this function
    add_action( 'save_post', 'mec_saveEvent' );*/

}

add_action('updated_post_meta', 'mec_updatedMeta', 10, 4);
function mec_updatedMeta($meta_id, $post_id, $meta_key='', $meta_value='') {
    //if ($meta_key =='_edit_lock') {
        $eventList = mec_build_events_list([get_post($post_id)], true);
        update_post_meta($post_id, 'event-items', $eventList);
    //}
}

add_filter( 'manage_event_posts_columns', 'mec_filter_posts_columns' );
function mec_filter_posts_columns( $columns ) {
    $columns = array(
        'cb' => $columns['cb'],
        'title' => __( 'Title' ),
        'eventdate' => __( 'Date', 'my-event-calendar' ),
        'eventtime' => __( 'Time', 'my-event-calendar' ),
        'author' => __( 'Author' ),
        'date' => __( 'Date' ),
    );
    return $columns;
}

add_action( 'manage_event_posts_custom_column', 'mec_event_column', 10, 2);
function mec_event_column( $column, $post_id ) {
    $start = get_post_meta( $post_id, 'my-event-calendar-event-start', true );
    $end = get_post_meta( $post_id, 'my-event-calendar-event-end', true );
    if ( 'eventdate' === $column ) {
        if ($start != '') {
            echo date('d.m.Y', $start);
            if ($end != '' && (($end - $start) / 60 / 60 ) > 24) {
                echo ' - ' . date('d.m.Y', $end);
            }
        }
        $repeat = get_post_meta( $post_id, 'my-event-calendar-repeat-check', true );
        if ($repeat != '') {
            echo ' (' . __('Wdh.', 'my-event-calendar') . ')';
        }
    }
    if ( 'eventtime' === $column ) {
        if ($start != '') {
            echo date('H:i', $start);
            if ($end != '') {
                echo ' - ' . date('H:i', $end);
            }
        }
    }
}

add_action('init', 'mec_event_taxonomies');
function mec_event_taxonomies() {
    // Teilnehmerarten
    $labels_type = array(
        'name' => __('Event Categories', 'my-event-calendar'), // 'taxonomy general name'),
        'singular_name' => __('Event Category', 'my-event-calendar'), //'taxonomy singular name'),
    );
    $args_type = array(
        'labels' => $labels_type,
        'public'       => true,
        'hierarchical' => true,
        'show_admin_column' => true,
    );
    register_taxonomy('event-category', 'event', $args_type);
}

add_action( 'cmb2_admin_init', 'mec_event_category_fields' );
function mec_event_category_fields() {
    $cmb_rest = new_cmb2_box( array(
        'id'           => 'event_category_color',
        'title'        => esc_html__( 'Color Metabox', 'my-event-calendar' ),
        'object_types' => array('term' ), // Post type
        'taxonomies'   => array( 'event-category' ),
    ) );

    $cmb_rest->add_field( array(
        'name'       => esc_html__( 'Color', 'my-event-calendar' ),
        'id'         => 'color',
        'type'       => 'colorpicker',
        'attributes' => array(
            'data-colorpicker' => json_encode( array(
                // Iris Options set here as values in the 'data-colorpicker' array
                'palettes' => array( '#303F9F', '#448AFF', '#00796B', '#FFEB3B', '#F57C00', '#D32F2F', '#C2185B', '#7B1FA2'),
            ) ),
        ),
    ) );
}

add_filter( 'pre_get_posts', 'mec_add_event_to_archiving');
function mec_add_event_to_archiving( $query ) {
    if ( $query->is_main_query() && is_tax( 'event-category' ) ) {
        $query->set( 'post_type', array(
            'event',
        ) );

        return $query;
    }
}
add_filter( 'get_the_excerpt', 'mec_excerptEvent', 99);
function mec_excerptEvent($excerpt) {
    if (get_post_type() !== 'event')
        return $excerpt;

    $id = get_the_ID();
    $meta = get_post_meta($id);
    $output = '';
    $eventItems = mec_get_meta($meta, 'event-items');
    $output .= '<div class="mec-event" itemscope itemtype="http://schema.org/Event">
        <meta itemprop="name" content="' . get_the_title() . '">
        <div class="mec-schedule">
        <ul>';
    $i = 0;
    foreach ($eventItems as $TSstart_ID => $TSend) {
        $TSstart = explode('#', $TSstart_ID)[0];
        $startDay = date('Y-m-d', $TSstart);
        $endDay = date('Y-m-d', $TSend);
        if ($endDay == $startDay) {
            $eventEnd = date('H:i \U\h\r', $TSend);
        } else {
            $eventEnd = date_i18n('D, d.m.Y - H:i', $TSend);
        }
        $output .= '<li><span class="dashicons dashicons-calendar"></span><span class="mec-event-date">' . date_i18n('D, d.m.Y - H:i', $TSstart) . '</span> ' . _x('to', 'for duration', 'my-event-calendar') . ' <span class="mec-event-time">' . $eventEnd . '</span>'
            . '<meta itemprop="startDate" content="'. date_i18n('c', $TSstart) . '">'
            . '<meta itemprop="endDate" content="'. date_i18n('c', $TSend) . '">'
            . '</li>';
        $i++;
        if ($i > 2) break;
    }
    if ($i < count($eventItems)) {
        $output .= '<li>&hellip;</li>';
    }
    $output .= '</ul>
    </div>';
    $output .= '<div itemprop="description">' . $excerpt . '</div></div>';
    return $output;
}

add_filter( 'the_content', 'mec_contentEvent', 99);
function mec_contentEvent( $content ) {
    if (get_post_type() !== 'event')
        return $content;

    $id = get_the_ID();
    $meta = get_post_meta($id);
    $output = '';
    $eventItems = mec_get_meta($meta, 'event-items');
    $scheduleClass = count($eventItems) > 3 ? 'cols-2' : '';
    $output .= '<div class="mec-event" itemscope itemtype="http://schema.org/Event">
        <meta itemprop="name" content="' . get_the_title() . '">
        <div class="mec-schedule ' . $scheduleClass . '">
        <ul>';
    foreach ($eventItems as $TSstart_ID => $TSend) {
        $TSstart = explode('#', $TSstart_ID)[0];
        if ($TSstart >= time()) {
            $startDay = date('Y-m-d', $TSstart);
            $endDay = date('Y-m-d', $TSend);
            if ($endDay == $startDay) {
                $eventEnd = date('H:i \U\h\r', $TSend);
            } else {
                $eventEnd = date_i18n('D, d.m.Y - H:i', $TSend);
            }
            $output .= '<li><span class="dashicons dashicons-calendar"></span><span class="mec-event-date">' . date_i18n('D, d.m.Y - H:i', $TSstart) . '</span> ' . _x('to', 'for duration', 'my-event-calendar') . ' <span class="mec-event-time">' . $eventEnd . '</span>'
                . '<meta itemprop="startDate" content="'. date_i18n('c', $TSstart) . '">'
                . '<meta itemprop="endDate" content="'. date_i18n('c', $TSend) . '">'
                .'</li>';
        }
    }
    $output .= '</ul>
    </div>';
    $output .= '<div class="mec-description" itemprop="description">';
    // Description
    $description = mec_get_meta($meta, 'description');
    $output .= wpautop($description);
    $output .= '</div>
    <div class="mec-details">';

    // Topics
    $topicsObjects = wp_get_object_terms($id, 'event-category');
    if (!empty($topicsObjects)) {
        $topics = [];
        foreach ($topicsObjects as $topicsObject) {
            $topics[] = '<a href="' . get_term_link($topicsObject->term_id) . '">' . $topicsObject->name . '</a>';
        }
        $output .= '<p><span class="label">' . __('Event Categories', 'my-event-calendar') . ':</span> ' . implode(', ', $topics);
    }

    // Organizers(s)
    $organizerIDs =  mec_get_meta($meta, 'organizer');
    if ($organizerIDs != '') {
        $organizers = [];
        foreach ($organizerIDs as $organizerID) {
            //$organizers[] = '<a href="' . get_permalink($organizerID) . '">' . get_the_title($organizerID) . '</a>';
            $organizers[] = get_the_title($organizerID);
        }
        $output .= '<p itemprop="organizer" itemscope><span class="label">' . __('Organizer', 'my-event-calendar') . ':</span> ' . implode(', ', $organizers) . '</p>';
    }

    // Location
    $locationIDs = mec_get_meta($meta, 'location');
    if ($locationIDs != '') {
        $locations = [];
        foreach ($locationIDs as $locationID) {
            $locations[] = '<a href="' . get_permalink($locationID) . '">' . get_the_title($locationID) . '</a>';
        }
        $output .= '<p itemprop="location" itemscope><span class="label">' . __('Location', 'my-event-calendar') . ':</span> ' . implode(', ', $locations) . '</p>';
    }
    $vc_url = mec_get_meta($meta, 'vc-url');
    if ($vc_url != '') {
        $output .= '<p itemprop="location" itemscope itemtype="http://schema.org/VirtualLocation">><span class="label">' . __('Video Conference Link', 'my-event-calendar') . ':</span> <a itemprop="url" href="'. $vc_url . '">' . $vc_url . '</a>';
    }

    // Prices + Tickets
    $prices = mec_get_meta($meta, 'prices');
    if ($prices != '') {
        $output .= '<div itemprop="offers" itemscope itemtype="https://schema.org/Offer"><span class="label" style="float:left;">' . __('Prices', 'my-event-calendar') . ':</span><div class="prices">' . wpautop($prices) . '</div></div>';
    }
    $url = mec_get_meta($meta, 'tickets-url');
    if ($url != '') {
        $output .= '<p><span class="label">' . __('Tickets', 'my-event-calendar') . ':</span> <a href="'. $url . '">' . $url . '</a>';
    }

    //Downloads
    $downloads = mec_get_meta($meta, 'downloads');
    if ($downloads != '') {
        $output .= '<div><span class="label" style="float:left;">' . __('Downloads', 'my-event-calendar') . ':</span><ul class="downloads"><li>';
        $downloadList = [];
        foreach ($downloads as $attachmentID => $attachmentURL ) {
            $caption = wp_get_attachment_caption($attachmentID);
            if ($caption == '') {
                $caption = basename(get_attached_file($attachmentID));
            }
            $downloadList[] = '<a href="' . $attachmentURL . '">' . $caption . '</a>';
        }
        $output .=  implode('</li><li>', $downloadList);
        $output .=  '</li></ul></div>';
    }
    $output .= '</div></div>';
    return $output;
}


