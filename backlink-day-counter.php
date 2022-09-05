<?php
/**
 * Plugin Name: Backlink Day Counter
 * Plugin URI: https://iltekin.com/projects/backlink-day-counter-wordpress-plugin
 * Description: By using [link] shortcode after backlink, Administrators can see remaining days of a sponsored backlink next to it.
 * Version: 1.0.0
 * Text Domain: backlink-day-counter
 * Author: Sezer Iltekin
 * Author URI: https://iltekin.com
 */

function bdc_css_and_js(){
    wp_enqueue_script('bdc-scripts', plugins_url('js/scripts.js',__FILE__ ), array('jquery'));
    wp_enqueue_style( 'bdc-style', plugins_url('css/style.css', __FILE__));
}

add_action('wp_enqueue_scripts', "bdc_css_and_js");

$bdc_language = get_option('bdc_lang_option');

$bdc_words = array();

switch ($bdc_language){
    case "en":
        $bdc_words['start']     = "start";
        $bdc_words['end']       = "end";
        $bdc_words['remaining'] = "remaining";
        $bdc_words['duration']  = "duration";
        $bdc_words['days']      = "days";
        $bdc_words['keyword']   = "keyword";
        $bdc_words['plain']     = "plain";
        break;
    case "tr":
        $bdc_words['start']     = "baslangic";
        $bdc_words['end']       = "bitis";
        $bdc_words['remaining'] = "kalan";
        $bdc_words['duration']  = "sure";
        $bdc_words['days']      = "gün";
        $bdc_words['keyword']   = "kelime";
        $bdc_words['plain']     = "detaysiz";
        break;
}

function createBacklink($arr) {
    global $bdc_words;
    if(array_key_exists('url', $arr) AND array_key_exists($bdc_words['keyword'], $arr)){
        $link = '<a href="' . $arr['url'] . '" ';

        if(array_key_exists('target', $arr)){
            $link .= 'target="' . $arr['target'] . '" ';
        }

        if(array_key_exists('title', $arr)){
            $link .= 'title="' . $arr['title'] . '" ';
        }

        if(array_key_exists('rel', $arr)){
            $link .= 'rel="' . $arr['rel'] . '" ';
        }

        if(array_key_exists('id', $arr)){
            $link .= 'id="' . $arr['id'] . '" ';
        }

        if(array_key_exists('class', $arr)){
            $link .= 'class="' . $arr['class'] . '" ';
        }

        $link .= '>' . $arr[$bdc_words['keyword']] . '</a>';
    }

    if ( current_user_can('manage_options') ) {

        $valid = false;

        if (array_key_exists($bdc_words['end'], $arr)) {
            $today = strtotime(Date("d.m.Y"));
            $end = strtotime($arr[$bdc_words['end']]);
            $remaining = ($end - $today) / (60*60*24);
            if(is_int($remaining)){
                $arr[$bdc_words['remaining']] = $remaining . " " . $bdc_words['days'];
                $valid = true;
            }
        }

        else if (array_key_exists($bdc_words['start'], $arr) AND array_key_exists($bdc_words['duration'], $arr)) {
            $today = strtotime(Date("d.m.Y"));
            $start = strtotime($arr[$bdc_words['start']]);
            $addition = $arr[$bdc_words['duration']] * (60*60*24);

            $end = $start + $addition;

            $remaining = ($end - $today) / (60*60*24);
            if(is_int($remaining)){
                $arr[$bdc_words['end']] = date('d.m.Y', $end);
                $arr[$bdc_words['remaining']] = $remaining . " " . $bdc_words['days'];
                $valid = true;
            }
        }

        if(!$valid){
            $remaining = "??";
        }

        if($remaining > 7){
            $bdc_class = "bdc_green";
        } else if($remaining > 0){
            $bdc_class = "bdc_yellow";
        } else {
            $bdc_class = "bdc_red";
        }

        $keys = '<div class="bdc_keys">';
        $values = '<div class="bdc_values">';
        foreach($arr as $key => $value){
            $keys .= "<div>" . $key . ":</div>";
            $values .= "<div>" . $value . "</div>";
        }
        $keys .= '</div>';
        $values .= '</div>';

        $result = '<div class="bdc_div '. $bdc_class . '">' . $remaining . '</div><div class="bdc_details"><div class="bdc_flex">' . $keys . $values . '</div></div>';

        if(isset($link)){
            $result = $link . $result;
        }

        if (array_key_exists($bdc_words['plain'], $arr)) {
            if(isset($link)){
                return $link;
            } else {
                return false;
            }
        } else {
            return $result;
        }

    } else {

        if(isset($link)){
            return $link;
        } else {
            return false;
        }

    }
}

add_shortcode('link', 'createBacklink');

add_action( 'admin_menu', 'custom_options_page' );

function custom_options_page() {

    add_options_page(
        'Backlink Day Counter Settings', // page title
        'Backlink Day Counter Settings', // menu title
        'manage_options', // capability to access the page
        'bdc-settings-page-slug', // menu slug
        'bdc_settings_page_content', // callback function
        5 // position
    );

}

function bdc_settings_page_content() {
    ?>
    <div class="wrap">
        <h1>Backlink Day Counter Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'bdc_group' ); // settings group name
            do_settings_sections( 'bdc-settings-page-slug' ); // a page slug
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action( 'admin_init',  'bdc_register_settings' );

function bdc_register_settings() {

    add_settings_section(
        'homepage_section', // section ID
        '', // title
        '', // callback function
        'bdc-settings-page-slug' // page slug
    );

    // first field
    add_settings_field(
        'bdc_lang_option',
        'Language',
        'bdc_lang_option_field_html', // function which prints the field
        'bdc-settings-page-slug', // page slug
        'homepage_section', // section ID
        array(
            'label_for' => 'bdc_lang_option',
            'class' => 'bdc_lang_option',
        )
    );

    register_setting(
        'bdc_group', // settings group name
        'bdc_lang_option', 	// field name
        'sanitize_text_field' // sanitization function
    );

}

function bdc_lang_option_field_html() {

    $lang_options = [
        ['code' => 'en', 'title' => 'English'],
        ['code' => 'tr', 'title' => 'Türkçe']
    ];

    $bdc_lang_option = get_option('bdc_lang_option');
    echo '<select id="bdc_lang_option" name="bdc_lang_option">';
    foreach($lang_options as $lo){
        echo '<option';
        if($lo['code'] == $bdc_lang_option){ echo ' selected'; }
        echo ' value="' . $lo['code'] . '">' . $lo['title'] . '</option>';
    }
    echo '</select>';
}

add_filter( 'plugin_action_links_backlink-day-counter/backlink-day-counter.php', 'bdc_settings_link' );
function bdc_settings_link( $links ) {
    // Build and escape the URL.
    $url = esc_url( add_query_arg(
        'page',
        'bdc-settings-page-slug',
        get_admin_url() . 'options-general.php'
    ) );
    // Create the link.
    $settings_link = "<a href='$url'>" . __( 'Settings' ) . '</a>';
    // Adds the link to the end of the array.
    array_push(
        $links,
        $settings_link
    );
    return $links;
}