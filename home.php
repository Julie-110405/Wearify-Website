<?php
session_start();
// Include the database connection
include(__DIR__ . "/config/db_connect.php");

// Fetch user fullname based on session user_id
$fullname = "User"; // default value

try {
    $stmt = $pdo->prepare("SELECT fullname FROM users WHERE id = :user_id");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $row = $stmt->fetch();

    if ($row) {
        $fullname = $row['fullname'];
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
</head>
<body>
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
    <img src="default_profile.png" alt="Profile" class="default_profile">
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

    <div id="group-lower" class="tooltip-trigger" style="left:215px; top:365px; width:195px; height:210px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_lower">
        <div id="bg-lower"></div>
        <span class="tooltip-text">Lower</span>
    </div>

    <div id="group-shoes" class="tooltip-trigger" style="left:215px; top:600px; width:195px; height:150px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_shoes">
        <div id="bg-shoes"></div>
        <span class="tooltip-text">Shoes</span>
    </div>

    <div id="group-eyewear" class="tooltip-trigger" style="left:430px; top:160px; width:180px; height:135px;">
        <img src="remove_icon.png" alt="Wearify" class="remove_eyewear">
        <div id="bg-eyewear"></div>
        <span class="tooltip-text">Eyewear</span>
    </div>

    <div id="group-bag" class="tooltip-trigger" style="left:15px; top:320px; width:180px; height:135px; z-index: 99999;">
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

    <div id="group-socks" class="tooltip-trigger" style="left:25px; top:480px; width:150px; height:180px; z-index: 99999;">
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

        <!-- All slides remain unchanged -->
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
        // Removed inline toggleColor function to prevent duplicate declaration of activeElement
        // toggleColor is handled in home.js now which declares activeElement properly
    </script>
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        document.body.classList.add("fade-in"); // fade in when page loads

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
