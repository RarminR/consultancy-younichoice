<?php
session_start();
require_once "configDatabase.php";

if (!isset($_SESSION['type'])) { // testez daca userul est logat
    header("location: index.php");
    die();
}
else {
    $typeAccount = $_SESSION["type"];
    $accountId = $_SESSION["id"];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Research and Reference Links</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      line-height: 1.6;
      margin: 0;
      padding: 0;
      background-color: #f9f9f9;
      color: #333;
    }
    .container {
      max-width: 800px;
      margin: 20px auto;
      padding: 20px;
      background: #fff;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }
    h1 {
      text-align: center;
      color: #2c3e50;
    }
    h2 {
      color: #34495e;
      cursor: pointer;
      padding: 10px;
      background: #ecf0f1;
      border-radius: 5px;
      margin-top: 20px;
    }
    h2:hover {
      background: #bdc3c7;
    }
    .content {
      display: none;
      padding: 10px 20px;
      border-left: 3px solid #3498db;
      margin-top: 10px;
    }
    a {
      color: #3498db;
      text-decoration: none;
    }
    a:hover {
      text-decoration: underline;
    }
    ul {
      list-style-type: none;
      padding: 0;
    }
    li {
      margin: 10px 0;
    }
  </style>
</head>

<?php require_once "navbar.php"; ?>
<br>
<body>
  <div class="container">
    <h1>Research and Reference Links</h1>

    <!-- Chapter 1: Internships -->
    <h2 onclick="toggleContent('internships')">1. Internships</h2>
    <div id="internships" class="content">
      <ul>
        <li><a href="https://docs.google.com/spreadsheets/d/1mppN4rcCxaLoXjCtP0muyjdkX101Yn5hPS4nOBS0Tlg/edit" target="_blank">a) Research Internships</a></li>
        <li><a href="https://docs.google.com/document/d/15zPLFaOJ2Pa85cGFwy-lQynUPf_O-p7rKvx0B-OhuN4/edit?tab=t.0" target="_blank">b) Template: Send Email to Companies</a></li>
        <li><a href="https://docs.google.com/document/d/1iBBb0FIRid3XGbItempzItjYrnyGuvusvS1SYuZQucg/edit?tab=t.0" target="_blank">c) Template: Email Parent About the Plan</a></li>
        <li><a href="https://docs.google.com/document/d/14MMyiOQS3ph5LbT83mn_cxO3UBo4tDw6foJsQX3s4Zw/edit?tab=t.0" target="_blank">d) Template: Inform Parent About Success</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1aLK3Z_MN1JZOfHvCwQHnseFvirVculpU4EtlE1Mk4WM/edit?gid=0#gid=0" target="_blank">e) Internship Results Tracker</a></li>
      </ul>
    </div>

    <!-- Chapter 2: Volunteer -->
    <h2 onclick="toggleContent('volunteer')">2. Volunteer</h2>
    <div id="volunteer" class="content">
      <ul>
        <li><a href="https://docs.google.com/spreadsheets/d/1dsIzJTq2HWnCGul4x-BJNqcaE8kj9o0I/edit?usp=sharing&ouid=115117542664153976765&rtpof=true&sd=true" target="_blank">a) Research Volunteer</a></li>
        <li><a href="https://www.volunteerhq.org/" target="_blank">b) Volunteer HQ</a></li>
        <li><a href="https://docs.google.com/document/d/1eDu6IneMqaMHBfOPexZTz1_naXhCmLobrzJ-CGVIMf4/edit?tab=t.0" target="_blank">c) Template: Send Email to Companies</a></li>
        <li><a href="https://docs.google.com/document/d/1KWocsuqlIR2e_XPUs3YF20qhEjyk0TGp5i8Jvr8xvBA/edit?tab=t.0" target="_blank">d) Template: Email Parent About Volunteering Plan</a></li>
        <li><a href="https://docs.google.com/document/d/1RpyNrS1mRD105gzTqXXgq3KbLsoWf8ovS49QwAQNoek/edit?tab=t.0" target="_blank">e) Inform Parent About Volunteering Success</a></li>
      </ul>
    </div>

    <!-- Chapter 3: Essays -->
    <h2 onclick="toggleContent('essays')">3. Essays</h2>
    <div id="essays" class="content">
      <ul>
        <li><a href="https://docs.google.com/document/d/1BedvRmgSkB-lU9BBqGLdVm_huXgvvuIgFBCIfyMcj0M/edit?tab=t.0" target="_blank">a) Academic-Oriented Universities</a></li>
        <li><a href="https://docs.google.com/document/d/13X_TS1SHWpnZ_86auskzRm9tE_qEo_SRQdXqr8GMKXU/edit?tab=t.0" target="_blank">b) Creative Universities</a></li>
        <li><a href="https://docs.google.com/document/d/1qfaMTDXFar2_NipYyHytVWFdn8e2zgIArARMWFr5YU8/edit?tab=t.0" target="_blank">c) Academic/Creative Universities</a></li>
        <li><a href="https://docs.google.com/document/d/16mC5y8XpXDAgAhdCJwGzQocJPdeIgC2E/edit" target="_blank">d) Common App Essay</a></li>
        <li><a href="https://docs.google.com/document/d/1SskcNwGzTHXDfkjlwkMB2SiJPOKM1CdEKyCe0xfRzSI/edit?tab=t.0" target="_blank">e) Covid Essay</a></li>
        <li><a href="https://docs.google.com/document/d/10Ve9xosuEpkw3TQjBrwnJwPbU8PlNPPb/edit" target="_blank">f) Additional Info Essay</a></li>
        <li><a href="https://docs.google.com/document/d/18qSknF5Ezq7BnpJzMOUXnrkfE6q_RkyeBGIi2iQefb4/edit?tab=t.0" target="_blank">g) Academic-Oriented Universities</a></li>
        <li><a href="https://docs.google.com/document/d/1uA4MNQmOXUUzvcfd7YETQikgL_HnMDUN/edit?tab=t.0" target="_blank">h) Creative Universities</a></li>
        <li><a href="https://docs.google.com/document/d/1QnsOxfrqwfhkemD-8nv2nGx-ScQelNs1/edit?tab=t.0" target="_blank">i) Academic/Creative Universities</a></li>
        <li><a href="https://docs.google.com/document/d/1MClBJyTwCZhiil4lsMNEax1pfYEX4HFxI5BfEB2PtZ8/edit?tab=t.0" target="_blank">j) Art-Oriented Essay</a></li>
        <li><a href="https://docs.google.com/document/d/1KRPBCsa29b90lSTIrR9bdh4G72D-sNM21VwXVC2Syf8/edit?tab=t.0" target="_blank">k) STEM-Oriented Essay</a></li>
        <li><a href="https://docs.google.com/document/d/1fUv8-VRNBJkEHeh01j5q4jWtDjj0GNLB/edit" target="_blank">l) Creative-Oriented Essay</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1hK9uTztlNDkpCJkAP4zFV6Q_hyFKqcI3T6mSnUVv0Io/edit?gid=0#gid=0" target="_blank">m) Essay Tracker</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1Y2BkCqvmAwhLZwlRXr5tUzGxZ96TX674kjjBjSD3DRM/edit?gid=0#gid=0" target="_blank">n) Initial Exercises</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1dPj321ratrEyAhs615gTi3ti42JzPyHpULCk-q-AM4U/edit?gid=0#gid=0" target="_blank">o) Tips & Tricks for Building Essays</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1mRnXL65jGYSB5Hb959_LHWw5qmFvnLssivGQHTX1U-Q/edit?gid=0#gid=0" target="_blank">p) Writing Exercises</a></li>
      </ul>
    </div>

    <!-- Chapter 4: Interview -->
    <h2 onclick="toggleContent('interview')">4. Interview</h2>
    <div id="interview" class="content">
      <ul>
        <li><a href="https://drive.google.com/drive/folders/1HZH7fJOxPuLJT8F8bCneHHiIUJ1DINVD" target="_blank">a) USA Interview Database</a></li>
        <li><a href="https://drive.google.com/file/d/1o4rBkHE9qCLU8EV9450IWWQ-sLiXS08A/view" target="_blank">b) Problem Solving Ecole - France</a></li>
        <li><a href="https://drive.google.com/drive/folders/1HZH7fJOxPuLJT8F8bCneHHiIUJ1DINVD" target="_blank">c) Europe Interview Database</a></li>
        <li><a href="https://drive.google.com/drive/folders/17NTIyF5KyOTEPVgVyEASiiL6R2nZQTix" target="_blank">d) Cambridge</a></li>
        <li><a href="https://docs.google.com/document/d/1FxWDMdTnWLSuClxUNg0Wj047EpF_vo002Z-kwBTVTug/edit?tab=t.0" target="_blank">e) Research the Interviewer</a></li>
        <li><a href="https://docs.google.com/document/d/1U0wjpY9_5JQ4mLJ8npwbsj5JQI3fWyZgoT6Cuyb9PPs/edit?tab=t.0" target="_blank">f) Workshop Interviewer</a></li>
      </ul>
    </div>

    <!-- Chapter 5: Exams -->
    <h2 onclick="toggleContent('exams')">5. Exams</h2>
    <div id="exams" class="content">
      <ul>
        <li><a href="https://docs.google.com/spreadsheets/d/1XwXzxclHSOwu7ap07k1aq82SjpdMc-gpPBsdABf9zMw/edit?gid=0#gid=0" target="_blank">a) USA TOEFL/IELTS/Duolingo Requirements</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1dWNRRfxEWVrBdZXcbS_1MrZBNgSO025SPQIaoAiDdmk/edit?gid=0#gid=0" target="_blank">b) USA Standardized Exams</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1TJl4B508LE8zPhft-cq9Q_nBg9BChZHZXHrNpi6CGXg/edit?gid=0#gid=0" target="_blank">c) Europe TOEFL/IELTS/Duolingo/CAE Requirements</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1Hl-NxZ6GtS2Dx52hz_f3kFTxo0pZQZ1uBs1eudrljpA/edit?gid=786982946#gid=786982946" target="_blank">d) Europe Standardized Exams</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1DqVQHnaNiCFpuVjgtjjQaOT1kVNoQr8jUJ046AZvF50/edit?gid=0#gid=0" target="_blank">e) UK TOEFL/IELTS/Duolingo/CAE Requirements</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1AvW3Q9W5ELNDZM7nh-SWfG1SUc2UDbe029qbKH3K0b0/edit?gid=0#gid=0" target="_blank">f) UK Standardized Exams</a></li>
        <li><a href="https://docs.google.com/document/d/1dV5bmdC8KoZ3CbRQktfEI28EhlWcEFGxiGD32Z6ksvA/edit?tab=t.0" target="_blank">g) Send Feedback to Parent if Milestones Are Not in Target</a></li>
      </ul>
    </div>

    <!-- Chapter 6: Summer Camps -->
    <h2 onclick="toggleContent('summer-camps')">6. Summer Camps</h2>
    <div id="summer-camps" class="content">
      <ul>
        <li><a href="https://docs.google.com/spreadsheets/d/1Pkdq8r5-gRpaG7MzUK_hB5EAtA0PiJlvuzp0fGEp9K8/edit" target="_blank">a) USA List</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1Z3l7AsekPO4uS17AP1FzB-zYfO0qgMPq1A8-hVKzZdQ/edit?gid=1863455534#gid=1863455534" target="_blank">b) Europe List</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1pH78H5wqinz41pRwGZVwBTk_1I-2UyNvQwu3DoekAhM/edit?gid=1863455534#gid=1863455534" target="_blank">c) UK List</a></li>
      </ul>
    </div>

    <!-- Chapter 7: Letters of Recommendation -->
    <h2 onclick="toggleContent('recommendations')">7. Letters of Recommendation</h2>
    <div id="recommendations" class="content">
      <ul>
        <li><a href="https://docs.google.com/document/d/1n_QV3WDAhh9w7JgNteIPwS9bWO9V7r5N/edit?tab=t.0" target="_blank">a) STEM</a></li>
        <li><a href="https://drive.google.com/file/d/1kJkrjtBP3eki6PSSXcw84URcLJI3MUYM/view" target="_blank">b) Humanities</a></li>
        <li><a href="https://docs.google.com/document/d/1gF9oXZ1Aih246_rhLSSzlYS0VgUwnv76/edit?tab=t.0" target="_blank">c) Employer</a></li>
        <li><a href="https://docs.google.com/document/d/1eU4SbTvcALgkCuZ3-qySZdmsAC780BuT6jkp0Jd2098/edit?tab=t.0" target="_blank">d) Volunteer</a></li>
        <li><a href="https://docs.google.com/document/d/13yWrcxqOl0ArhXtFst9ueXunwFC3h7xz/edit?tab=t.0" target="_blank">e) Counselor</a></li>
      </ul>
    </div>

    <!-- Chapter 8: CV -->
    <h2 onclick="toggleContent('cv')">8. CV</h2>
    <div id="cv" class="content">
      <ul>
        <li><a href="https://docs.google.com/document/d/19ns-aU_ajxKbsIgT0qAyDnu_EUcOyIe6gGjGEma-YMQ/edit?tab=t.0" target="_blank">a) CV Template</a></li>
        <li><a href="https://drive.google.com/file/d/1ijpq7_Ch1LbM-eY_rNeUnv4aNgn-nRm6/view" target="_blank">b) CV Tips</a></li>
      </ul>
    </div>

    <!-- Chapter 9: Best Majors -->
    <h2 onclick="toggleContent('best-majors')">9. Best Majors</h2>
    <div id="best-majors" class="content">
      <ul>
        <li><a href="https://docs.google.com/spreadsheets/d/19yQr-_RwZeugc5e5T5mN6sOWNAYEAeSDydhsL6LddSc/edit?gid=0#gid=0" target="_blank">a) USA</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1kby3fgfa17Zz8FUAjNQC4GLy2FlQai_fU9H_Uw1v3AU/edit?gid=0#gid=0" target="_blank">b) Europe</a></li>
        <li><a href="https://docs.google.com/spreadsheets/d/1xhY_uZ0O1UC6joCWbe_AXkZLrM2UXdMbsFr15Oa_cLw/edit?gid=0#gid=0" target="_blank">c) UK</a></li>
      </ul>
    </div>

    <!-- Chapter 10: Passion Projects -->
    <h2 onclick="toggleContent('passion-projects')">10. Passion Projects</h2>
    <div id="passion-projects" class="content">
      <ul>
        <li><a href="https://docs.google.com/spreadsheets/u/2/d/1uH6fNJlCwDxLcIv_cAK3VJvnAuTkUYSoiDpc0AgpvxU/edit" target="_blank">a) Existing Conferences</a></li>
        <li><a href="https://docs.google.com/spreadsheets/u/2/d/1ypduMVXGwmsWWW_zI28N2aw_W3NPeEQzJo26osiFx0U/edit" target="_blank">b) Clubs List</a></li>
      </ul>
    </div>
  </div>

  <script>
    function toggleContent(id) {
      const content = document.getElementById(id);
      if (content.style.display === "block") {
        content.style.display = "none";
      } else {
        content.style.display = "block";
      }
    }
  </script>
</body>
</html>