(function ($) {
  $(document).on("submit", "#dynamic-survey-form", function (e) {
    e.preventDefault(); // Prevent form's default submission behavior
    console.log("submit");
    const form = $(this);
    const surveyId = form.data("survey-id"); // Retrieve survey ID
    const selectedOption = form
      .find('input[name="survey_option"]:checked')
      .val(); // Get selected option

    // Validation: Ensure an option is selected
    if (!selectedOption) {
      $("#survey-message").html(
        '<p style="color: red;">Please select an option before submitting.</p>'
      );
      return;
    }

    // AJAX request
    $.ajax({
      url: dynamicSurvey.ajax_url, // From wp_localize_script
      type: "POST",
      data: {
        action: "submit_survey_vote",
        survey_id: surveyId,
        option: selectedOption,

        _ajax_nonce: dynamicSurvey.nonce, // Security nonce
      },
      beforeSend: function () {
        // Optional: Add a loading spinner or disable the submit button
        form
          .find('button[type="submit"]')
          .prop("disabled", true)
          .text("Submitting...");
      },
      success: function (response) {
        if (response.success) {
          // Replace form with results
          form.replaceWith(response.data.html);
        } else {
          // Show error message
          $("#survey-message").html(
            '<p style="color: red;">' + response.data.message + "</p>"
          );
        }
      },
      error: function (xhr, status, error) {
        // Handle errors
        console.error("AJAX Error:", error);
        $("#survey-message").html(
          '<p style="color: red;">An unexpected error occurred. Please try again later.</p>'
        );
      },
      complete: function () {
        // Re-enable the submit button
        form
          .find('button[type="submit"]')
          .prop("disabled", false)
          .text("Submit");
      },
    });
  });
})(jQuery);
