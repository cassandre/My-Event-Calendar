<?php

add_action( 'wp_ajax_UpdateCalendar', 'mec_ajaxUpdateCalendar' );
add_action( 'wp_ajax_nopriv_UpdateCalendar', 'mec_ajaxUpdateCalendar' );


function mec_CalendarShortcode($atts) {
    $atts = shortcode_atts(
        array(
            'year' => date('Y', current_time('timestamp')),
            'month' => 'current',
            'day' => '',
            'layout' => 'full',
            'navigation' => 'ja',
        ), $atts, 'mec-calendar' );

    // Calendar period
    $buttonDayClass = 'inactive';
    $buttonMonthClass = 'active';
    $buttonYearClass = 'inactive';
    $day = '';

    if (isset($_GET['cal-year']) && is_numeric($_GET['cal-year']) && ((int)$_GET['cal-year'] > 2000 && (int)$_GET['cal-year'] < 3000)) {
        $year = (int)$_GET['cal-year'];
    } else {
        if (is_numeric($atts['year']) && ((int)$atts['year'] > 2000 && (int)$atts['year'] < 3000)) {
            $year = (int)$atts['year'];
        } else {
            $year = date('Y', current_time('timestamp'));
        }
    }
    if (isset($_GET['cal-month']) && is_numeric($_GET['cal-month']) && ((int)$_GET['cal-month'] >= 1 && (int)$_GET['cal-month'] <= 12)) {
        $month = (int)$_GET['cal-month'];
    } else {
        if (is_numeric($atts['month']) && ((int)$atts['month'] >= 1 && (int)$atts['month'] <= 12)) {
            $month = (int)$atts['month'];
        } elseif (!isset($_GET['cal-year']) && $atts['month'] == "current") {
            $month = date('m', current_time('timestamp'));
        } else {
            $month = '';
            $atts['layout'] = 'mini';
            $buttonDayClass = 'inactive';
            $buttonMonthClass = 'inactive';
            $buttonYearClass = 'active';
        }
    }
    if ($month != '') {
        if (isset($_GET['cal-day']) && is_numeric($_GET['cal-day']) && ((int)$_GET['cal-day'] >= 1 && (int)$_GET['cal-day'] <= 31)) {
            $day = (int)$_GET['cal-day'];
            $buttonDayClass = 'active';
            $buttonMonthClass = 'inactive';
            $buttonYearClass = 'inactive';
        } else {
            if (is_numeric($atts['day']) && ((int)$atts['day'] >= 1 && (int)$atts['day'] <= 31)) {
                $day = (int)$atts['day'];
                $buttonDayClass = 'active';
                $buttonMonthClass = 'inactive';
                $buttonYearClass = 'inactive';
            } elseif ($atts['day'] == "heute") {
                $day = date('d', current_time('timestamp'));
                $buttonDayClass = 'active';
                $buttonMonthClass = 'inactive';
                $buttonYearClass = 'inactive';
            } else {
                $day = '';
                $buttonDayClass = 'inactive';
                $buttonMonthClass = 'active';
                $buttonYearClass = 'inactive';
                $monthName = date("F", mktime(0, 0, 0, $month, 1));
                $startObj = date_create('first day of ' . $monthName . ' ' . $year);
                $startTS = $startObj->getTimestamp();
                $endObj = date_create('last day of ' . $monthName . ' ' . $year);
                $endObj->add(new DateInterval('PT23H59M59S'));
                $endTS = $endObj->getTimestamp();
            }
        }
    } else {
        $startObj = date_create('first day of january' . $year);
        $startTS = $startObj->getTimestamp();
        $endObj = date_create('last day of december ' . $year);
        $endObj->add(new DateInterval('PT23H59M59S'));
        $endTS = $endObj->getTimestamp();
    }
    if ($day != '') {
        $startTS = strtotime($year.'-'.$month.'-'.str_pad($day, 2, '0', STR_PAD_LEFT));
        $endTS = $startTS + 24*60*60;
    }

    // Calendar layout: full or mini
    $layout = $atts['layout'] == 'full' ? 'full' : 'mini';
    if ($layout == 'full' && $month == '') {
        $month = date('m', current_time('timestamp'));
    }

    // Paging
    $paging = $atts['navigation'] == 'ja' ? true : false;

    // Get bookings in calendar period
    $events = get_posts([
        'post_type' => 'event',
        'numberposts' => -1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'mec-event-lastdate',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key' => 'mec-event-lastdate',
                'value' => $startTS,
                'compare' => '>='
            ],
        ],
    ]);
    $i = 0;
    foreach ($events as $event) {
        $eventItems = get_post_meta($event->ID, 'event-items', true);
        if (!empty($eventItems)) {
            foreach ($eventItems as $start => $end) {
                $eventsArray[$start][$i]['id'] = $event->ID;
                $eventsArray[$start][$i]['end'] = $end;
                $i++;
            }
        }
    }
    //print "<pre>";var_dump($eventItems);print "</pre>"; exit;
    //$eventsArray = mec_build_events_list($events, false);
    //print "<pre>";var_dump($events);print "</pre>";
    /*foreach ($eventsArray as $ts => $events) {
        foreach ($events as $event) {
            print date('Y-m-d', $ts) . ' ' . $event['id'] . '<br />';
        }
    }
    exit;*/

    // Render calendar output
    $output = '<div class="mec-calendar">';
    $output .= '<p class="cal-type-select">'
        .'<a href="?cal-year='.$year.'&cal-month='.date('m', current_time('timestamp')).'&cal-day='.date('d', current_time('timestamp')).'" class="' . $buttonDayClass . '" title="'.__('View day', 'my-event-calendar').'">' . __('Day', 'my-event-calendar') . '</a>'
        .'<a href="?cal-year='.$year.'&cal-month='.date('m', current_time('timestamp')).'" class="' . $buttonMonthClass . '" title="'.__('View monthly calendar', 'my-event-calendar').'">' . __('Month', 'my-event-calendar') . '</a>'
        .'<a href="?cal-year='.$year.'" class="' . $buttonYearClass . '" title="'.__('View yearly calendar', 'my-event-calendar').'">' . __('Year', 'my-event-calendar') . '</a>'
        .'</p>';
    $output .= mec_buildCalendar($year, $month, $day, $eventsArray, $layout, $paging);
    $output .= '</div>';

    wp_enqueue_script('jquery');
    wp_enqueue_script( 'mec-calendar-script', plugin_dir_url( __DIR__ ) . '/js/shortcode.js', array(), '1.0.0', true );
    wp_localize_script('mec-calendar-script', 'mec_ajax', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce( 'mec-calendar-ajax-nonce' ),
    ]);
    return $output;
}

