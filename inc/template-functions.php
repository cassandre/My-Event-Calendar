<?php
/**
 * Functions which enhance the theme by hooking into WordPress
 *
 * @package Erlanger_Tanzhaus_2022
 */

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function mec_body_classes( $classes ) {
	// Adds a class of hfeed to non-singular pages.
	if ( ! is_singular() ) {
		$classes[] = 'hfeed';
	}

	// Adds a class of no-sidebar when there is no sidebar present.
	if ( ! is_active_sidebar( 'sidebar-1' )  ||
        (is_front_page() && get_theme_mod('mec_frontpage-sidebar') == false)) {
		$classes[] = 'no-sidebar';
	}

	return $classes;
}
add_filter( 'body_class', 'mec_body_classes' );

/**
 * Add a pingback url auto-discovery header for single posts, pages, or attachments.
 */
function mec_pingback_header() {
	if ( is_singular() && pings_open() ) {
		printf( '<link rel="pingback" href="%s">', esc_url( get_bloginfo( 'pingback_url' ) ) );
	}
}
add_action( 'wp_head', 'mec_pingback_header' );

/**
 * Make a single event list out of repeating events
 * @param $events
 */
function mec_build_events_list($events, $upcomingOnly = true) {
    $eventsArray = [];
    foreach ($events as $event) {
        $period = [];
        $meta = get_post_meta($event->ID);
        $startTS = mec_get_meta($meta, 'start');
        if ($startTS == '') return [];
        $endTS = mec_get_meta($meta, 'end');
        if ($endTS == '') return [];
        $duration = $endTS - $startTS;
        $repeat = mec_get_meta($meta, 'repeat');
        //if ($repeat !== 'on' && $date <= time()) {
            // not repeating event in the past
            //continue;
        //}
        $startHour = date('H', $startTS);
        $startMinute = date('i', $startTS);
        if ($repeat !== 'on' && (($upcomingOnly && $startTS >= time()) || !$upcomingOnly)) {
            // not repeating event
            $eventsArray[$startTS] = $startTS + $duration;
        } else {
            // repeating event
            $repeatInterval = mec_get_meta($meta, 'repeat-interval');
            $startDate = DateTime::createFromFormat( 'U', $startTS );
            $todayDate = new DateTime('today');
            /*if ($upcomingOnly && ($startDate < $todayDate)) {
                $start = $todayDate;
            } else {
                $start = $startDate;
            }*/
            $start = $startDate;
            $lastDate = mec_get_meta($meta, 'repeat-lastdate');
            if ($lastDate != '') {
                $end = DateTime::createFromFormat( 'U', ($lastDate + (60*60*24-1)));
            } else {
                $end = clone $start;
                $end->add(new DateInterval('P1Y')); // Move to 1 year from start
            }
            switch ($repeatInterval) {
                case 'week':
                    $unit  = 'W';
                    $step = mec_get_meta($meta, 'repeat-weekly-interval');
                    $dows = mec_get_meta($meta, 'repeat-weekly-day');
                    if ($step != '' && $dows != '') {
                        $interval = new DateInterval("P{$step}{$unit}");
                        foreach ($dows as $dow) {
                            $start->modify($dow); // Move to first occurence
                            $period[] = new DatePeriod($start, $interval, $end);
                        }
                        foreach ($period as $d) {
                            foreach ($d as $date) {
                                $date->add(new DateInterval('PT'.$startHour.'H'.$startMinute.'M'));
                                if (!$upcomingOnly || ($upcomingOnly && $date >= $todayDate)) {
                                    $eventsArray[$date->getTimestamp()] = $date->getTimestamp() + $duration;
                                }
                            }
                        }
                    }
                    // unset exceptions
                    $exceptionsRaw = mec_get_meta($meta,'exceptions');
                    if (!empty($exceptionsRaw)) {
                        $exceptions = explode("\n", str_replace("\r", '', $exceptionsRaw));
                        foreach ($eventsArray as $start => $end) {
                            $dayFormatted = date('Y-m-d', $start);
                            if (in_array($dayFormatted, $exceptions)) {
                                unset($eventsArray[$start]);
                            }
                        }
                    }
                    break;
                case 'month':
                    $unit  = 'M';
                    $monthlyType = mec_get_meta($meta,'repeat-monthly-type');
                    if ($monthlyType == 'date') {
                        $monthlyDate = mec_get_meta($meta,'repeat-monthly-type-date');
                        if ($monthlyDate < $start->format('d')) {
                            $start->modify('first day of next month');
                            $diff = (int)$monthlyDate - 1;
                        } else {
                            $diff = (int)$monthlyDate - (int)$start->format('d');
                        }
                        $start->modify('+'.$diff.' day');
                        $interval = new DateInterval("P1M");
                        $period[] = new DatePeriod($start, $interval, $end);
                        foreach ($period as $d) {
                            foreach ($d as $date) {
                                $date->add(new DateInterval('PT'.$startHour.'H'.$startMinute.'M'));
                                if (!$upcomingOnly || ($upcomingOnly && $date >= $todayDate)) {
                                    $eventsArray[$date->getTimestamp()][$i]['id'] = $event->ID;
                                    $eventsArray[$date->getTimestamp()][$i]['end'] = $date->getTimestamp() + $duration;
                                }
                            }
                        }
                    } elseif ($monthlyType == 'dow') {
                        $monthlyDOW = mec_get_meta($meta,'repeat-monthly-type-dow');
                        if ($monthlyDOW == '' || !isset($monthlyDOW ["day"]) || !isset($monthlyDOW ["daycount"])) {
                            continue 2;
                        }
                        $diff = $monthlyDOW ["daycount"] - 1;
                        $start->modify('first ' . $monthlyDOW ["day"] . ' of this month')->modify('+'.$diff.' week'); // Move to first occurence
                        if ($start < $startDate) {
                            $start->modify('first ' . $monthlyDOW ["day"] . ' of next month')->modify('+'.$diff.' week');
                        }
                        while ($start <= $end) {
                            $start->add(new DateInterval('PT'.$startHour.'H'.$startMinute.'M'));
                            if (!$upcomingOnly || ($upcomingOnly && $start >= $todayDate)) {
                                $eventsArray[$start->getTimestamp()][$i]['id'] = $event->ID;
                                $eventsArray[$start->getTimestamp()][$i]['end'] =  $start->getTimestamp() + $duration;
                            }
                            $start->modify('first ' . $monthlyDOW ["day"] . ' of next month')->modify('+'.$diff.' week');
                        }
                    }
                    // unset unselected months
                    $months = (array)mec_get_meta($meta,'repeat-monthly-month');
                    foreach ($eventsArray as $timestamp => $events) {
                        foreach ($events as $event => $eventData) {
                            $month = strtolower(date('M', $timestamp));
                            if (!in_array($month, $months)) {
                                unset($eventsArray[$timestamp][$event]);
                            }
                        }
                    }
                    break;
            }
        }
        ksort($eventsArray);
    }
    return $eventsArray;
}

