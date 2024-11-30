jQuery(document).ready(function ($) {
  $("#dynamic-survey-form").on("submit", function (e) {
    e.preventDefault(); // Prevent default form submission

    const $form = $(this);
    const surveyId = $form.data("survey-id");

    // Correctly retrieve the survey type
    const surveyType = $form.data("survey-type");
    console.log(surveyType);
    let selectedOption;

    // Handle survey based on type
    if (surveyType === "choice") {
      selectedOption = $form.find('input[name="survey_option"]:checked').val();

      if (!selectedOption) {
        alert("Please select an option.");
        return;
      }
    } else if (surveyType === "text") {
      const textResponse = $form
        .find('textarea[name="survey_option"]')
        .val()
        .trim();

      if (!textResponse) {
        alert("Please provide your response.");
        return;
      }

      selectedOption = textResponse;
    } else {
      alert("Invalid survey type.");
      return;
    }

    // AJAX request to submit the survey
    $.ajax({
      url: dynamicSurvey.ajax_url, // Defined in wp_localize_script
      type: "POST",
      dataType: "json",
      data: {
        action: "submit_survey_vote",
        survey_id: surveyId,
        option: selectedOption,
        _ajax_nonce: dynamicSurvey.nonce, // Nonce for security
      },
      success: function (response) {
        if (response.success) {
          // Replace the form with the results
          $("#dynamic-survey-form").replaceWith(response.data.html);
        } else {
          alert(response.data.message); // Display error message from server
        }
      },
      error: function () {
        alert("An error occurred. Please try again.");
      },
    });
  });
});
