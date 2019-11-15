<?php

add_action('rest_api_init', function () {
    register_rest_route( 'commissions/v1', 'create/(?P<category_id>\d+)',array(
                  'methods'  => 'GET',
                  'callback' => 'get_latest_posts_by_category'
                  //'permission_callback' => function() {
                   // return current_user_can('edit_posts');
                   // }
        ));
});

function get_latest_posts_by_category($request) {

    $args = array(
            'category' => $request['category_id']
    );

    $posts = get_posts($args);
    if (empty($posts)) {
    return new WP_Error( 'empty_category', 'there is no post in this category', array('status' => 409) );

    }

    $response = new WP_REST_Response($posts);
    $response->set_status(200);

    return $response;
}

?>