<?php
/*
* Shortcode Event Series
*/

add_shortcode('mec-events', 'shortcodeEvents');
function shortcodeEvents( $atts ) {
    $atts_default = [
        'featured' => 'false',
        'display' => 'teaser',
        'number' => '99',
        'category' => '',
        'tag' => '',

    ];
    $atts = shortcode_atts( $atts_default, $atts );
    $featured = in_array($atts['featured'], ['true', 'ja', 'yes', '1']);
    $display = $atts['display'] == 'list' ? 'list' : 'teaser';
    $number = (int)$atts['number'];
    $category = sanitize_text_field($atts['category']);
    $tag = sanitize_text_field($atts['tag']);
    $args = [
        'post_type' => 'event',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'AND',
            [
                'relation' => 'OR',
                [
                    'key' => 'repeat-lastdate',
                    'compare' => 'NOT EXISTS'
                ],
                [
                    'key' => 'repeat-lastdate',
                    'value' => time(),
                    'compare' => '>='
                ],
            ],
        ],
        'orderby' => 'meta_key',
        'meta_key' => 'start',
    ];
    if ($featured) {
        $args['meta_query'][] = [
            'key' => 'featured',
            'value' => 'on',
        ];
    }

    if ($category != '') {
        $categories = array_map('trim', explode(",", $category));
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'event-category',
                'field' => 'name',
                'terms' => $categories,
            )
        );
    }

    if ($tag != '') {
        $t_id = [];
        $tags = array_map('trim', explode(",", $tag));
        foreach ($tags as $_t) {
            if ($term_id = get_term_by('name', $_t, 'post_tag')->term_id) {
                $t_id[] = $term_id;
            }
        }
    }

    $events = get_posts($args );

    $output = '<div class="mec-sc-events">';

    if (!empty($events)) {
        $i = 0;
        foreach ($events as $event) {
            $eventItems = get_post_meta($event->ID, 'event-items', true);
            if (!empty($eventItems)) {
                foreach ($eventItems as $TSstart_ID => $TSend) {
                    $start = explode('#', $TSstart_ID)[0];
                    $eventsArray[$start][$i]['id'] = $event->ID;
                    $eventsArray[$start][$i]['end'] = $TSend;
                    $i++;
                }
            }
        }
        ksort($eventsArray);
        if ($display == 'list') {
            $output .= '<ul class="mec-events-list">';
            $i = 0;
            foreach ($eventsArray as $timestamp => $events) {
                if ($timestamp >= time()) {
                    foreach ($events as $event) {
                        $eventTitle = get_the_title($event['id']);
                        $eventURL = get_the_permalink($event['id']);
                        $eventTitle = '<a href="' . $eventURL . '">' . $eventTitle . '</a>';
                        $output .= '<li><span class="mec-event-date"> ' . date_i18n('D, d.m.Y', $timestamp) . '<span class="dashicons dashicons-clock"></span>' . date_i18n('H:i', $timestamp) . '</span><br />'
                            . '<span class="mec-event-title">' . $eventTitle . '</span></li>';
                        $i++;
                        if ($i >= $number) break 2;
                    }
                }
            }
            $output .= '</ul>';
        } else {
            $i = 0;
            foreach ($eventsArray as $timestamp => $events) {
                if ($timestamp >= time()) {
                    foreach ($events as $event) {
                        $eventTitle = get_the_title($event['id']);
                        $eventURL = get_the_permalink($event['id']);
                        $eventTitle = '<a href="' . $eventURL . '">' . $eventTitle . '</a>';
                        $eventSummary = get_the_excerpt($event['id']);
                        $output .= '<div class="mec-event">'
                            . '<div class="event-dateblock">'
                            . '<div class="day-month">'
                            . '<div class="day">' . date('d', $timestamp) . '</div>'
                            . '<div class="month">' . date_i18n('M', $timestamp) . '.</div>'
                            . '</div>'
                            //. '<div class="year">' . date('Y', $timestamp) .'</div>'
                            . '</div>'
                            . '<div class="event-content">'
                            . '<h2 class="event-title">' . $eventTitle . '</h2>'
                            . '<div class="event-summary">' . $eventSummary . '</div>'
                            . '</div>'
                            . '</div>';
                        $i++;
                        if ($i >= $number) break 2;
                    }
                }
            }
        }
    } else {
        $output .= '<p>' . __('No events scheduled.', 'my-event-calendar') . '</p>';
    }
    $output .= '</div>';
    wp_reset_postdata();
    return $output;
}