function mec_event_excerpt($excerpt, $post): string
{
    $post_id = $post->ID;
    $read_more = sprintf(
            ' <a href="%s" class="read-more">'
            . wp_kses(
            /* translators: %s: Name of current post. Only visible to screen readers */
                __('Continue reading<span class="screen-reader-text"> "%s"</span>', 'my-event-calendar'),
                array(
                    'span' => array(
                        'class' => array(),
                    ),
                )
            )
            . ' &raquo;</a>',
            get_the_permalink(),
            wp_kses_post(get_the_title())
        );
    if (get_post_type($post_id) != 'event')
        return $excerpt . $read_more;
    if (has_excerpt($post_id)) {
        return $excerpt . $read_more;
    } else {
        $content = get_post_meta($post_id, 'description', true);
        $content = strip_tags($content);
        $content = str_replace(array("\r", "\n"), ' ', $content);
        $content = substr($content, 0, 260);
        return substr($content, 0, strrpos($content, ' ')) . '&hellip;' . $read_more;
    }
}
add_filter('get_the_excerpt', 'mec_event_excerpt', 10, 2);


function mec_get_meta($meta, $key) {
    if (!isset($meta[$key]))
        return '';
    if (strpos($meta[$key][0], 'a:',0) === 0) {
        return unserialize($meta[$key][0]);
    } else {
        return $meta[$key][0];
    }
}
