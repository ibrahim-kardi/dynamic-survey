<?php
$options = maybe_unserialize($survey->options); // Unserialize stored options
$options_text = is_array($options) ? implode("\n", $options) : ''; // Convert to newline-separated string
?>
    <div class="wrap">
        <h1>Edit Survey</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="save_survey">
            <input type="hidden" name="survey_id" value="<?php echo esc_attr($survey->id); ?>">
            <?php wp_nonce_field('save_survey_nonce'); ?>

            <p>
                <label for="question"><strong>Question:</strong></label>
                <input type="text" name="question" id="question" class="regular-text" value="<?php echo esc_attr($survey->question); ?>" required>
            </p>
            <p>
                <label for="options"><strong>Comma separated options:</strong></label>
                <textarea name="options" id="options" rows="4" cols="50" required><?php echo esc_textarea($options_text); ?></textarea>
            </p>
      
            <button type="submit" class="button button-primary">Save Changes</button>
        </form>
    </div>