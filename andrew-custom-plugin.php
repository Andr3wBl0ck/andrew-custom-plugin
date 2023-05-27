<?php

/*
Plugin Name: Andrew's Custom Plugin
Description: Simple Plugin
Version: 1.0
Author: Andrew Block
Author URI: https://github.com/
Text Domain: acpdomain
Domain Path: /languages
*/

class WordCountAndTimePlugin {
    function __construct() {
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'settings'));
        add_filter('the_content', array($this, 'ifWrap'));
        add_action('init', array($this, 'languages'));

    }

    function languages() {
        load_plugin_textdomain('acpdomain', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    function ifWrap($content) {
        if(is_main_query() AND is_single() AND 
        (get_option('wcp_wordcount', '1') OR 
        get_option('wcp_charcnt', '1', ) OR
        get_option('wcp_readtime', '1')
        )){
            return $this->createHTML($content);
        }
        return $content;
    }

    function createHTML($content){
        $html = '<h3>' . esc_html(get_option('wcp_headline', 'Post Statistics')) . ' </h3><p>';

        // Calculate word count ONCE because word count and read time will need it
        
        if(get_option('wcp_wordcount', '1') OR get_option('wcp_readtime', '1')) {
            $wordCount = str_word_count(strip_tags($content));
        }

        if(get_option('wcp_wordcount', '1')) {
            $html .= esc_html__('This post has', 'acpdomain') . ' ' . $wordCount . ' ' . __('words', 'acpdomain') . '</br>';
        }

        if(get_option('wcp_charcnt', '1')) {
            $html .=' This post has ' . strlen(strip_tags($content)) . ' characters.</br>';
        }

        if(get_option('wcp_readtime', '1')) {
            $readTime = round($wordCount/225);
            if($readTime == 0){
                $html .=' This post will take about less than a minute to read.' ;
            }else
            $html .=' This post will take about ' . $readTime . ' minute(s) to read.</br>';
        }

    



        if(get_option('wcp_location', '0') == '0') {
            return $html . $content;
        }
        return $content . $html;
    }

    function settings() {
        add_settings_section('wcp_first_section', null, null, 'word-count-settings-page');

        // Select location of display for plugin
        add_settings_field('wcp_location','Display Location: ',array($this, 'locationHTML'),'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin','wcp_location',array('sanitize_callback' => array($this, 'sanitizeLocation' ), 'default' => '0'));

        // Headline Text Setting Field
        add_settings_field('wcp_headline','Headline Text:',array($this, 'headlineHTML'),'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin','wcp_headline',array('sanitize_callback' => 'sanitize_text_field', 'default' => 'Post Statistics'));

        // Check field "WordCount"
        add_settings_field('wcp_wordcount','Word Count:',array($this, 'wordcountHTML'),'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin','wcp_wordcount',array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));

        // Check field "Character Count"
        add_settings_field('wcp_charcnt','Character Count:',array($this, 'charcntHTML'),'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin','wcp_charcnt',array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));


        // Check field "Read Time"
        add_settings_field('wcp_readtime','Read Time: ',array($this, 'readtimeHTML'),'word-count-settings-page', 'wcp_first_section');
        register_setting('wordcountplugin','wcp_readtime',array('sanitize_callback' => 'sanitize_text_field', 'default' => '1'));


    }

    function sanitizeLocation($input) {
        if($input != '0' AND $input != '1') {
            add_settings_error('wcp_location','wcp_location_error', 'Display Location must be beginning or ending');
            return get_option('wcp_location');
        }
        return $input;

    }
    


    function readtimeHTML() {
        ?>
    <input type="checkbox" name="wcp_readtime" value="1" <?php  checked(get_option('wcp_readtime'),1)   ?>>
        <?php
    }
    function charcntHTML() {
        ?>
            <input type="checkbox" name="wcp_charcnt" value="1" <?php  checked(get_option('wcp_charcnt'),1)   ?>>
<?php
    }

    function wordcountHTML() {
        ?>

            <input type="checkbox" name="wcp_wordcount" value="1" <?php  checked(get_option('wcp_wordcount'),1)   ?>>


<?php
    }

    function headlineHTML(){
         ?>

            <input type="text" name="wcp_headline" value="<?php echo esc_attr(get_option('wcp_headline')) ?>">



        <?php
    }

    function locationHTML() {?>
        
        <select name="wcp_location" id="">
            <option value="0" <?php selected(get_option('wcp_location'), '0') ?>>Beginning of Post </option>
            <option value="1" <?php selected(get_option('wcp_location'), '1') ?>>End of Post </option>
        </select>

        <?php
    }



    function adminPage() {

        add_options_page('Word Count Settings', __('Word Count','acpdomain'), 'manage_options','word-count-settings-page', array($this, 'mainHTML'));
    
    }
    
    function mainHTML() { ?>
        
        <div class="wrap">
        <h1>Word Count Settings</h1>
        <form action="options.php" method="POST">
            <?php
                settings_fields('wordcountplugin');
                do_settings_sections('word-count-settings-page');
                submit_button();

            ?>
        </form>


    </div>


        <?php
    
        
    }
}

$wordCountAndTimePlugin = new WordCountAndTimePlugin();

