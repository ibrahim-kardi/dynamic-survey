document.addEventListener("DOMContentLoaded", function () {
  const questionTypeSelect = document.getElementById("question_type");
  const optionsContainer = document.getElementById("options-container");
  const optionsTextarea = document.getElementById("options");
  console.log(questionTypeSelect);
  function toggleOptions() {
    if (questionTypeSelect.value === "text") {
      optionsContainer.style.display = "none";
      optionsTextarea.removeAttribute("required");
    } else {
      optionsContainer.style.display = "block";
      optionsTextarea.setAttribute("required", "required");
    }
  }

  // Initial check
  toggleOptions();

  // Add event listener
  questionTypeSelect.addEventListener("change", toggleOptions);
});
