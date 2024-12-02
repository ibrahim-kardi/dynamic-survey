<div class="wrap">
	<h1><?php esc_html_e( 'Edit Survey', 'dynamic-survey' ); ?></h1>

	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="save_survey">
		<input type="hidden" name="survey_id" value="<?php echo esc_attr( $survey->id ); ?>">
		<?php wp_nonce_field( 'save_survey_nonce' ); ?>

		<p>
			<label for="question"><strong><?php esc_html_e( 'Question:', 'dynamic-survey' ); ?></strong></label> <br>
			<input type="text" name="question" id="question" class="regular-text" value="<?php echo esc_attr( $survey->question ); ?>" required>
		</p>
		<p>
			<label for="question_type"><strong><?php esc_html_e( 'Question Type:', 'dynamic-survey' ); ?></strong></label><br>
			<select name="question_type" id="question_type" required>
				<option value="choice" <?php selected( $survey->type, 'choice' ); ?>><?php esc_html_e( 'Multiple Choice', 'dynamic-survey' ); ?></option>
				<option value="text" <?php selected( $survey->type, 'text' ); ?>><?php esc_html_e( 'Text', 'dynamic-survey' ); ?></option>
			</select>
		</p>
		<div id="options-container">
			<label for="options"><strong><?php esc_html_e( 'Options (one per line):', 'dynamic-survey' ); ?></strong></label><br>
			<textarea name="options" id="options" rows="4" cols="50">
			<?php
				$options = maybe_unserialize( $survey->options );
				echo is_array( $options ) ? esc_textarea( implode( "\n", $options ) ) : '';
			?>
			</textarea>
		</div>
		<button type="submit" class="button button-primary"><?php esc_html_e( 'Save Survey', 'dynamic-survey' ); ?></button>
	</form>
</div>
