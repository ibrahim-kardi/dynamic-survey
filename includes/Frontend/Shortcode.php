<?php
namespace DynamicSurvey\Frontend;
class Shortcode {

    public function __construct() {
        add_shortcode('dynamic_survey', [$this, 'render_survey_shortcode']);
        add_action('wp_ajax_submit_survey_vote', [$this, 'submit_vote']);
        add_action('wp_ajax_nopriv_submit_survey_vote', [$this, 'restrict_non_logged_in']);
    }

 

public function render_survey_shortcode($atts) {
    $atts = shortcode_atts(['id' => 0], $atts, 'dynamic_survey');
    $survey_id = intval($atts['id']);

    if (!$survey_id) {
        return '<p>Invalid survey ID.</p>';
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'dynamic_surveys';
    $survey = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $survey_id));

    if (!$survey) {
        return '<p>Survey not found.</p>';
    }

    $user_id = get_current_user_id();
    $votes_table = $wpdb->prefix . 'dynamic_survey_votes';
    $has_voted = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $votes_table WHERE survey_id = %d AND user_id = %d",
        $survey_id,
        $user_id
    ));

    if ($has_voted) {
        return $this->render_results($survey_id);
    }

    $options = maybe_unserialize($survey->options);
    if (is_array($options) && count($options) === 1 && is_string($options[0])) {
        $options = explode(',', $options[0]); // Handle single-string options
    }

    ob_start();
    ?>
    <form id="dynamic-survey-form" data-survey-id="<?php echo esc_attr($survey_id); ?>">
        <h3><?php echo esc_html($survey->question); ?></h3>
        <div>
            <?php foreach ($options as $option): ?>
                <p>
                    <label>
                        <input type="radio" name="survey_option" value="<?php echo esc_attr(trim($option)); ?>" required>
                        <?php echo esc_html(trim($option)); ?>
                    </label>
                </p>
            <?php endforeach; ?>
        </div>
        <button type="submit">Submit</button>
    </form>
    <div id="survey-message"></div>

    <?php
    return ob_get_clean();
}


public function submit_vote() {
    error_log('AJAX request received'); // Debug log

    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in to vote.']);
    }

    check_ajax_referer('submit_survey_vote_nonce', '_ajax_nonce');

    global $wpdb;
    $table_name = $wpdb->prefix . 'dynamic_survey_votes';
    $survey_id = intval($_POST['survey_id']);
    $option = sanitize_text_field($_POST['option']);
    $user_id = get_current_user_id();

    error_log("Survey ID: $survey_id, Option: $option, User ID: $user_id"); // Debug log

    // Check if user has already voted
    $has_voted = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE survey_id = %d AND user_id = %d",
        $survey_id,
        $user_id
    ));

    if ($has_voted) {
        wp_send_json_error(['message' => 'You have already voted in this survey.']);
    }

    // Insert the vote
        $wpdb->insert(
            $table_name,
            [
                'survey_id' => $survey_id,
                'user_id' => $user_id,
                'option_selected' => $option, // Updated column name
            ]
        );

    // Return the updated results
    $html = $this->render_results($survey_id);
    wp_send_json_success(['message' => 'Thank you for voting!', 'html' => $html]);
}

    private function render_results($survey_id) {
        global $wpdb;
        $votes_table = $wpdb->prefix . 'dynamic_survey_votes';

        // Get vote counts
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT option_selected, COUNT(*) as count FROM $votes_table WHERE survey_id = %d GROUP BY option_selected",
            $survey_id
        ));


        if (empty($results)) {
            return '<p>No votes have been cast yet.</p>';
        }

        // Prepare data for chart
        $data = [
            'labels' => [],
            'counts' => [],
        ];

        foreach ($results as $result) {
            $data['labels'][] = $result->option_selected;
            $data['counts'][] = $result->count;
        }

        ob_start();
        ?>
        <canvas id="survey-results-chart"></canvas>
        <script>
            (function() {
                const ctx = document.getElementById('survey-results-chart').getContext('2d');
                console.log(<?php echo json_encode($data['labels']); ?>);   
                new Chart(ctx, {
                    type: 'pie', // Change to 'bar' for bar chart
                    data: {
                        labels: <?php echo json_encode($data['labels']); ?>,
                        datasets: [{
                            label: 'Votes',
                            data: <?php echo json_encode($data['counts']); ?>,
                            backgroundColor: ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'],
                        }]
                    }
                });
            })();
        </script>
        <?php
        return ob_get_clean();
    }

    public function restrict_non_logged_in() {
        wp_send_json_error(['message' => 'You must be logged in to vote.']);
    }

}

new Shortcode();
