<?php

require_once WP_CONTENT_DIR . '/plugins/my-event-calendar/vendor/CMB2/init.php';

// Register Custom Post Type
add_action( 'init', 'mec_locationPostType', 0 );
function mec_locationPostType() {
    $labels = [
        'name'                  => _x('Locations', 'Post type general name', 'my-event-calendar'),
        'singular_name'         => _x('Location', 'Post type singular name', 'my-event-calendar'),
        'menu_name'             => _x('Locations', 'Admin Menu text', 'my-event-calendar'),
        'name_admin_bar'        => _x('Location', 'Add New on Toolbar', 'my-event-calendar'),
        'add_new'               => __('Add New', 'my-event-calendar'),
        'add_new_item'          => __('Add New Location', 'my-event-calendar'),
        'new_item'              => __('New Location', 'my-event-calendar'),
        'edit_item'             => __('Edit Location', 'my-event-calendar'),
        'view_item'             => __('View Location', 'my-event-calendar'),
        'all_items'             => __('Location', 'my-event-calendar'),
        'search_items'          => __('Search Location', 'my-event-calendar'),
        'not_found'             => __('No Locations found.', 'my-event-calendar'),
        'not_found_in_trash'    => __('No Locations found in Trash.', 'my-event-calendar'),
        'featured_image'        => _x('Location Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'set_featured_image'    => _x('Set location image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'remove_featured_image' => _x('Remove location image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'use_featured_image'    => _x('Use as location image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'archives'              => _x('Location archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-event-calendar'),
        'insert_into_item'      => _x('Insert into location', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-event-calendar'),
        'uploaded_to_this_item' => _x('Uploaded to this location', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-event-calendar'),
        'filter_items_list'     => _x('Filter location list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-event-calendar'),
        'items_list_navigation' => _x('Location list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-event-calendar'),
        'items_list'            => _x('Location list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-event-calendar'),
    ];

    //$capabilities = CPT::makeCapabilities('event', 'location');
    $args = [
        'label' => __('Locations', 'my-event-calendar'),
        //'description' => __('', 'my-event-calendar'),
        'labels' => $labels,
        'supports'                  => ['title', 'editor', 'author', 'thumbnail'],
        'hierarchical'              => false,
        'public'                    => true,
        'show_ui'                   => true,
        'show_in_menu'              => 'edit.php?post_type=event',
        //'show_in_menu'              => true,
        'show_in_nav_menus'         => true,
        'show_in_admin_bar'         => true,
        'menu_icon'                 => 'dashicons-calendar-alt',
        'can_export'                => true,
        'has_archive'               => true,
        'exclude_from_search'       => true,
        'publicly_queryable'        => true,
        'delete_with_user'          => false,
        'show_in_rest'              => false,
        //'capabilities'              => $capabilities,
        'capability_type'           => 'post',
        'map_meta_cap'              => true,
        //'rewrite'                   => array( 'slug' => 'location')
    ];
    register_post_type('location', $args);
}

add_action( 'cmb2_admin_init', 'mec_locationFields' );
function mec_locationFields() {
    $cmb_info = new_cmb2_box(array(
        'id' => 'mec-location-address',
        'title' => esc_html__('Address', 'my-event-calendar'),
        'object_types' => ['location'],
    ));
    $cmb_info->add_field( array(
        'name' => esc_html__( 'Street', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'street',
        'type' => 'text_medium',
    ) );
    $cmb_info->add_field( array(
        'name' => esc_html__( 'Postal Code', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'postalcode',
        'type' => 'text_medium',
    ) );
    $cmb_info->add_field( array(
        'name' => esc_html__( 'City', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'city',
        'type' => 'text_medium',
    ) );
    $cmb_info->add_field( array(
        'name' => esc_html__( 'Website', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'website',
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
        /*'text'    => array(
            'add_upload_file_text' => 'Add File' // Change upload button text. Default: "Add or Upload File"
        ),*/
        'query_args' => array(
            'type' => array(
                'application/pdf',
            ),
        ),
        'preview_size' => 'large', // Image size to use when previewing in the admin.
    ) );
}

function mec_contentLocation( $content ) {
    if (get_post_type() !== 'location')
        return $content;

    $id = get_the_ID();
    $meta = get_post_meta($id);
    $output = $content;
    $output .= '<div class="mec-details">';
    $street = mec_get_meta($meta, 'street');
    $zip = mec_get_meta($meta, 'postalcode');
    $city = mec_get_meta($meta, 'city');
    if ($street != '' || $zip != '' || $city != '') {
        $output .= '<p><span class="label">' . __('Address', 'my-event-calendar') . ':</span> ' . $street . ', ' . $zip . ' ' . $city;
    }
    $url = mec_get_meta($meta, 'website');
    if ($url != '') {
        $output .= '<p><span class="label">' . __('Website', 'my-event-calendar') . ':</span> <a href="'. $url . '">' . $url . '</a>';
    }
    $output .= '</div>';
    return $output;
}

add_filter( 'the_content', 'mec_contentLocation', 99);


