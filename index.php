<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Session Management</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>

<body>
  <?php
  session_start();

  // Function to display the session array for debugging
  function show($stuff)
  {
    echo "<pre>";
    print_r($stuff);
    echo "</pre>";
  }

  // Function to recursively convert arrays and objects to JSON string
  function recursive_convert($value)
  {
    if (is_array($value) || is_object($value)) {
      return json_encode($value, JSON_PRETTY_PRINT); // Using pretty print for readability
    } elseif (is_null($value)) {
      return "NULL"; // Handle null values explicitly
    } elseif (is_bool($value)) {
      return $value ? "true" : "false"; // Handle boolean values
    }
    return $value; // Return scalar values directly (strings, integers, etc.)
  }

  // Function to check if a value looks like JSON
  function is_json($value)
  {
    // Try decoding the value
    $decoded = json_decode($value);
    // Check if it's a valid JSON and whether the decoded value is actually an object or array
    return (json_last_error() == JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded)));
  }

  // Check if form to update session is submitted
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_session'])) {
    // Loop through posted data and update session variables
    foreach ($_POST as $key => $value) {
      if (array_key_exists($key, $_SESSION)) {
        $_SESSION[$key] = recursive_convert($value);  // Apply conversion recursively
      }
    }
  }

  // Check if form to unset session is submitted
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['unset_session'])) {
    session_unset();
  }

  // Check if form to add new session variable is submitted
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_session'])) {
    $new_key = $_POST['new_key'];
    $new_value = $_POST['new_value'];
    if (!empty($new_key) && !empty($new_value)) {
      $_SESSION[$new_key] = recursive_convert($new_value); // Apply conversion recursively
    }
  }

  // Check if the form is submitted
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plain_text'])) {
    $plain_text = $_POST['plain_text'];
    // Hash the plain text using bcrypt
    $hashed_value = password_hash($plain_text, PASSWORD_BCRYPT);
    echo '<div class="alert alert-success alert-dismissible fade show" role="alert">';
    echo '<strong>Hashed Value:</strong> ' . $hashed_value;
    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    echo '</div>';
  }
  ?>

  <div class="container mt-5">
    <div class="row">
      <div class="col-6">
        <!-- Form to update session values -->
        <form method="POST" action="" class="mb-3">
          <div class="card">
            <div class="card-header">
              Session List
            </div>
            <div class="card-body">
              <?php
              // Loop through the session array to create input fields
              foreach ($_SESSION as $key => $value) {
                $value = recursive_convert($value);  // Apply conversion recursively for each session value
                echo '<div class="mb-3">';
                echo '<label for="' . $key . '" class="form-label">' . $key . ':</label>';
                
                // Check if the value looks like JSON
                if (is_json($value)) {
                  // If the value is JSON, display it in a <textarea>
                  echo '<textarea class="form-control" id="' . $key . '" name="' . $key . '" rows="10">' . htmlspecialchars($value) . '</textarea>';
                } else {
                  // Otherwise, display it in a regular <input> field
                  echo '<input type="text" class="form-control" id="' . $key . '" name="' . $key . '" value="' . htmlspecialchars($value) . '">';
                }
                
                echo '</div>';
              }
              ?>
              <button type="submit" name="update_session" class="btn btn-primary">Update</button>
            </div>
          </div>
        </form>
      </div>
      <div class="col-6">
        <!-- Form to add new session variable -->
        <form method="POST" action="" class="mb-3">
          <div class="card">
            <div class="card-header">
              New Session
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label for="new_key" class="form-label">New Key:</label>
                <input type="text" class="form-control" id="new_key" name="new_key" required>
              </div>
              <div class="mb-3">
                <label for="new_value" class="form-label">New Value:</label>
                <input type="text" class="form-control" id="new_value" name="new_value" required>
              </div>
              <button type="submit" name="new_session" class="btn btn-success">Add</button>
            </div>
          </div>
        </form>
        <!-- Form to input plain text and hash -->
        <form method="POST" action="" class="mb-3">
          <div class="card">
            <div class="card-header">
              Text to Hash
            </div>
            <div class="card-body">
              <div class="mb-3">
                <label for="plain_text" class="form-label">Plain Text:</label>
                <input type="text" class="form-control" id="plain_text" name="plain_text" required>
              </div>
              <button type="submit" class="btn btn-primary">Convert</button>
            </div>
          </div>
        </form>

        <!-- Form to unset all session values -->
        <form method="POST" action="">
          <button type="submit" name="unset_session" class="btn btn-danger">Unset All Session</button>
        </form>
      </div>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
