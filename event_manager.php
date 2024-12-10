<?php
/**
 * Plugin Name: Event Manager
 * Description: Ein verbessertes Plugin, um Veranstaltungen mit Start-/Enddatum und -zeiten klar anzuzeigen.
 * Version: 1.6
 * Author: Your Name
 */

// Erstelle den Custom Post Type für Events
function em_create_event_post_type() {
    register_post_type('em_event', [
        'labels' => [
            'name' => __('Termine', 'event-manager'),
            'singular_name' => __('Termin', 'event-manager'),
        ],
        'public' => true,
        'has_archive' => true,
        'rewrite' => ['slug' => 'termine'],
        'supports' => ['title', 'editor'],
    ]);
}
add_action('init', 'em_create_event_post_type');

// Füge Meta-Boxen hinzu
function em_add_event_meta_boxes() {
    add_meta_box('em_event_details', 'Veranstaltungsdetails', 'em_event_details_meta_box_callback', 'em_event', 'normal');
}
add_action('add_meta_boxes', 'em_add_event_meta_boxes');

function em_event_details_meta_box_callback($post) {
    $start_date = get_post_meta($post->ID, '_em_event_start_date', true);
    $end_date = get_post_meta($post->ID, '_em_event_end_date', true);
    $start_time = get_post_meta($post->ID, '_em_event_start_time', true);
    $end_time = get_post_meta($post->ID, '_em_event_end_time', true);
    $location = get_post_meta($post->ID, '_em_event_location', true);
    $note = get_post_meta($post->ID, '_em_event_note', true);

    echo '<label for="em_event_start_date">Startdatum:</label>';
    echo '<input type="date" id="em_event_start_date" name="em_event_start_date" value="' . esc_attr($start_date) . '" /><br><br>';

    echo '<label for="em_event_start_time">Startzeit:</label>';
    echo '<input type="time" id="em_event_start_time" name="em_event_start_time" value="' . esc_attr($start_time) . '" /><br><br>';

    echo '<label for="em_event_end_date">Enddatum:</label>';
    echo '<input type="date" id="em_event_end_date" name="em_event_end_date" value="' . esc_attr($end_date) . '" /><br><br>';

    echo '<label for="em_event_end_time">Endzeit:</label>';
    echo '<input type="time" id="em_event_end_time" name="em_event_end_time" value="' . esc_attr($end_time) . '" /><br><br>';

    echo '<label for="em_event_location">Ort:</label>';
    echo '<input type="text" id="em_event_location" name="em_event_location" value="' . esc_attr($location) . '" /><br><br>';

    echo '<label for="em_event_note">Notiz:</label>';
    echo '<textarea id="em_event_note" name="em_event_note">' . esc_textarea($note) . '</textarea>';
}

// Speichere die Meta-Felder
function em_save_event_meta($post_id) {
    if (array_key_exists('em_event_start_date', $_POST)) {
        update_post_meta($post_id, '_em_event_start_date', $_POST['em_event_start_date']);
    }
    if (array_key_exists('em_event_end_date', $_POST)) {
        update_post_meta($post_id, '_em_event_end_date', $_POST['em_event_end_date']);
    }
    if (array_key_exists('em_event_start_time', $_POST)) {
        update_post_meta($post_id, '_em_event_start_time', $_POST['em_event_start_time']);
    }
    if (array_key_exists('em_event_end_time', $_POST)) {
        update_post_meta($post_id, '_em_event_end_time', $_POST['em_event_end_time']);
    }
    if (array_key_exists('em_event_location', $_POST)) {
        update_post_meta($post_id, '_em_event_location', $_POST['em_event_location']);
    }
    if (array_key_exists('em_event_note', $_POST)) {
        update_post_meta($post_id, '_em_event_note', $_POST['em_event_note']);
    }
}
add_action('save_post', 'em_save_event_meta');

// Shortcode für die Anzeige der Events
function em_display_events_shortcode() {
    $query = new WP_Query([
        'post_type' => 'em_event',
        'posts_per_page' => -1,
        'orderby' => 'meta_value',
        'meta_key' => '_em_event_start_date',
        'order' => 'ASC',
    ]);

    $output = '<div class="em-event-list">';
    while ($query->have_posts()) {
        $query->the_post();
        $start_date = get_post_meta(get_the_ID(), '_em_event_start_date', true);
        $end_date = get_post_meta(get_the_ID(), '_em_event_end_date', true);
        $start_time = get_post_meta(get_the_ID(), '_em_event_start_time', true);
        $end_time = get_post_meta(get_the_ID(), '_em_event_end_time', true);
        $location = get_post_meta(get_the_ID(), '_em_event_location', true);
        $note = get_post_meta(get_the_ID(), '_em_event_note', true);

        $output .= '<div class="em-event-item">';
        $output .= '<div class="em-event-calendar-icon">';
        $output .= '<div class="em-event-day">' . date_i18n('d', strtotime($start_date)) . '</div>';
        $output .= '<div class="em-event-month">' . date_i18n('M', strtotime($start_date)) . '</div>';
        $output .= '</div>';
        $output .= '<div class="em-event-details">';
        $output .= '<strong>' . get_the_title() . '</strong><br>';
        $output .= 'Start: ' . date_i18n('d.m.Y', strtotime($start_date)) . ' um ' . esc_html($start_time) . '<br>';
        if (!empty($end_date)) {
            $output .= 'Ende: ' . date_i18n('d.m.Y', strtotime($end_date)) . ' um ' . esc_html($end_time) . '<br>';
        }
        if (!empty($location)) {
            $output .= 'Ort: ' . esc_html($location) . '<br>';
        }
        if (!empty($note)) {
            $output .= 'Notiz: ' . esc_html($note) . '<br>';
        }
        $output .= '</div></div>';
    }
    $output .= '</div>';
    wp_reset_postdata();

    return $output;
}
add_shortcode('em_events', 'em_display_events_shortcode');

// CSS laden
function em_enqueue_styles() {
    wp_enqueue_style('em_styles', plugins_url('style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'em_enqueue_styles');