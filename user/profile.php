<?php
session_start();
include "../config/database.php";

if(!isset($_SESSION['user_id'])){
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT name, email, phone, student_id FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_data = $stmt->get_result()->fetch_assoc();
$stmt->close();

$message = "";
$error = "";

// Update profile
if(isset($_POST['update_profile'])){
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $student_id = trim($_POST['student_id']);
    
    $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, student_id = ? WHERE id = ?");
    $stmt->bind_param("sssi", $name, $phone, $student_id, $user_id);
    
    if($stmt->execute()){
        $message = "Profile updated successfully!";
        $_SESSION['user_name'] = $name;
        // Refresh user data
        $stmt2 = $conn->prepare("SELECT name, email, phone, student_id FROM users WHERE id = ?");
        $stmt2->bind_param("i", $user_id);
        $stmt2->execute();
        $user_data = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
    } else {
        $error = "Failed to update profile. Please try again.";
    }
    $stmt->close();
}

// Update password
if(isset($_POST['update_password'])){
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if(strlen($new_password) < 8){
        $error = "New password must be at least 8 characters.";
    } elseif($new_password !== $confirm_password){
        $error = "New passwords do not match.";
    } else {
        // Verify current password
        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stored_password = $row['password'];
        $stmt->close();
        
        if(password_verify($current_password, $stored_password)){
            $hashed_new_password = password_hash($new_password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->bind_param("si", $hashed_new_password, $user_id);
            if($stmt->execute()){
                $message = "Password updated successfully!";
            } else {
                $error = "Failed to update password.";
            }
            $stmt->close();
        } else {
            $error = "Current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
<title>My Profile - University Library</title>
<link rel="stylesheet" href="../css/style.css">
<style>
.profile-container {
    max-width: 600px;
    margin: 50px auto;
}
.profile-card {
    background: white;
    padding: 30px;
    border-radius: 20px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.profile-section {
    margin-bottom: 30px;
}
.profile-section h3 {
    color: #333;
    margin-bottom: 20px;
    font-size: 24px;
}
.form-group {
    margin-bottom: 20px;
}
.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    color: #555;
}
.form-group input {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e1e5e9;
    border-radius: 10px;
    font-size: 16px;
    transition: border-color 0.3s;
}
.form-group input:focus {
    outline: none;
    border-color: #667eea;
}
.btn-primary {
    background: linear-gradient(135deg, #667eea, #764ba2);
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: transform 0.2s;
}
.btn-primary:hover {
    transform: translateY(-2px);
}
.alert-success {
    background: linear-gradient(135deg, #4caf50, #45a049);
    color: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
.alert-error {
    background: linear-gradient(135deg, #f44336, #da190b);
    color: white;
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}
</style>
</head>
<body>

<div class="navbar" style="display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center;">
        <h1 style="font-size: 24px; margin-right: 20px;">👤 My Profile</h1>
        <div style="background: rgba(255,255,255,0.2); padding: 10px 20px; border-radius: 25px;">
            <span style="color: white; font-weight: bold;"><?= htmlspecialchars($user_data['name']) ?></span>
        </div>
    </div>
    <div>
        <a href="dashboard.php" style="color: white; text-decoration: none; margin: 0 15px; font-weight: bold;">🏠 Dashboard</a>
        <a href="../auth/logout.php" style="color: #ff6b6b; background: rgba(255,255,255,0.2); padding: 8px 16px; border-radius: 20px; text-decoration: none; font-weight: bold;" onclick="return confirm('Are you sure you want to logout?')">🚪 Logout</a>
    </div>
</div>

<div class="container">
    <div class="profile-container">
        
        <?php if($message): ?>
            <div class="alert-success"><?= $message ?></div>
        <?php endif; ?>
        <?php if($error): ?>
            <div class="alert-error"><?= $error ?></div>
        <?php endif; ?>

        <!-- Profile Information Card -->
        <div class="profile-card">
            <div class="profile-section">
                <h3>👤 Profile Information</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div>
                        <strong>Name:</strong> <?= htmlspecialchars($user_data['name']) ?>
                    </div>
                    <div>
                        <strong>Email:</strong> <?= htmlspecialchars($user_data['email']) ?>
                    </div>
                    <div>
                        <strong>Phone:</strong> <?= htmlspecialchars($user_data['phone'] ?? 'Not set') ?>
                    </div>
                    <div>
                        <strong>Student ID:</strong> <?= htmlspecialchars($user_data['student_id'] ?? 'Not set') ?>
                    </div>
                </div>
                <div style="margin-top: 25px;">
                    <a href="#update-profile" class="btn-primary" onclick="document.getElementById('update-profile').scrollIntoView();">✏️ Edit Profile</a>
                    <a href="#update-password" class="btn-primary" onclick="document.getElementById('update-password').scrollIntoView();" style="margin-left: 15px;">🔐 Change Password</a>
                </div>
            </div>
        </div>

        <!-- Update Profile Form -->
        <div class="profile-card" id="update-profile">
            <div class="profile-section">
                <h3>✏️ Update Profile</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Name</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($user_data['name']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Phone</label>
                        <input type="text" name="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Student ID</label>
                        <input type="text" name="student_id" value="<?= htmlspecialchars($user_data['student_id'] ?? '') ?>">
                    </div>
                    <button type="submit" name="update_profile" class="btn-primary" onclick="return confirm('Update your profile information?')">💾 Update Profile</button>
                </form>
            </div>
        </div>

        <!-- Update Password Form -->
        <div class="profile-card" id="update-password">
            <div class="profile-section">
                <h3>🔐 Change Password</h3>
                <form method="POST">
                    <div class="form-group">
                        <label>Current Password</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label>New Password (min 8 chars)</label>
                        <input type="password" name="new_password" minlength="8" required>
                    </div>
                    <div class="form-group">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" minlength="8" required>
                    </div>
                    <button type="submit" name="update_password" class="btn-primary" onclick="return confirm('Update your password? You will remain logged in.')">🔒 Update Password</button>
                </form>
            </div>
        </div>

    </div>
</div>

</body>
</html> 

**New user/profile.php created** with:
- View current profile info.
- Edit name/phone/student_id.
- Change password w/ current verification.
- Modern styling matching dashboard.
- Inline confirmations.
- Navbar w/ name greeting + back/dashboard/logout.

Added to navbar links. Users can fully manage complete profiles.

Library system now has comprehensive profile management + all previous features.
