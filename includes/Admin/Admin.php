<?php
namespace DynamicSurvey\Admin;

class Admin {

	public function __construct() {
		add_action( 'admin_menu', [ $this, 'add_admin_pages' ] );
		add_action( 'admin_post_save_survey', [ $this, 'save_survey' ] );
		add_action( 'admin_post_delete_survey', [ $this, 'delete_survey' ] );
	}

	public function add_admin_pages() {
		add_submenu_page(
			'tools.php',
			esc_html__( 'Dynamic Survey', 'dynamic-survey' ),
			esc_html__( 'Dynamic Survey', 'dynamic-survey' ),
			'manage_options',
			'dynamic-survey',
			[ $this, 'render_admin_page' ]
		);

		// Edit Survey Page
		add_submenu_page(
			null, // Hidden from menu
            esc_html__( 'Edit Survey', 'dynamic-survey' ),
			 esc_html__( 'Edit Survey', 'dynamic-survey' ),
			'manage_options',
			'edit-dynamic-survey',
			[ $this, 'render_edit_page' ]
		);
	}

	public function render_admin_page() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'dynamic_surveys';
		$surveys    = $wpdb->get_results( "SELECT * FROM {$table_name}" );

		include plugin_dir_path( __FILE__ ) . 'views/admin-page.php';
	}

	public function render_edit_page() {
		if ( ! isset( $_GET['survey_id'] ) ) {
			wp_die( 'Survey ID is missing.' );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'dynamic_surveys';
		$survey_id  = intval( $_GET['survey_id'] );
		$survey     = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table_name} WHERE id = %d", $survey_id ) );

		if ( ! $survey ) {
			wp_die( 'Survey not found.' );
		}

		include plugin_dir_path( __FILE__ ) . 'views/edit-survey.php';
	}

	public function save_survey() {
		if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'save_survey_nonce' ) ) {
			wp_die( 'Unauthorized action.' );
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'dynamic_surveys';

		$question = sanitize_text_field( $_POST['question'] );
		$options  = isset( $_POST['options'] ) ? explode( "\n", sanitize_textarea_field( $_POST['options'] ) ) : [];
		$type     = isset( $_POST['question_type'] ) ? sanitize_text_field( $_POST['question_type'] ) : 'choice';

		if ( ! empty( $options ) ) {
			$options            = array_map( 'trim', $options );
			$options_serialized = maybe_serialize( $options );
		} else {
			$options_serialized = maybe_serialize( [] );
		}

		if ( ! empty( $_POST['survey_id'] ) ) {
			$survey_id = absint( $_POST['survey_id'] );
			$wpdb->update(
				$table_name,
				[
					'question' => $question,
					'options'  => $options_serialized,
					'type'     => $type,
				],
				[ 'id' => $survey_id ]
			);
		} else {
			$wpdb->insert(
				$table_name,
				[
					'question' => $question,
					'options'  => $options_serialized,
					'type'     => $type,
				]
			);
		}

		wp_safe_redirect( admin_url( 'tools.php?page=dynamic-survey' ) );
		exit;
	}

	public function delete_survey() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( 'Unauthorized action.' );
		}

		check_admin_referer( 'delete_survey_nonce' );

		global $wpdb;
		$table_name = $wpdb->prefix . 'dynamic_surveys';
		$survey_id  = intval( $_GET['survey_id'] );
		$wpdb->delete( $table_name, [ 'id' => $survey_id ] );

		wp_safe_redirect( admin_url( 'tools.php?page=dynamic-survey' ) );
		exit;
	}
}
