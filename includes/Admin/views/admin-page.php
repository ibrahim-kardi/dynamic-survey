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
            <label for="options"><strong>Options (one per line):</strong></label>
            <textarea name="options" id="options" rows="4" cols="50" required></textarea>
        </p>
        <button type="submit" class="button button-primary">Create Survey</button>
    </form>

    <h2>Existing Surveys</h2>
    <table class="widefat fixed">
        <thead>
            <tr>
                <th>ID</th>
                <th>Question</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($surveys)) : ?>
                <?php foreach ($surveys as $survey): ?>
                    <tr>
               <td><?php echo esc_html($survey->id); ?></td>
    <td><?php echo esc_html($survey->question); ?></td>
    <td>
        <?php
        $options = maybe_unserialize($survey->options);
        if (is_array($options)) {
            echo implode(', ', array_map('esc_html', $options)); // Display as comma-separated options
        } else {
            echo 'No options found.';
        }
        ?>
    </td>
    <td>
        <a href="<?php echo esc_url(admin_url('tools.php?page=edit-survey&survey_id=' . $survey->id)); ?>" class="button">Edit</a>
        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=delete_survey&survey_id=' . $survey->id), 'delete_survey_nonce')); ?>" class="button button-danger" onclick="return confirm('Are you sure you want to delete this survey?');">Delete</a>
    </td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="3">No surveys found.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