add_shortcode('mec-calendar', 'mec_CalendarShortcode');

/**
 * Build a booking calendar for a defined period (month or year)
 * @param   string  $month      If empty -> build yearly calendar
 * @param   string  $year
 * @param   string  $day
 * @param   array   $eventsArray
 * @param   string  $layout     'full' or 'mini'
 * @param   bool    $paging     Allow skipping to next/previous month/year
 * @return  string
 */
function mec_buildCalendar($year, $month, $day, $eventsArray, $layout = 'full', $paging = true) {
    $output = '';
    if ($day != '') {
        $output .= '<div class="calendar-wrapper cal-day" data-period="'.$year.'-'.$month.'-'.$day.'" data-layout="' . ($layout == 'full' ? 'full' : 'mini') . '">';
        $output .= mec_renderDayList($year, $month, $day, $eventsArray);
        $output .= '</div>';
    } elseif ($month != '') {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $output .= '<div class="calendar-wrapper cal-month" data-period="'.$year.'-'.$month.'" data-layout="' . ($layout == 'full' ? 'full' : 'mini') . '">';
        if ($layout == 'full') {
            $output .= mec_renderMonthCalendarFull($year, $month,  $eventsArray, $paging);
        } else {
            $output .= mec_renderMonthCalendarMini($year, $month,  $eventsArray, true);
        }
        $output .= '</div>';
    } else {
        $output .= '<div class="calendar-wrapper cal-year" data-period="'.$year.'" data-layout="' . ($layout == 'full' ? 'full' : 'mini') . '">';
        $output .= '<div class="calendar-header"><h2 class="title-year">'.$year.'</h2>';
        if ($paging) {
            $output .= '<ul class="calendar-pager">
                    <li class="date-prev">
                        <a href="#" title="Zum vorangegangenen Jahr wechseln" rel="nofollow" data-direction="prev">« Zurück</a>
                    </li>
                    <li class="date-next">
                        <a href="#" title="Zum nächsten Jahr" rel="nofollow" data-direction="next">Weiter »</a>
                    </li>
                </ul>';
        }
        $output .= '</div>'
            .'<div class="calendar-year">';
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad((string)$i, 2, '0', STR_PAD_LEFT);
            $output .= mec_renderMonthCalendarMini($year, $month,  $eventsArray, false);
        }
        $output .= '</div></div>';
    }
    return $output;
}

