<?php

require_once WP_CONTENT_DIR . '/plugins/my-event-calendar/vendor/CMB2/init.php';

// Register Custom Post Type
add_action( 'init', 'mec_organizerPostType', 0 );
function mec_organizerPostType() {
    $labels = [
        'name'                  => _x('Organizers', 'Post type general name', 'my-event-calendar'),
        'singular_name'         => _x('Organizer', 'Post type singular name', 'my-event-calendar'),
        'menu_name'             => _x('Organizers', 'Admin Menu text', 'my-event-calendar'),
        'name_admin_bar'        => _x('Organizer', 'Add New on Toolbar', 'my-event-calendar'),
        'add_new'               => __('Add New', 'my-event-calendar'),
        'add_new_item'          => __('Add New Organizer', 'my-event-calendar'),
        'new_item'              => __('New Organizer', 'my-event-calendar'),
        'edit_item'             => __('Edit Organizer', 'my-event-calendar'),
        'view_item'             => __('View Organizer', 'my-event-calendar'),
        'all_items'             => __('Organizer', 'my-event-calendar'),
        'search_items'          => __('Search Organizer', 'my-event-calendar'),
        'not_found'             => __('No Organizers found.', 'my-event-calendar'),
        'not_found_in_trash'    => __('No Organizers found in Trash.', 'my-event-calendar'),
        'featured_image'        => _x('Organizer Image', 'Overrides the “Featured Image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'set_featured_image'    => _x('Set organizer image', 'Overrides the “Set featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'remove_featured_image' => _x('Remove organizer image', 'Overrides the “Remove featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'use_featured_image'    => _x('Use as organizer image', 'Overrides the “Use as featured image” phrase for this post type. Added in 4.3', 'my-event-calendar'),
        'archives'              => _x('Organizer archives', 'The post type archive label used in nav menus. Default “Post Archives”. Added in 4.4', 'my-event-calendar'),
        'insert_into_item'      => _x('Insert into organizer', 'Overrides the “Insert into post”/”Insert into page” phrase (used when inserting media into a post). Added in 4.4', 'my-event-calendar'),
        'uploaded_to_this_item' => _x('Uploaded to this organizer', 'Overrides the “Uploaded to this post”/”Uploaded to this page” phrase (used when viewing media attached to a post). Added in 4.4', 'my-event-calendar'),
        'filter_items_list'     => _x('Filter organizer list', 'Screen reader text for the filter links heading on the post type listing screen. Default “Filter posts list”/”Filter pages list”. Added in 4.4', 'my-event-calendar'),
        'items_list_navigation' => _x('Organizer list navigation', 'Screen reader text for the pagination heading on the post type listing screen. Default “Posts list navigation”/”Pages list navigation”. Added in 4.4', 'my-event-calendar'),
        'items_list'            => _x('Organizer list', 'Screen reader text for the items list heading on the post type listing screen. Default “Posts list”/”Pages list”. Added in 4.4', 'my-event-calendar'),
    ];

    //$capabilities = CPT::makeCapabilities('event', 'organizer');
    $args = [
        'label' => __('Organizers', 'my-event-calendar'),
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
        //'rewrite'                   => array( 'slug' => 'tanzanleiter')
    ];
    register_post_type('organizer', $args);
}

add_action( 'cmb2_admin_init', 'mec_organizerFields' );
function mec_organizerFields() {
    /**
     * Metabox to add fields to categories and tags
     */
    $cmb_term = new_cmb2_box(array(
        'id' => 'mec-organizer',
        'title' => esc_html__('Organizer Information', 'my-event-calendar'),
        'object_types' => ['organizer'],
    ));
    $cmb_term->add_field( array(
        'name' => esc_html__( 'Phone (landline)', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'phone',
        'type' => 'text_medium',
    ) );
    $cmb_term->add_field( array(
        'name' => esc_html__( 'Phone (mobile)', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'phone_mobile',
        'type' => 'text_medium',
    ) );
    $cmb_term->add_field( array(
        'name' => esc_html__( 'Email', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'email',
        'type' => 'text_email',
    ) );
    $cmb_term->add_field( array(
        'name' => esc_html__( 'Website', 'my-event-calendar' ),
        //'desc' => esc_html__( '', 'my-event-calendar' ),
        'id'   => 'website',
        'type' => 'text_url',
    ) );
}


function mec_contentOrganizer( $content ) {
    if (get_post_type() !== 'organizer')
        return $content;

    $id = get_the_ID();
    $meta = get_post_meta($id);
    $output = $content;
    $output .= '<div class="mec-details">';
    $phone = mec_get_meta($meta, 'phone');
    $mobile = mec_get_meta($meta, 'phone_mobile');
    $email = mec_get_meta($meta, 'email');
    $website = mec_get_meta($meta, 'website');
    if ($phone != '') {
        $output .= '<p><span class="label">' . __('Phone (landline)', 'my-event-calendar') . ':</span> ' . $phone . '</p>';
    }
    if ($mobile != '') {
        $output .= '<p><span class="label">' . __('Phone (mobile)', 'my-event-calendar') . ':</span> ' . $mobile . '</p>';
    }
    if ($email != '') {
        $output .= '<p><span class="label">' . __('Email', 'my-event-calendar') . ':</span> <a href="mailto:' . $email . '">' . $email . '</a></p>';
    }
    if ($website != '') {
        $output .= '<p><span class="label">' . __('Website', 'my-event-calendar') . ':</span> <a href="' . $website . '">' . $website . '</a></p>';
    }
    $output .= '</div>';
    return $output;
}
add_filter( 'the_content', 'mec_contentOrganizer', 99);
