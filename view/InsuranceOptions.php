<?php
session_start();
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
if (!isset($_SESSION['status']) || $_SESSION['status'] !== true) {
    if (isset($_COOKIE['status']) && $_COOKIE['status'] === '1') {
        $_SESSION['status'] = true;
        if (!isset($_SESSION['username']) && isset($_COOKIE['remember_user'])) {
            $_SESSION['username'] = $_COOKIE['remember_user'];
        }
    } else {
        header('location: ../view/login.php?error=badrequest');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
  <title>Insurance Options</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background-color: #f2f9ff;
      margin: 0;
      padding: 0;
    }

    h1 {
      text-align: center;
      color: #004080;
      margin-top: 30px;
    }

    form {
      max-width: 500px;
      margin: 30px auto;
      padding: 20px;
      background-color: #ffffff;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    fieldset {
      border: 2px solid #66b3ff;
      padding: 20px;
      border-radius: 10px;
    }

    h3 {
      color: #0066cc;
    }

    select {
      width: 100%;
      padding: 8px;
      margin: 8px 0;
      border-radius: 5px;
      border: 1px solid #ccc;
    }

    input[type="submit"] {
      background-color: #3399ff;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
    }

    input[type="submit"]:hover {
      background-color: #007acc;
    }

    p {
      font-size: 14px;
    }

    #result, #deductibleDisplay, #payoutResult {
      font-weight: bold;
    }
  </style>
</head>
<body>
<h1>Insurance Options</h1>
<form onsubmit="return checkForm()">
  <fieldset>
    <h3>Coverage Selector:</h3>
    <label><input type="radio" name="coverage" value="basic"> Basic Coverage</label>
    <label><input type="radio" name="coverage" value="standard"> Standard Coverage</label>
    <label><input type="radio" name="coverage" value="premium"> Premium Coverage</label>
    <p id="coverageError"></p>

    <h3>Choose Claim Amount:</h3>
    <select id="claimAmount" onchange="setDeductibleAndPayout()">
      <option value="">--Select Claim Example--</option>
      <option value="2000">Claim: $2000</option>
      <option value="5000">Claim: $5000</option>
      <option value="8000">Claim: $8000</option>
    </select>
    <p id="claimError"></p>

    <h3>Auto-filled Deductible:</h3>
    <p id="deductibleDisplay"></p>

    <h3>Estimated Payout:</h3>
    <p id="payoutResult"></p>

    <input type="submit" value="Submit" />
    <p id="result"></p>
  </fieldset>
</form>

<script>
  // Mapping claim to deductible
  const deductibleMap = {
    "2000": 500,
    "5000": 1000,
    "8000": 1500
  };

  function setDeductibleAndPayout() {
    let claimValue = document.getElementById("claimAmount").value;

    if (claimValue) {
      let deductible = deductibleMap[claimValue];
      let payout = claimValue - deductible;

      document.getElementById("deductibleDisplay").innerHTML = "Deductible: $" + deductible;
      document.getElementById("deductibleDisplay").style.color = 'blue';

      document.getElementById("payoutResult").innerHTML = "You will receive: $" + payout + " after deductible.";
      document.getElementById("payoutResult").style.color = 'green';
    } else {
      document.getElementById("deductibleDisplay").innerHTML = "";
      document.getElementById("payoutResult").innerHTML = "";
    }
  }

  function checkForm() {
    let valid = true;

    let coverage = document.querySelector('input[name="coverage"]:checked');
    let claim = document.getElementById("claimAmount").value;

    if (!coverage) {
      document.getElementById("coverageError").innerHTML = "Please select a coverage tier.";
      document.getElementById("coverageError").style.color = "red";
      valid = false;
    } else {
      document.getElementById("coverageError").innerHTML = "";
    }

    if (!claim) {
      document.getElementById("claimError").innerHTML = "Please select a claim amount.";
      document.getElementById("claimError").style.color = "red";
      valid = false;
    } else {
      document.getElementById("claimError").innerHTML = "";
    }

    if (valid) {
      document.getElementById("result").innerHTML = "Form submitted successfully!";
      document.getElementById("result").style.color = "green";
    } else {
      document.getElementById("result").innerHTML = "Please fix the errors above.";
      document.getElementById("result").style.color = "red";
    }

    //return false; // prevent 404 or form submission
  }
</script>
</body>
</html>
