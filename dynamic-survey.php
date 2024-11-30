<?php
/**
 * Plugin Name: Dynamic Survey and Results
 * Description: Create dynamic surveys and display results visually.
 * Version: 1.0.0
 * Author: Codexpert
 * Text Domain: dynamic-survey 
 * Domain Path: /languages
 * Tested up to: 6.2
 *
 *
 * @package Dynamic Survey
 */


 if( !defined('ABSPATH')){
    exit;
 }

 require_once __DIR__ .'/vendor/autoload.php';

/**
 * The main plugin class
 */
final class DynamicSurveyPlugin {

    private static $instance = null;
    /**
     * class constructor
     */
    private function __construct() {
        $this->register_autoloader();
        $this->initialize_classes();
        register_activation_hook(__FILE__, [$this, 'activate']);
         add_action('wp_ajax_submit_survey_vote',  'submit_vote');
        add_action('wp_ajax_nopriv_submit_survey_vote','restrict_non_logged_in');


        add_action('wp_enqueue_scripts', [$this, 'enqueue_dynamic_survey_scripts']);
        
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function register_autoloader() {
        spl_autoload_register(function ($class) {
            $prefix = 'DynamicSurvey\\';
            $base_dir = __DIR__ . '/includes/';
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) return;

            $relative_class = substr($class, $len);
            $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

            if (file_exists($file)) {
                require_once $file;
            }
        });
    }

    private function initialize_classes() {
        new DynamicSurvey\Admin\Admin();
        new DynamicSurvey\Frontend\Shortcode();
    }

    public function activate() {
        DynamicSurvey\Installer\Migrations::create_tables();
    }
    
    function enqueue_dynamic_survey_scripts() {
    wp_enqueue_script('jquery');

    // Enqueue your custom script
    wp_enqueue_script(
        'dynamic-survey-script', // Handle
        plugin_dir_url(__FILE__) . 'assets/js/dynamic-survey.js', // Path to your JS file
        ['jquery'], // Dependencies
        '1.0.0', // Version
        true // Load in footer
    );
        // Enqueue Chart.js
    wp_enqueue_script(
        'chartjs', 
        'https://cdn.jsdelivr.net/npm/chart.js', 
        [], // No dependencies
        '3.7.0', // Version
        true // Load in footer
    );
    // Localize script to pass AJAX URL and nonce
    wp_localize_script('dynamic-survey-script', 'dynamicSurvey', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('submit_survey_vote_nonce')
    ]);
}
}

DynamicSurveyPlugin::get_instance();


