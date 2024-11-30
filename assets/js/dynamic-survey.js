jQuery(document).ready(function ($) {
  $("#dynamic-survey-form").on("submit", function (e) {
    e.preventDefault();

    const $form = $(this);
    const surveyId = $form.data("survey-id");
    const selectedOption = $form
      .find('input[name="survey_option"]:checked')
      .val();

    if (!selectedOption) {
      alert("Please select an option.");
      return;
    }

    $.ajax({
      url: dynamicSurvey.ajax_url,
      type: "POST",
      dataType: "json",
      data: {
        action: "submit_survey_vote",
        survey_id: surveyId,
        option: selectedOption,
        _ajax_nonce: dynamicSurvey.nonce,
      },
      success: function (response) {
        if (response.success) {
          // Replace form with results
          $form.replaceWith(response.data.html);
        } else {
          alert(response.data.message);
        }
      },
      error: function () {
        alert("An error occurred. Please try again.");
      },
    });
  });
});
