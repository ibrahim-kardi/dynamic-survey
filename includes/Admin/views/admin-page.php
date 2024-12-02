<div class="wrap">
	<h1><?php esc_html_e( 'Dynamic Survey', 'dynamic-survey' ); ?></h1>

	<h2><?php esc_html_e( 'Create a New Survey', 'dynamic-survey' ); ?></h2>
	<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<input type="hidden" name="action" value="save_survey">
		<?php wp_nonce_field( 'save_survey_nonce' ); ?>

		<p>
			<label for="question"><strong><?php esc_html_e( 'Question:', 'dynamic-survey' ); ?></strong></label><br>
		
			<input type="text" name="question" id="question" class="regular-text" required>
		</p>
		<p><label for="question_type"><strong><?php esc_html_e( 'Question Type:', 'dynamic-survey' ); ?></strong></label><br>
		
			<select name="question_type" id="question_type" required>
				<option value="choice"><?php esc_html_e( 'Multiple Choice', 'dynamic-survey' ); ?></option>
				<option value="text"><?php esc_html_e( 'Text', 'dynamic-survey' ); ?></option>
			</select>
		</p>
		<p id="options-container">
			<label for="options"><strong><?php esc_html_e( 'Options (comma separated):', 'dynamic-survey' ); ?></strong></label><br>
			<textarea name="options" id="options" rows="4" cols="50"></textarea>
		</p>
		<button type="submit" class="button button-primary"><?php esc_html_e( 'Create Survey', 'dynamic-survey' ); ?></button>
	</form>

	<h2><?php esc_html_e( 'Existing Surveys', 'dynamic-survey' ); ?></h2>
	<table class="widefat fixed">
		<thead>
			<tr>
				<th><?php esc_html_e( 'ID', 'dynamic-survey' ); ?></th>
				<th><?php esc_html_e( 'Question', 'dynamic-survey' ); ?></th>
				<th><?php esc_html_e( 'Type', 'dynamic-survey' ); ?></th>
				<th><?php esc_html_e( 'Shortcode', 'dynamic-survey' ); ?></th>
				<th><?php esc_html_e( 'Options', 'dynamic-survey' ); ?></th>
				<th><?php esc_html_e( 'Actions', 'dynamic-survey' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php if ( ! empty( $surveys ) ) : ?>
				<?php foreach ( $surveys as $survey ) : ?>
					<tr>
						<td><?php echo esc_html( $survey->id ); ?></td>
						<td><?php echo esc_html( $survey->question ); ?></td>
						<td><?php echo esc_html( $survey->type ); ?></td>
						<td>[dynamic_survey id="<?php echo esc_html( $survey->id ); ?>"]</td>
						<td>
							<?php
							$options = maybe_unserialize( $survey->options );
							echo is_array( $options ) ? implode( ', ', array_map( 'esc_html', $options ) ) : esc_html__( 'N/A', 'dynamic-survey' );
							?>
						</td>
						<td>
							<?php if ( current_user_can( 'manage_options' ) ) : ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=edit-dynamic-survey&survey_id=' . $survey->id ) ); ?>" class="button"><?php esc_html_e( 'Edit', 'dynamic-survey' ); ?></a>
							<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=delete_survey&survey_id=' . $survey->id ), 'delete_survey_nonce' ) ); ?>" class="button button-danger" onclick="return confirm('<?php esc_html_e( 'Are you sure?', 'dynamic-survey' ); ?>');"><?php esc_html_e( 'Delete', 'dynamic-survey' ); ?></a>
							
							<a href="<?php echo esc_url( admin_url( 'admin-ajax.php?action=export_survey_results&survey_id=' . esc_attr( $survey->id ) ) ); ?>" 
							class="button button-primary">
								<?php esc_html_e( 'Export Results to CSV', 'dynamic-survey' ); ?>
							</a>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="5"><?php esc_html_e( 'No surveys found.', 'dynamic-survey' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>
</div>
