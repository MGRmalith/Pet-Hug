<?php
include_once "../connection.php";

$error_messages = [];

// Handling form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Password confirmation
    if ($password !== $confirm_password) {
        $error_messages[] = "Passwords do not match.";
    }

    // Phone validation
    if (!preg_match("/^[0-9]{10}$/", $phone)) {
        $error_messages[] = "Phone number must be 10 digits.";
    }

    // Check if email already exists
    $checkEmail = "SELECT * FROM admin WHERE admin_email = ?";
    $stmt = $conn->prepare($checkEmail);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error_messages[] = "This email is already registered. Please use a different email.";
    }

    // File upload validation (only if an image is uploaded)
    $target_dir = "../uploads/";
    $target_file = $target_dir . basename($_FILES["image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

    if (!empty($_FILES["image"]["name"])) {
        // Validate file type
        if (!in_array($imageFileType, $allowed_types)) {
            $error_messages[] = "Only JPG, JPEG, PNG, and GIF files are allowed.";
        }

        // Attempt to move the uploaded file only if validations pass
        if (empty($error_messages)) {
            if (!move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                $error_messages[] = "File upload failed.";
            }
        }
    } else {
        $target_file = null; // Set to null if no image is uploaded
    }

    if (empty($error_messages)) {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Insert admin data
        $sql = "INSERT INTO admin (admin_name, admin_email, admin_image, admin_address, admin_phone, admin_password) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $name, $email, $target_file, $address, $phone, $hashed_password);

        if ($stmt->execute()) {
            header("Location: adminLogin.php");
            exit();
        } else {
            $error_messages[] = "Database error: " . $stmt->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Signup Page</title>
  <link rel="stylesheet" href="../afterLoginUser_style/signup.css">
</head>
<body>
  <?php 
  if (!empty($error_messages)) {
      foreach ($error_messages as $error) {
          echo "<div class='error_message'>" . htmlspecialchars($error) . "</div>";
      }
  }
  ?>
  <div class="container">
    <!-- Left Section -->
    <div class="left-section">
        <h1>Welcome to PetHug</h1>
        <p>Your partner in pet care and wellness.</p>
        <img src="../images/images.png" alt="Pets Illustration" class="left-image">
    </div>

    <!-- Right Section -->
    <div class="right-section">
      <h2> Create your PetHug Account </h2>
      <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" enctype="multipart/form-data">
        
        <div class="form-group">
          <label for="name">Name:</label>
          <input type="text" id="name" name="name" placeholder="Enter your first name" required>
        </div>

        <div class="form-group">
          <label for="email">Email</label>
          <input type="email" id="email" name="email" placeholder="Enter your email" required>
        </div>

        <div class="form-group">
          <label for="address">Address</label>
          <input type="text" id="address" name="address" placeholder="Enter your address" required>
        </div>

        <div class="form-group">
          <label for="phone">Phone</label>
          <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required>
        </div>

        <div class="form-group">
          <label for="confirm_password">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
        </div>

        <div class="form-group">
          <label for="image">Upload Image:</label>
          <input type="file" id="image" name="image" accept="image/*">
        </div>

        <div class="form-actions">
          <div>
            <input type="checkbox" id="remember" name="remember">
            <label for="remember">Remember me</label>
          </div>
          <a href="#" class="forgot-password">Forgot Password?</a>
        </div>

        <div class="button-container">
          <button type="submit" class="btn-signin">Sign In</button>
          <button type="reset" class="btn reset">Reset</button>
        </div>
      </form>
      <p class="signup-link">Have an account? <a href="adminLogin.php">Log In</a></p>
    </div>
  </div>
</body>
</html>