/**
 * Render list of bookings on one day
 * @param array $bookings
 */
function mec_renderDayList($year, $month, $day, $eventsArray = []) {
    $calDay = $year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
    $calDayTs = strtotime($calDay);
    $output = '<div class="calendar-header"><h2 class="title-year">' . date_i18n(get_option( 'date_format' ), $calDayTs) . '</h2>';
    $output .= '<ul class="calendar-pager">
            <li class="date-prev">
                <a href="#" title="Zum vorangegangenen day wechseln" rel="nofollow" data-direction="prev">« Zurück</a>
            </li>
            <li class="date-next">
                <a href="#" title="Zum nächsten day wechseln" rel="nofollow" data-direction="next">Weiter »</a>
            </li>
        </ul>';
    $output .= '</div>';

    $list = '<ul class="day-list">';
    $hasEvents = false;

    foreach ($eventsArray as $ts => $events) {
        foreach ($events as $event) {
            $meta = get_post_meta($event['id']);
            $eventStart = $ts;
            $eventEnd = $event['end'];
            $eventStartDate = date('Y-m-d', $eventStart);
            $eventEndDate = date('Y-m-d', $eventEnd);
            if ($calDay < $eventStartDate || $calDay > $eventEndDate) {
                continue;
            }
            $timeText = '';
            $locationText = '';
            $intructorText = '';

            $eventTitle = get_the_title($event['id']);
            $eventURL = get_the_permalink($event['id']);
            $eventTitle = '<a href="' . $eventURL . '">' . $eventTitle . '</a>';
            // Date/Time
            if ($eventStartDate == $eventEndDate) {
                $timeText = '<span class="event-date">' . date('H:i', $eventStart) . ' - ' . date('H:i \U\h\r', $eventEnd) . '</span>';
            } else {
                $timeText = '<span class="event-date">' . date_i18n(get_option( 'date_format' ) . ', H:i \U\h\r,', $eventStart) . ' bis ' . date_i18n(get_option( 'date_format' ) . ', H:i \U\h\r', $eventEnd) . '</span>';
            }
            // Location
            $locationIDs = mec_get_meta($meta, 'mec-event-location');
            if (empty($locationIDs) && $hasSeries) {
                $locationIDs = mec_get_meta($seriesMeta, 'mec-eventseries-location');
            }
            if ($locationIDs != '') {
                $locations = [];
                foreach ($locationIDs as $locationID) {
                    $locations[] = get_the_title($locationID);
                }
                $locationText = implode(', ', $locations);
            }
            // Organizer(s)
            $organizerIDs =  mec_get_meta($meta, 'organizer');
            if ($organizerIDs != '') {
                $organizers = [];
                foreach ($organizerIDs as $organizerID) {
                    $organizers[] = '<a href="' . get_permalink($organizerID) . '">' . get_the_title($organizerID) . '</a>';
                }
                $intructorText = __('Organizer', 'my-event-calendar') . ': ' . implode(', ', $organizers);
            }



            $list .= '<li>';
            $list .= $timeText . '<br />' . $eventTitle . '<br />' . $locationText . '<br />' . $intructorText;
            $list .= '</li>';
            $hasEvents = true;
        }
    }
    $list .= '</ul>';

    if ($hasEvents) {
        $output .= $list;
    } else {
        $output .= '<p>' . __('An diesem Tag finden keine Veranstaltungen statt.', 'my-event-calendar') . '</p>';
    }

    return $output;
}

