<?php
namespace DynamicSurvey\Installer;

class Migrations {

   public static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();
        $survey_table = $wpdb->prefix . 'dynamic_surveys';
        $votes_table = $wpdb->prefix . 'dynamic_survey_votes';

        $sql = "
            CREATE TABLE $survey_table (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                question TEXT NOT NULL,
                options TEXT NOT NULL,
                status ENUM('open', 'closed') DEFAULT 'open',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            ) $charset_collate;

            CREATE TABLE $votes_table (
                id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                survey_id BIGINT(20) UNSIGNED NOT NULL,
                user_id BIGINT(20) UNSIGNED NULL,
                ip_address VARCHAR(45) NULL,
                option_selected TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE (survey_id, user_id),
                FOREIGN KEY (survey_id) REFERENCES $survey_table(id) ON DELETE CASCADE
            ) $charset_collate;
        ";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }
}
