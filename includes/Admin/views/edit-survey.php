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
            <label for="question_type"><strong>Question Type:</strong></label>
            <select name="question_type" id="question_type" required>
                <option value="choice" <?php selected($survey->type, 'choice'); ?>>Multiple Choice</option>
                <option value="text" <?php selected($survey->type, 'text'); ?>>Text</option>
            </select>
        </p>
        <p id="options-container">
            <label for="options"><strong>Options (one per line):</strong></label>
            <textarea name="options" id="options" rows="4" cols="50"><?php
                $options = maybe_unserialize($survey->options);
                echo is_array($options) ? esc_textarea(implode("\n", $options)) : '';
            ?></textarea>
        </p>
        <button type="submit" class="button button-primary">Save Survey</button>
    </form>
</div>