/**
 * Render mini calendar month view with availability information for external use
 * @param   integer $month
 * @param   integer $year
 * @param   array   $bookings
 * @param   bool    $showYear
 * @return  string
 */

function mec_renderMonthCalendarMini($year, $month,  $eventsArray = [], $showYear = false) {
    $first_day_in_month = date('w',mktime(0,0,0,$month,1,$year));
    $month_days = date('t',mktime(0,0,0,$month,1,$year));
    $month_names = [
        '01' => __('January', 'my-event-calendar'),
        '02' => __('February', 'my-event-calendar'),
        '03' => __('March', 'my-event-calendar'),
        '04' => __('April', 'my-event-calendar'),
        '05' => __('May', 'my-event-calendar'),
        '06' => __('June', 'my-event-calendar'),
        '07' => __('July', 'my-event-calendar'),
        '08' => __('August', 'my-event-calendar'),
        '09' => __('September', 'my-event-calendar'),
        '10' => __('October', 'my-event-calendar'),
        '11' => __('November', 'my-event-calendar'),
        '12' => __('December', 'my-event-calendar')];
    $month_name = $month_names[$month];
    // in PHP, Sunday is the first day in the week with number zero (0)
    // to make our calendar works we will change this to (7)
    if ($first_day_in_month == 0){
        $first_day_in_month = 7;
    }
    if ($showYear) {
        $month_name .= ' ' . $year;
    } else {
        $month_name = '<a href="?cal-year='.$year.'&cal-month='.$month.'"">' . $month_name . '</a>';
    }
    $output = '<div class="calendar-month mini">';
    $output .= '<table>';
    $output .= '<tr><th colspan="7">' . $month_name . '</th></tr>';
    $output .= '<tr class="days">'
        .'<td>'._x('Mon', 'Abbr. Monday', 'my-event-calendar').'</td>'
        .'<td>'._x('Tue', 'Abbr. Tuesday', 'my-event-calendar').'</td>'
        .'<td>'._x('Wed', 'Abbr. Wednesday', 'my-event-calendar').'</td>'
        .'<td>'._x('Thu', 'Abbr. Thursday', 'my-event-calendar').'</td>'
        .'<td>'._x('Fri', 'Abbr. Friday', 'my-event-calendar').'</td>'
        .'<td>'._x('Sat', 'Abbr. Saturday', 'my-event-calendar').'</td>'
        .'<td>'._x('Sun', 'Abbr. Sunday', 'my-event-calendar').'</td>'
        .'<tr>';

    for($i = 1; $i < $first_day_in_month; $i++) {
        $output .= '<td> </td>';
    }
    //var_dump($eventsArray);
    for($day = 1; $day <= $month_days; $day++) {
        $pos = ($day + $first_day_in_month - 1) % 7;
        $date = $year.'-'.$month.'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
        $calDay = strtotime($date);
        $linkOpen = '';
        $linkClose = '';
        $class = 'has-no-events';
        foreach ($eventsArray as $ts => $events) {
            foreach ($events as $event) {
                $eventStart = $ts;
                $eventEnd = $event['end'];
                //var_dump($calDay >= $eventStart && $calDay <= $eventEnd);
                //var_dump($date, date('Y-m-d', $eventStart), date('Y-m-d', $eventEnd), ($date >= date('Y-m-d', $eventStart) && $date <= date('Y-m-d', $eventEnd)) );
                //print "<br />";
                //if ($calDay >= $eventStart && $calDay <= $eventEnd) {
                if ($date >= date('Y-m-d', $eventStart) && $date <= date('Y-m-d', $eventEnd)) {
                    $linkOpen = '<a href="?cal-year='.$year.'&cal-month='.$month.'&cal-day='.str_pad($day, 2, '0', STR_PAD_LEFT).'" title="'.__('View Details', 'my-event-calendar').'">';
                    $linkClose = '</a>';
                    $class = 'has-events';
                    continue 2;
                }
            }
        }
        $day = date('d', $calDay);
        $output .= '<td class="' . $class . '">' . $linkOpen . $day . $linkClose . '</td>';
        if ($pos == 0) $output .= '</tr><tr>';
    }
    //TODO: leere Tabellenzellen bis Monatsende
    //TODO: Link intern zu Tagesansicht

    $output .= '</tr>';
    $output .= '</table>';
    $output .= '</div>';

    return $output;
}

