<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tab Panel with Pagination</title>
<style>
  /* CSS styles */
  .tab-panel {
    width: 80%;
    margin: 0 auto;
  }

  .tabs {
    margin-bottom: 10px;
  }

  .tab-button {
    cursor: pointer;
    padding: 10px 20px;
    border: none;
    border-radius: 5px 5px 0 0;
    background-color: #f0f0f0;
  }

  .tab-button.active {
    background-color: #ccc;
  }

  .tab-content {
    display: none;
    padding: 20px;
    border: 1px solid #ccc;
  }

  .tab-content.active {
    display: block;
  }

  .pagination {
    margin-top: 20px;
  }

  .pagination button {
    padding: 8px 16px;
    margin: 0 5px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
  }

  .pagination button:hover {
    background-color: #ddd;
  }
</style>
</head>
<body>

<div class="tab-panel">
  <div class="tabs">
    <button class="tab-button active" data-target="tab1">Tab 1</button>
    <button class="tab-button" data-target="tab2">Tab 2</button>
    <button class="tab-button" data-target="tab3">Tab 3</button>
  </div>
  <div id="tab1" class="tab-content active">
    <!-- Content for Tab 1 -->
    <p>Content for Tab 1 goes here.</p>
  </div>
  <div id="tab2" class="tab-content">
    <!-- Content for Tab 2 -->
    <p>Content for Tab 2 goes here.</p>
  </div>
  <div id="tab3" class="tab-content">
    <!-- Content for Tab 3 -->
    <p>Content for Tab 3 goes here.</p>
  </div>
  <div class="pagination">
    <button class="prev">Prev</button>
    <button class="next">Next</button>
  </div>
</div>

<script>
  // JavaScript code
  document.addEventListener("DOMContentLoaded", function () {
    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContents = document.querySelectorAll(".tab-content");
    const prevButton = document.querySelector(".prev");
    const nextButton = document.querySelector(".next");

    let currentIndex = 0;

    // Show initial tab
    tabButtons[currentIndex].classList.add("active");
    tabContents[currentIndex].classList.add("active");

    // Add event listeners for tab buttons
    tabButtons.forEach((button, index) => {
      button.addEventListener("click", () => {
        showTab(index);
      });
    });

    // Show specific tab
    function showTab(index) {
      tabButtons.forEach((button) => {
        button.classList.remove("active");
      });
      tabContents.forEach((tabContent) => {
        tabContent.classList.remove("active");
      });
      tabButtons[index].classList.add("active");
      tabContents[index].classList.add("active");
      currentIndex = index;
    }

    // Pagination
    prevButton.addEventListener("click", () => {
      if (currentIndex > 0) {
        showTab(currentIndex - 1);
      }
    });

    nextButton.addEventListener("click", () => {
      if (currentIndex < tabButtons.length - 1) {
        showTab(currentIndex + 1);
      }
    });
  });
</script>

</body>
</html>
