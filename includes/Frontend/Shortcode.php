<?php
namespace DynamicSurvey\Frontend;

class Shortcode {

	public function __construct() {
		add_shortcode( 'dynamic_survey', [ $this, 'render_survey_shortcode' ] );
		add_action( 'wp_ajax_submit_survey_vote', [ $this, 'submit_vote' ] );
		add_action( 'wp_ajax_nopriv_submit_survey_vote', [ $this, 'restrict_non_logged_in' ] );
		add_action( 'wp_ajax_export_survey_results', [ $this, 'export_survey_results' ] );
	}

	/**
	 * Renders the survey shortcode
	 */
	public function render_survey_shortcode( $atts ) {
		$atts      = shortcode_atts( [ 'id' => 0 ], $atts, 'dynamic_survey' );
		$survey_id = intval( $atts['id'] );

		if ( ! $survey_id ) {
			return '<p>Invalid survey ID.</p>';
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'dynamic_surveys';
		$survey     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $survey_id ) );

		if ( ! $survey ) {
			return '<p>Survey not found.</p>';
		}

		// Enqueue the script only when rendering
		wp_enqueue_script( 'dynamic-survey-script' );
		wp_localize_script(
			'dynamic-survey-script',
			'surveyData',
			[
				'surveyType' => $survey->type,  // Pass the survey type to JS
				'ajax_url'   => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( 'submit_survey_vote_nonce' ),
			]
		);

		$user_id     = get_current_user_id();
		$votes_table = $wpdb->prefix . 'dynamic_survey_votes';
		$has_voted   = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$votes_table} WHERE survey_id = %d AND user_id = %d",
				$survey_id,
				$user_id
			)
		);

		if ( $has_voted ) {
			return $this->render_results( $survey_id );
		}

		$options = maybe_unserialize( $survey->options );
		if ( is_array( $options ) && 1 === count( $options ) && is_string( $options[0] ) ) {
			$options = explode( ',', $options[0] );
		}

		ob_start();
		?>
		<form id="dynamic-survey-form" data-survey-id="<?php echo esc_attr( $survey_id ); ?>"  data-survey-type="<?php echo esc_attr( $survey->type ); ?>">
			<h3><?php echo esc_html( $survey->question ); ?></h3>
			<div>
				<?php if ( 'choice' === $survey->type ) : ?>
					<?php foreach ( $options as $option ) : ?>
						<p>
							<label>
								<input type="radio" name="survey_option" value="<?php echo esc_attr( trim( $option ) ); ?>" required>
								<?php echo esc_html( trim( $option ) ); ?>
							</label>
						</p>
					<?php endforeach; ?>
				<?php elseif ( 'text' === $survey->type ) : ?>
					<textarea name="survey_option" placeholder="Your answer" required></textarea>
				<?php endif; ?>
			</div>
			<button type="submit" class="submit-btn">Submit</button>
		</form>
		<div id="survey-message"></div>
		<?php
		return ob_get_clean();
	}

	public function submit_vote() {
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( [ 'message' => 'You must be logged in to vote.' ] );
		}

		check_ajax_referer( 'submit_survey_vote_nonce', '_ajax_nonce' );

		$survey_id = isset( $_POST['survey_id'] ) ? intval( $_POST['survey_id'] ) : 0;
		$option    = isset( $_POST['option'] ) ? sanitize_text_field( $_POST['option'] ) : '';
		$user_id   = get_current_user_id();

		if ( ! $survey_id || ! $option ) {
			wp_send_json_error( [ 'message' => 'Invalid input.' ] );
		}

		global $wpdb;
		$votes_table = $wpdb->prefix . 'dynamic_survey_votes';

		$inserted = $wpdb->insert(
			$votes_table,
			[
				'survey_id'       => $survey_id,
				'user_id'         => $user_id ?: null,
				'ip_address'      => $user_id ? null : $_SERVER['REMOTE_ADDR'],
				'option_selected' => $option,
			],
			[ '%d', '%d', '%s', '%s' ]
		);

		if ( false === $inserted ) {
			wp_send_json_error( [ 'message' => 'Failed to submit vote.' ] );
		}

		$response = [
			'success' => true,
			'data'    => [
				'message' => 'Thank you for voting!',
				'html'    => $this->render_results( $survey_id ),
			],
		];

		wp_send_json( $response );
	}

	private function render_results( $survey_id ) {
		global $wpdb;
		$votes_table = $wpdb->prefix . 'dynamic_survey_votes';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_selected, COUNT(*) as count FROM {$votes_table} WHERE survey_id = %d GROUP BY option_selected",
				$survey_id
			)
		);

		if ( empty( $results ) ) {
			return '<p>No votes have been cast yet.</p>';
		}

		$data = [
			'labels' => [],
			'counts' => [],
		];

		foreach ( $results as $result ) {
			$data['labels'][] = $result->option_selected;
			$data['counts'][] = $result->count;
		}

		ob_start();
		?>
		<canvas id="survey-results-chart"></canvas>
		<script>
			(function() {
				const ctx = document.getElementById('survey-results-chart').getContext('2d');
				new Chart(ctx, {
					type: 'pie',
					data: {
						labels: <?php echo wp_json_encode( $data['labels'] ); ?>,
						datasets: [{
							label: 'Votes',
							data: <?php echo wp_json_encode( $data['counts'] ); ?>,
							backgroundColor: [
								'#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF', '#FF9F40'
							],
							borderWidth: 1
						}]
					},
					options: {
						responsive: true,
						plugins: {
							legend: {
								position: 'top',
							},
						},
					}
				});
			})();
		</script>
		<?php
		return ob_get_clean();
	}

	public function restrict_non_logged_in() {
		wp_send_json_error( [ 'message' => 'You must be logged in to vote.' ] );
	}

	/**
	 * Export survey results as CSV
	 */
	public function export_survey_results() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( [ 'message' => 'Unauthorized access.' ] );
		}

		$survey_id = isset( $_GET['survey_id'] ) ? intval( $_GET['survey_id'] ) : 0;
		if ( ! $survey_id ) {
			wp_send_json_error( [ 'message' => 'Invalid survey ID.' ] );
		}

		global $wpdb;
		$votes_table = $wpdb->prefix . 'dynamic_survey_votes';

		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT option_selected, COUNT(*) as count FROM $votes_table WHERE survey_id = %d GROUP BY option_selected",
				$survey_id
			)
		);

		if ( empty( $results ) ) {
			wp_send_json_error( [ 'message' => 'No results found for the survey.' ] );
		}

		// Generate CSV content
		$csv_data   = [];
		$csv_data[] = [ 'Option', 'Votes' ];
		foreach ( $results as $row ) {
			$csv_data[] = [ $row->option_selected, $row->count ];
		}

		// Send headers and output CSV
		header( 'Content-Type: text/csv' );
		header( 'Content-Disposition: attachment; filename="survey_results.csv"' );
		$output = fopen( 'php://output', 'w' );
		foreach ( $csv_data as $line ) {
			fputcsv( $output, $line );
		}
		fclose( $output );
		exit;
	}
}

new Shortcode();