/**
 * Render Full Calendar Month View with booking details for internal use
 * @param integer $month
 * @param integer $year
 * @param array $bookings
 * @param bool $paging  Allow skipping to next/previous month
 * @param array $locations
 * @return string
 */

function mec_renderMonthCalendarFull($year, $month,  $eventsArray = [], $paging = true) {
    /*var_dump($eventsArray);
    foreach ($eventsArray as $ts => $events) {
        print date('Y-m-d', $ts) . '<br />';
    }
    exit;*/
    $first_day_in_month = date('w',mktime(0,0,0,$month,1,$year));
    $month_days = date('t',mktime(0,0,0,$month,1,$year));
    $month_names = [
        '01' => __('January', 'my-event-calendar'),
        '02' => __('February', 'my-event-calendar'),
        '03' => __('March', 'my-event-calendar'),
        '04' => __('April', 'my-event-calendar'),
        '05' => __('May', 'my-event-calendar'),
        '06' => __('June', 'my-event-calendar'),
        '07' => __('July', 'my-event-calendar'),
        '08' => __('August', 'my-event-calendar'),
        '09' => __('September', 'my-event-calendar'),
        '10' => __('October', 'my-event-calendar'),
        '11' => __('November', 'my-event-calendar'),
        '12' => __('December', 'my-event-calendar')];
    $month_name = $month_names[$month];
    // in PHP, Sunday is the first day in the week with number zero (0)
    // to make our calendar works we will change this to (7)
    if ($first_day_in_month == 0){
        $first_day_in_month = 7;
    }
    $day_names = [
        '1' => _x('Mon', 'Abbr. Monday', 'my-event-calendar'),
        '2' => _x('Tue', 'Abbr. Tuesday', 'my-event-calendar'),
        '3' => _x('Wed', 'Abbr. Wednesday', 'my-event-calendar'),
        '4' => _x('Thu', 'Abbr. Thursday', 'my-event-calendar'),
        '5' => _x('Fri', 'Abbr. Friday', 'my-event-calendar'),
        '6' => _x('Sat', 'Abbr. Saturday', 'my-event-calendar'),
        '7' => _x('Sun', 'Abbr. Sunday', 'my-event-calendar'),
    ];

    // Calender Header (Title + Nav)
    $output = '<div class="calendar-header"><h2 class="title-year">' . $month_name . ' ' . $year . '</h2>';
    if ($paging) {
        $output .= '<ul class="calendar-pager">
            <li class="date-prev">
                <a href="#" title="Zum vorangegangenen Monat wechseln" rel="nofollow" data-direction="prev">« Zurück</a>
            </li>
            <li class="date-next">
                <a href="#" title="Zum nächsten Monat" rel="nofollow" data-direction="next">Weiter »</a>
            </li>
        </ul>';
    }
    $output .= '</div>';
    $output .= '<div class="calendar-month full">';
    $output .= '<div class="days">';
    foreach ($day_names as $i => $day_name) {
        $output .= '<div style="grid-column-start: day-'.$i.'; grid-column-end: span 1; grid-row-start: date; grid-row-end: span 1;" class="day-names">' . $day_name . '</div>';
    }
    $output .='</div>';

    // Weeks
    $output .= '<div class="week">';
    for($i = 1; $i < $first_day_in_month; $i++) {
        $output .= '<div class="empty-day" style="grid-column: day-'.$i.' / day-'.$i.';  grid-row: 1 / 5;" aria-hidden="true"> </div>';
    }
    $weekNum = 1;
    $eventsPerDay = [];

    for($day = 1; $day <= $month_days; $day++) {
        $pos = ($day + $first_day_in_month - 1) % 7;
        $date = $year.'-'.$month.'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
        $col = $pos == 0 ? 7 : $pos;
        $calDay = $date;
        $output .= '<div class="day" style="grid-column: day-'.$col.' / day-'.$col.'; grid-row: 1 / 2;" aria-hidden="true">' . $day . '</div>';
        $week = '';
        $daysLeft = $month_days - $day + 1;

        // Background div for each day
        $week .= '<div class="no-event" style="grid-column-start: day-'.$col.'; grid-column-end: span 1; grid-row-start: 2; grid-row-end: 5" aria-hidden="true"> </div>';

        foreach ($eventsArray as $ts => $events) {
            foreach ($events as $event) {
                $eventStart = $ts;
                $eventEnd = $event['end'];
                $eventStartDate = date('Y-m-d', $eventStart);
                $eventEndDate = date('Y-m-d', $eventEnd);
                if ($calDay < $eventStartDate || $calDay > $eventEndDate) {
                    continue;
                }
                $meta = get_post_meta($event['id']);
                $eventTitle = get_the_title($event['id']);
                $eventURL = get_the_permalink($event['id']);
                $categories = get_the_terms($event['id'], 'event-category');
                if ($categories) {
                    $catID = $categories[0]->term_id;
                    $catColor = get_term_meta($catID, 'color', true);
                } else {
                    $catColor = '';
                }
                if ($catColor == '') $catColor = 'inherit';
                $eventTitleShort = $eventTitle;
                if (strlen($eventTitle) > 40) {
                    $eventTitleShort = substr($eventTitle, 0, 37) . '&hellip;';
                }
                $eventTitle = '<a href="' . $eventURL . '">' . $eventTitle . '</a>';
                $eventTitleShort = '<a href="' . $eventURL . '">' . $eventTitleShort . '</a>';

                $eventClasses = ['event'];

                if ($calDay == $eventStartDate) {
                    // Bookings starting on this day
                    array_push($eventClasses, 'event-start', 'event-end');
                    $dateClasses = ['event-date'];
                    $span = floor(($eventEnd - $eventStart) / (60 * 60 * 24) + 1);
                    if ($span < 1) $span = 1;
                    if ($span > 1) {
                        $timeOut = '';
                    } else {
                        $dateClasses[] = 'hide-desktop';
                        $timeOut = '<span class="event-time">' . date('H:i', $eventStart) . ' - ' . date('H:i', $eventEnd) . '<br /></span>';
                    }
                    if (($col + $span) > 8) {
                        $span = 8 - $col + 1; // trim if event longer than week
                        if (($key = array_search('event-end', $eventClasses)) !== false) {
                            unset($eventClasses[$key]);
                        }
                    }
                    if ($span > $daysLeft) {
                        $span = $daysLeft; // trim if event longer than month
                        if (($key = array_search('event-end', $eventClasses)) !== false) {
                            unset($eventClasses[$key]);
                        }
                    }
                    $eventInfos = [];
                    if (isset($eventsPerDay[$eventStartDate])) {
                        $eventsPerDay[$eventStartDate]++;
                    } else {
                        $eventsPerDay[$eventStartDate] = 1;
                    }
                    if ($eventStartDate == $eventEndDate) {
                        $dateOut = date('d.m.Y', $eventStart);
                    } else {
                        $dateOut = date('d.m.Y', $eventStart) . ' - ' . date('d.m.Y', $eventEnd);
                    }
                    $thumbnail = get_the_post_thumbnail($event['id'], 'medium');
                    $content = get_post_meta($event['id'], 'description', true);
                    $excerpt = strip_tags($content);
                    if (strlen($excerpt) > 100) {
                        $excerpt = substr($excerpt, 0, 100);
                        $excerpt = '<span>' . substr($excerpt, 0, strrpos($excerpt, ' ')) . '&hellip;</span>';
                    }
                    $rowNum = $eventsPerDay[$eventStartDate];
                    $week .= '<div class="' . implode(' ', $eventClasses) . '" style="grid-column: day-' . $col . ' / day-' . ($col + $span) . '; grid-row: ' . ($rowNum + 1) . ' / ' . ($rowNum + 1) . '; border-color: ' . $catColor . ';">'
                        . '<p><span class="' . implode(' ', $dateClasses) . '">' . $dateOut . '<br /></span>'
                        . $timeOut
                        . '<span class="event-title">' . $eventTitleShort . '</span></p>'
                        . '<div role="tooltip" aria-hidden="true">'
                            . '<p style="margin: 0;">' . $thumbnail . '</p>'
                            . '<div class="event-title">' . $eventTitle . '</div>'
                            . '<div class="event-date-time">' . $dateOut . ', ' . $timeOut . '</div>'
                            . '<div class="event-description">' . $excerpt . ' <a href="' . $eventURL . '">' . __('Read more', 'my-event-calendar') . ' &raquo;</a></div>'
                        . '</div>'
                        . '</div>';

                } elseif (($col == 1 || $day == 1) && $calDay > $eventStartDate && $calDay <= $eventEndDate) {
                    // Bookings continuing from past week (or past month)
                    if ((($eventEnd - strtotime($calDay)) / (60 * 60 * 24)) < 0.3) {
                        // Don't show event that end before 6:00, because it is probably the rest of a previous' day event
                        continue;
                    }
                    $span = floor(($eventEnd - strtotime($calDay)) / (60 * 60 * 24) + 1);
                    var_dump($calDay, ($eventEnd - strtotime($calDay)) / (60 * 60 * 24));
                    if ($span > 7) {
                        $span = 7; // trim if event longer than week
                        array_push($eventClasses, 'event-week');
                    } else {
                        array_push($eventClasses, 'event-end');
                    }
                    if ($span > $daysLeft) {
                        $span = $daysLeft - 1; // trim if event longer than month
                    }
                    if (isset($eventsPerDay[$eventStartDate])) {
                        $eventsPerDay[$eventStartDate]++;
                    } else {
                        $eventsPerDay[$eventStartDate] = 1;
                    }
                    if ($eventStartDate == $eventEndDate) {
                        $dateOut = date('d.m.Y', $eventStart);
                    } else {
                        $dateOut = date('d.m.Y', $eventStart) . ' - ' . date('d.m.Y', $eventEnd);
                    }
                    $timeOut = '<span class="event-time">' . date('H:i', $eventStart) . ' - ' . date('H:i', $eventEnd) . '<br /></span>';
                    $thumbnail = get_the_post_thumbnail($event['id'], 'medium');
                    $content = get_post_meta($event['id'], 'description', true);
                    $excerpt = strip_tags($content);
                    if (strlen($excerpt) > 100) {
                        $excerpt = substr($excerpt, 0, 100);
                        $excerpt = '<span>' . substr($excerpt, 0, strrpos($excerpt, ' ')) . '&hellip;</span>';
                    }
                    $rowNum = $eventsPerDay[$eventStartDate];
                        $week .= '<div class="' . implode(' ', $eventClasses) . '" style="grid-column: day-' . $col . ' / day-' . ($col + $span) . '; grid-row: ' . ($rowNum + 1) . ' / ' . ($rowNum + 1) . ';">'
                        . '<span class="event-date">' . date('d.m.Y', $eventStart) . ' - ' . date('d.m.Y', $eventEnd) . '<br /></span>'
                        . '<span class="event-title">' . $eventTitleShort . '</span>'
                        . '<div role="tooltip" aria-hidden="true">'
                            . '<p style="margin: 0;">' . $thumbnail . '</p>'
                            . '<div class="event-title">' . $eventTitle . '</div>'
                            . '<div class="event-date-time">' . $dateOut . ', ' . $timeOut . '</div>'
                            . '<div class="event-description">' . $excerpt . ' <a href="' . $eventURL . '">' . __('Read more', 'my-event-calendar') . ' &raquo;</a></div>'
                        . '</div>'
                        . '</div>';
                }
            }
        }

        // Add empty cells if month ends before weekend
        if ($day == $month_days && $col < 7) {
            for ($i = ($col + 1); $i <= 7; $i++) {
                $week .= '<div class="empty-day" style="grid-column: day-'.$i.' / day-'.$i.';  grid-row: 1 / 5;" aria-hidden="true"> </div>';
            }
        }

        // Add week to output
        $output .= $week;

        // After 7 days: Increment week counter, reset row counter, line break
        if ($pos == 0) {
            $weekNum++;
            $eventsPerDay = [];
            $output .= '</div><div class="week">';
        }
    }
    $output .= '</div>';

    $output .= '</div>';
    return $output;
}

