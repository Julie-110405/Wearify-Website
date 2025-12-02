<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// Include the database connection
include(__DIR__ . "/config/db_connect.php");

// Fetch user data based on session user_id
$fullname = "User";
$username = "";
$email = "";
$profile_pic = "default_profile.png";

try {
    $stmt = $pdo->prepare("SELECT fullname, username, email, profile_pic FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        $fullname = $user['fullname'];
        $username = $user['username'];
        $email = $user['email'];
        $profile_pic = $user['profile_pic'] ? $user['profile_pic'] : 'default_profile.png';
    }
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap" rel="stylesheet">
  <link href="https://db.onlinewebfonts.com/c/274507bb1293902e80e0d824d978be9e?family=Canva+Sans" rel="stylesheet">
  <title>Wearify HomePage</title>
  <link rel="stylesheet" href="home.css">
  <link rel="stylesheet" href="edit_account_style.css">
  <link rel="stylesheet" href="edit_username_style.css">
  <style>
    /* Additional styles for account modal integration */
    .account-sidebar-panel {
        position: fixed;
        top: 0;
        left: -320px;
        width: 320px;
        height: auto;
        min-height: 50vh;
        max-height: 90vh;
        background-color: var(--tan-color, #eae0d6);
        padding: 20px;
        z-index: 9999;
        box-sizing: border-box;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.2);
        transition: left 0.3s ease-in-out;
        overflow-y: auto;
    }

    .account-sidebar-panel.active {
        left: 0;
    }

    .account-sidebar-panel.expanded {
        min-height: 65vh;
    }

    .modal-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 9998;
        display: none;
    }

    .modal-overlay.active {
        display: block;
    }

    .message-box {
        padding: 10px;
        margin: 10px 0;
        border-radius: 5px;
        font-size: 0.9em;
        display: none;
    }

    .message-box.success {
        background-color: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }

    .message-box.error {
        background-color: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }

    .message-box.active {
        display: block;
    }

    .default_profile {
        cursor: pointer;
    }

    /* Logout Button Styles */
    .logout-button {
        width: 100%;
        padding: 12px;
        margin-top: 15px;
        background-color: #d9534f;
        color: white;
        border: none;
        border-radius: 20px;
        font-size: 0.9em;
        font-weight: 700;
        font-family: 'Montserrat', sans-serif;
        cursor: pointer;
        transition: background-color 0.2s;
    }

    .logout-button:hover {
        background-color: #c9302c;
    }
  </style>
