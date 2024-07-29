<?php
/*
Plugin Name: Marquee News
Description: Mit diesem Plugin können benuzerdefinierte Lauftexte mit dem Shortcode '[display_marquee_news text_color="#ed605c" continuous="true" custom_variable="Hier ist der laufende Text."]' anzeigen lassen.
Version: 5.0.2 (18.03.2024)
Author: LordSteini
*/

// Shortcode für die Anzeige von News mit Lauftext und Farbwahl
function display_marquee_news_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'text_color' => '#ed605c', // Standardfarbe ist Rot
            'continuous' => 'true', // Standardmäßig  lückenlos
            'custom_variable' => 'Hier ist der laufende Text.' // Standard-CSS-Variable
        ),
        $atts,
        'display_marquee_news'
    );

    // Hole alle News mit einem Enddatum größer oder gleich dem aktuellen Datum
    $current_date = date('Y-m-d');
    $args = array(
        'post_type' => 'marquee_news',
        'meta_query' => array(
            array(
                'key' => 'marquee_end_date',
                'value' => $current_date,
                'compare' => '>=',
                'type' => 'DATE',
            ),
        ),
    );
    $marquee_news_query = new WP_Query($args);

    // Wenn es News gibt, zeige sie als Lauftext an
    if ($marquee_news_query->have_posts()) {
        ob_start(); // Starte den Output Buffer

        // Farbe und Lückenlos-Optionen
        $text_color = sanitize_hex_color($atts['text_color']);
        $continuous = $atts['continuous'] === 'true' ? 'infinite' : '1';
        $custom_variable = $atts['custom_variable'];

        echo '<div class="marquee-news-container">';
        echo '<marquee behavior="scroll" direction="left" scrollamount="5" style="--laufender-text: \'' . $custom_variable . '\'; color:' . $text_color . ';" loop="' . $continuous . '">'; // Anpassung für Farbwahl, Lückenlos und benutzerdefinierte CSS-Variable
        while ($marquee_news_query->have_posts()) {
            $marquee_news_query->the_post();
            echo '<div class="marquee-news-item">';
            echo '<h2>' . get_the_title() . '</h2>';
            echo '<p>' . get_the_content() . '</p>';
            echo '</div>';
        }
        echo '</marquee>';
        echo '</div>';
        wp_reset_postdata();
        return ob_get_clean(); // Gib den Buffer-Inhalt zurück
    } else {
        // Wenn es keine News gibt, zeige nichts an
        return '';
    }
}
add_shortcode('display_marquee_news', 'display_marquee_news_shortcode');

// Registriere den Marquee News Custom Post Type
function register_marquee_news_post_type() {
    $labels = array(
        'name' => 'Marquee News',
        'singular_name' => 'Marquee News',
        'menu_name' => 'Marquee News',
    );

    $args = array(
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => array('title', 'editor'),
    );

    register_post_type('marquee_news', $args);
}
add_action('init', 'register_marquee_news_post_type');

// Füge ein benutzerdefiniertes Meta-Feld für das Enddatum hinzu
function add_marquee_end_date_meta_box() {
    add_meta_box(
        'marquee_end_date_meta_box',
        'Enddatum',
        'display_marquee_end_date_meta_box',
        'marquee_news',
        'side'
    );
}
add_action('add_meta_boxes', 'add_marquee_end_date_meta_box');

function display_marquee_end_date_meta_box($post) {
    $marquee_end_date = get_post_meta($post->ID, 'marquee_end_date', true);
    ?>
    <label for="marquee_end_date">Enddatum:</label>
    <input type="date" id="marquee_end_date" name="marquee_end_date" value="<?php echo esc_attr($marquee_end_date); ?>">
    <?php
}

// Füge ein benutzerdefiniertes Meta-Feld für die Farbwahl hinzu
function add_marquee_text_color_meta_box() {
    add_meta_box(
        'marquee_text_color_meta_box',
        'Textfarbe',
        'display_marquee_text_color_meta_box',
        'marquee_news',
        'side'
    );
}
add_action('add_meta_boxes', 'add_marquee_text_color_meta_box');

function display_marquee_text_color_meta_box($post) {
    $marquee_text_color = get_post_meta($post->ID, 'marquee_text_color', true);
    ?>
    <label for="marquee_text_color">Textfarbe:</label>
    <input type="color" id="marquee_text_color" name="marquee_text_color" value="<?php echo esc_attr($marquee_text_color); ?>">
    <?php
}

// Füge ein benutzerdefiniertes Meta-Feld für die benutzerdefinierte CSS-Variable hinzu
function add_marquee_custom_variable_meta_box() {
    add_meta_box(
        'marquee_custom_variable_meta_box',
        'Benutzerdefinierte CSS-Variable',
        'display_marquee_custom_variable_meta_box',
        'marquee_news',
        'side'
    );
}
add_action('add_meta_boxes', 'add_marquee_custom_variable_meta_box');

function display_marquee_custom_variable_meta_box($post) {
    $marquee_custom_variable = get_post_meta($post->ID, 'marquee_custom_variable', true);
    ?>
    <label for="marquee_custom_variable">Benutzerdefinierte CSS-Variable:</label>
    <input type="text" id="marquee_custom_variable" name="marquee_custom_variable" value="<?php echo esc_attr($marquee_custom_variable); ?>">
    <?php
}

// Speichere die benutzerdefinierten Meta-Felder
function save_marquee_end_date_meta_data($post_id) {
    if (array_key_exists('marquee_end_date', $_POST)) {
        update_post_meta(
            $post_id,
            'marquee_end_date',
            sanitize_text_field($_POST['marquee_end_date'])
        );
    }
    if (array_key_exists('marquee_text_color', $_POST)) {
        update_post_meta(
            $post_id,
            'marquee_text_color',
            sanitize_hex_color($_POST['marquee_text_color'])
        );
    }
    if (array_key_exists('marquee_custom_variable', $_POST)) {
        update_post_meta(
            $post_id,
            'marquee_custom_variable',
            sanitize_text_field($_POST['marquee_custom_variable'])
        );
    }
}
add_action('save_post', 'save_marquee_end_date_meta_data');
?>