function mec_renderSingleEvent($id) {

}

function mec_ajaxUpdateCalendar() {
    check_ajax_referer( 'mec-calendar-ajax-nonce', 'nonce' );
    $output = '';
    $periodRaw = sanitize_text_field($_POST['period']);
    $period = explode('-', $periodRaw);
    $layout = sanitize_text_field($_POST['layout']);
    if (count($period) == 3) {
        $day = (int)$period[2];
        $month = (int)$period[1];
        $year = (int)$period[0];
        switch ($_POST['direction']) {
            case 'prev':
                $date = date('Y-m-d', strtotime($periodRaw .' -1 day'));
                $day = date('d', strtotime($periodRaw .' -1 day'));
                break;
            case 'next':
            default:
                $date = date('Y-m-d', strtotime($periodRaw .' +1 day'));
                $day = date('d', strtotime($periodRaw .' +1 day'));
            break;
        }
        $startObj = date_create($date);
    } elseif (count($period) == 2) {
        $day = '';
        $month = (int)$period[1];
        $year = (int)$period[0];
        switch ($month) {
            case 0:
                $month = '';
                // $year = $year;
                break;
            case 1:
                $month += ($_POST['direction'] == 'next' ? 1 : 11);
                $year += ($_POST['direction'] == 'next' ? 0 : -1);
                break;
            case 12:
                $month += ($_POST['direction'] == 'next' ? -11 : -1);
                $year += ($_POST['direction'] == 'next' ? 1 : 0);
                break;
            default:
                $month += ($_POST['direction'] == 'next' ? 1 : -1);
                // $year = $year;
                break;
        }
        $monthName = strtolower(date("F", mktime(0, 0, 0, $month, 1)));
        $startObj = date_create('first day of ' . $monthName . ' ' . $year);
        //$endObj = date_create('last day of ' . $monthName . ' ' . $year);
    } else {
        $day = '';
        $month = '';
        $year = (int)$period[0];
        $year += ($_POST['direction'] == 'next' ? 1 : -1);
        $startObj = date_create('first day of january' . $year);
        //$endObj = date_create('last day of december ' . $year);
    }
    //$endObj->add(new DateInterval('PT23H59M59S'));
    //$endTS = $endObj->getTimestamp();
    $startTS = $startObj->getTimestamp();
    // Get bookings in calendar period
    $events = get_posts([
        'post_type' => 'event',
        'posts_per_page' => -1,
        'meta_query' => [
            'relation' => 'OR',
            [
                'key' => 'mec-event-lastdate',
                'compare' => 'NOT EXISTS'
            ],
            [
                'key' => 'mec-event-lastdate',
                'value' => $startTS,
                'compare' => '>='
            ],
        ],
    ]);
    $i = 0;
    foreach ($events as $event) {
        $eventItems = get_post_meta($event->ID, 'event-items', true);
        if (!empty($eventItems)) {
            foreach ($eventItems as $start => $end) {
                $eventsArray[$start][$i]['id'] = $event->ID;
                $eventsArray[$start][$i]['end'] = $end;
                $i++;
            }
        }
    }
    $output .= mec_BuildCalendar($year, $month, $day, $eventsArray, $layout);
    echo $output;
    wp_die();
}