</head>
<body>
    <!-- Modal Overlay -->
    <div class="modal-overlay" id="accountOverlay"></div>

    <!-- Account Management Sidebar Panel -->
    <div class="account-sidebar-panel" id="accountPanel">
        <h1 class="page-title">Account</h1>

        <!-- Message Box for Success/Error -->
        <div class="message-box" id="messageBox"></div>

        <div class="profile-section">
            <div class="profile-photo-wrapper">
                <img src="<?php echo htmlspecialchars($profile_pic); ?>" alt="Profile Picture" class="profile-photo" id="profilePhotoDisplay">
                <span class="camera-icon">📷</span>
                <input type="file" id="profilePhotoInput" accept="image/*" style="display: none;">
            </div>
            
            <div class="profile-name-edit">
                <input type="text" value="<?php echo htmlspecialchars($fullname); ?>" class="user-name-input" id="fullnameInput">
                <span class="edit-icon">✎</span>
            </div>
        </div>
        
        <div class="account-links">
            <!-- Username Section -->
            <div id="username-area">
                <div id="username-toggle" class="account-link">
                    <span id="usernameDisplay"><?php echo htmlspecialchars($username); ?></span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                
                <div id="username-inputs" class="edit-username-inputs hidden">
                    <input type="text" placeholder="Enter new username" class="rounded-input" id="newUsername" value="<?php echo htmlspecialchars($username); ?>">
                    <input type="password" placeholder="Enter password to confirm" class="rounded-input" id="usernamePassword">
                </div>
            </div>

            <!-- Password Section -->
            <div id="password-area">
                <div id="password-toggle" class="account-link">
                    <span>Change password</span>
                    <span class="dropdown-arrow">▼</span>
                </div>
                
                <div id="password-inputs" class="edit-username-inputs hidden">
                    <input type="password" placeholder="Current password" class="rounded-input" id="currentPassword">
                    <input type="password" placeholder="New password" class="rounded-input" id="newPassword">
                    <input type="password" placeholder="Confirm new password" class="rounded-input" id="confirmPassword">
                </div>
            </div>
        </div>
        
        <div class="control-buttons">
            <button class="back-button" id="closeAccountPanel">Back</button>
            <button class="save-button" id="saveAccountChanges">Save</button>
        </div>

        <!-- Logout Button -->
        <button class="logout-button" id="logoutButton">Log Out</button>
    </div>

    <!-- Original Home Content -->
    <div id="square1"></div>
    <div id="square2"></div>
    <div id="square3"></div>

    <div id="home-link-group">
        <div class="home-text">Home</div>
        <div class="line"></div>
    </div>
    <div class="edit-text">Edit</div>
    <div class="name-text">
        <?php echo htmlspecialchars($fullname . "'s Closet"); ?>
    </div>
    <img src="<?php echo htmlspecialchars($profile_pic); ?>" 
         alt="Profile" 
         class="default_profile" 
         id="profileIcon"
         style="cursor: pointer;">

    <img src="logo.png" alt="Wearify" class="logo">
    <div class="rounded-upper" onclick="toggleColor(this)">Upper</div>
    <div class="rounded-lower" onclick="toggleColor(this)">Lower</div>
    <div class="rounded-shoes" onclick="toggleColor(this)">Shoes</div>
    <div class="rounded-eyewear" onclick="toggleColor(this)">Eyewear</div>
    <div class="rounded-bag" onclick="toggleColor(this)">Bag</div>
    <div class="rounded-headwear" onclick="toggleColor(this)">Headwear</div>
    <div class="rounded-accessory" onclick="toggleColor(this)">Accessory</div>
    <div class="rounded-socks" onclick="toggleColor(this)">Socks</div>
    <div id="recwhite"></div>

    <div id="group-upper" class="tooltip-trigger" style="left:215px; top:130px; width:195px; height:210px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_upper">
        <div id="bg-upper"></div>
        <span class="tooltip-text">Upper</span>
    </div>

    <div id="group-lower" class="tooltip-trigger" style="left:215px; top:350px; width:195px; height:210px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_lower">
        <div id="bg-lower"></div>
        <span class="tooltip-text">Lower</span>
    </div>

    <div id="group-shoes" class="tooltip-trigger" style="left:215px; top:570px; width:195px; height:150px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_shoes">
        <div id="bg-shoes"></div>
        <span class="tooltip-text">Shoes</span>
    </div>

    <div id="group-eyewear" class="tooltip-trigger" style="left:430px; top:160px; width:180px; height:135px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_eyewear">
        <div id="bg-eyewear"></div>
        <span class="tooltip-text">Eyewear</span>
    </div>

    <div id="group-bag" class="tooltip-trigger" style="left:15px; top:320px; width:180px; height:135px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_bag">
        <div id="bg-bag"></div>
        <span class="tooltip-text">Bag</span>
    </div>

    <div id="group-headwear" class="tooltip-trigger" style="left:15px; top:160px; width:180px; height:135px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_headwear">
        <div id="bg-headwear"></div>
        <span class="tooltip-text">Headwear</span>
    </div>

    <div id="group-accessory1" class="tooltip-trigger" style="left:430px; top:320px; width:180px; height:135px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_accessory1">
        <div id="bg-accessory1"></div>
        <span class="tooltip-text">Accessory 1</span>
    </div>

    <div id="group-accessory2" class="tooltip-trigger" style="left:430px; top:480px; width:180px; height:135px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_accessory2">
        <div id="bg-accessory2"></div>
        <span class="tooltip-text">Accessory 2</span>
    </div>

    <div id="group-socks" class="tooltip-trigger" style="left:25px; top:480px; width:150px; height:180px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_socks">
        <div id="bg-socks"></div>
        <span class="tooltip-text">Socks</span>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <p>Are you sure you want to delete this item?</p>
            <button id="confirmDelete">Yes</button>
            <button id="cancelDelete">No</button>
        </div>
    </div>

    <!-- Selection Modal -->
    <div id="selectionModal" class="modal">
        <div class="modal-content">
            <h3>Select Item</h3>
            <div id="selectionItems"></div>
            <button id="closeSelection">Close</button>
        </div>
    </div>

    <div id="scrollContainer">
        <div id="square4"></div>

        <div class="slide" id="slide-upper">
            <div class="home-clothes-container" id="home-upper-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>

        <div class="slide" id="slide-lower">
            <div class="home-clothes-container" id="home-lower-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>

        <div class="slide" id="slide-shoes">
            <div class="home-clothes-container" id="home-shoes-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>

        <div class="slide" id="slide-eyewear">
            <div class="home-clothes-container" id="home-eyewear-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>

        <div class="slide" id="slide-bag">
            <div class="home-clothes-container" id="home-bag-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>

        <div class="slide" id="slide-headwear">
            <div class="home-clothes-container" id="home-headwear-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>

        <div class="slide" id="slide-accessory">
            <div class="home-clothes-container" id="home-accessory-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>

        <div class="slide" id="slide-socks">
            <div class="home-clothes-container" id="home-socks-items">
                <p class="home-empty">No items yet. Please add some items!</p>
            </div>
        </div>
    </div>

    <script src="home.js"></script>
    <script>
        // Account Panel Management
        document.addEventListener("DOMContentLoaded", () => {
            document.body.classList.add("fade-in");

            const accountPanel = document.getElementById('accountPanel');
            const accountOverlay = document.getElementById('accountOverlay');
            const profileIcon = document.getElementById('profileIcon');
            const closeAccountPanel = document.getElementById('closeAccountPanel');
            const usernameToggle = document.getElementById('username-toggle');
            const usernameInputs = document.getElementById('username-inputs');
            const passwordToggle = document.getElementById('password-toggle');
            const passwordInputs = document.getElementById('password-inputs');
            const saveButton = document.getElementById('saveAccountChanges');
            const logoutButton = document.getElementById('logoutButton');
            const messageBox = document.getElementById('messageBox');
            const profilePhotoInput = document.getElementById('profilePhotoInput');
            const profilePhotoDisplay = document.getElementById('profilePhotoDisplay');
            const cameraIcon = document.querySelector('.camera-icon');

            // Open account panel
            profileIcon.addEventListener('click', () => {
                accountPanel.classList.add('active');
                accountOverlay.classList.add('active');
            });

            // Close account panel
            function closePanel() {
                accountPanel.classList.remove('active');
                accountOverlay.classList.remove('active');
                usernameInputs.classList.add('hidden');
                passwordInputs.classList.add('hidden');
                accountPanel.classList.remove('expanded');
            }

            closeAccountPanel.addEventListener('click', closePanel);
            accountOverlay.addEventListener('click', closePanel);

            // Toggle username inputs
            usernameToggle.addEventListener('click', () => {
                usernameInputs.classList.toggle('hidden');
                if (!usernameInputs.classList.contains('hidden') || !passwordInputs.classList.contains('hidden')) {
                    accountPanel.classList.add('expanded');
                } else {
                    accountPanel.classList.remove('expanded');
                }
            });

            // Toggle password inputs
            passwordToggle.addEventListener('click', () => {
                passwordInputs.classList.toggle('hidden');
                if (!usernameInputs.classList.contains('hidden') || !passwordInputs.classList.contains('hidden')) {
                    accountPanel.classList.add('expanded');
                } else {
                    accountPanel.classList.remove('expanded');
                }
            });

            // Profile photo upload
            cameraIcon.addEventListener('click', () => {
                profilePhotoInput.click();
            });

            profilePhotoInput.addEventListener('change', (e) => {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        profilePhotoDisplay.src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });

            // Logout functionality
            logoutButton.addEventListener('click', () => {
                if (confirm('Are you sure you want to log out?')) {
                    window.location.href = 'logout.php';
                }
            });

            // Save account changes
            saveButton.addEventListener('click', async () => {
                const formData = new FormData();
                
                const fullname = document.getElementById('fullnameInput').value.trim();
                if (fullname) {
                    formData.append('fullname', fullname);
                }

                const newUsername = document.getElementById('newUsername').value.trim();
                const usernamePassword = document.getElementById('usernamePassword').value;
                if (!usernameInputs.classList.contains('hidden') && newUsername) {
                    if (!usernamePassword) {
                        showMessage('Please enter your password to change username', 'error');
                        return;
                    }
                    formData.append('new_username', newUsername);
                    formData.append('username_password', usernamePassword);
                }

                const currentPassword = document.getElementById('currentPassword').value;
                const newPassword = document.getElementById('newPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                if (!passwordInputs.classList.contains('hidden') && (currentPassword || newPassword || confirmPassword)) {
                    if (!currentPassword || !newPassword || !confirmPassword) {
                        showMessage('Please fill all password fields', 'error');
                        return;
                    }
                    if (newPassword !== confirmPassword) {
                        showMessage('New passwords do not match', 'error');
                        return;
                    }
                    if (newPassword.length <= 1) {
                        showMessage('New password must be at least 1 character', 'error');
                        return;
                    }
                    formData.append('current_password', currentPassword);
                    formData.append('new_password', newPassword);
                }

                if (profilePhotoInput.files[0]) {
                    formData.append('profile_photo', profilePhotoInput.files[0]);
                }

                try {
                    const response = await fetch('account_handler.php', {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        showMessage(result.message, 'success');
                        
                        if (result.new_username) {
                            document.getElementById('usernameDisplay').textContent = result.new_username;
                        }

                        if (result.new_profile_pic) {
                            document.getElementById('profileIcon').src = result.new_profile_pic;
                        }

                        if (result.new_fullname) {
                            document.querySelector('.name-text').textContent = result.new_fullname + "'s Closet";
                        }

                        document.getElementById('currentPassword').value = '';
                        document.getElementById('newPassword').value = '';
                        document.getElementById('confirmPassword').value = '';
                        document.getElementById('usernamePassword').value = '';

                        setTimeout(() => {
                            closePanel();
                        }, 1500);
                    } else {
                        showMessage(result.message, 'error');
                    }
                } catch (error) {
                    showMessage('An error occurred. Please try again.', 'error');
                    console.error('Error:', error);
                }
            });

            function showMessage(message, type) {
                messageBox.textContent = message;
                messageBox.className = 'message-box ' + type + ' active';
                setTimeout(() => {
                    messageBox.classList.remove('active');
                }, 3000);
            }

            // Edit button functionality
            const editBtn = document.querySelector(".edit-text");
            if (editBtn) {
                editBtn.style.cursor = "pointer";
                editBtn.onclick = () => {
                    document.body.classList.add("fade-out");
                    setTimeout(() => {
                        window.location.href = "edit.php";
                    }, 500);
                };
            }
        });
    </script>
</body>
</html>