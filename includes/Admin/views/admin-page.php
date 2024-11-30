<div class="wrap">
    <h1>Dynamic Survey</h1>

    <h2>Create a New Survey</h2>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="save_survey">
        <?php wp_nonce_field('save_survey_nonce'); ?>

        <p>
            <label for="question"><strong>Question:</strong></label>
            <input type="text" name="question" id="question" class="regular-text" required>
        </p>
        <p>
            <label for="question_type"><strong>Question Type:</strong></label>
            <select name="question_type" id="question_type" required>
                <option value="choice">Multiple Choice</option>
                <option value="text">Text</option>
            </select>
        </p>
        <p id="options-container">
            <label for="options"><strong>Options (one per line):</strong></label>
            <textarea name="options" id="options" rows="4" cols="50"></textarea>
        </p>
        <button type="submit" class="button button-primary">Create Survey</button>
    </form>

    <h2>Existing Surveys</h2>
    <table class="widefat fixed">
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Type</th>
                <th>Options</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($surveys)) : ?>
                <?php foreach ($surveys as $survey) : ?>
                    <tr>
                        <td><?php echo esc_html($survey->id); ?></td>
                        <td><?php echo esc_html($survey->question); ?></td>
                        <td><?php echo esc_html($survey->type); ?></td>
                        <td>
                            <?php
                            $options = maybe_unserialize($survey->options);
                            echo is_array($options) ? implode(', ', array_map('esc_html', $options)) : 'N/A';
                            ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('admin.php?page=edit-dynamic-survey&survey_id=' . $survey->id)); ?>" class="button">Edit</a>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=delete_survey&survey_id=' . $survey->id), 'delete_survey_nonce')); ?>" class="button button-danger" onclick="return confirm('Are you sure?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="5">No surveys found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
