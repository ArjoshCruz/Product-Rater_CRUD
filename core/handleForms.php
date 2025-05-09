<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'dbConfig.php';
require_once 'models.php';
require_once 'validate.php';

header('Content-Type: application/json');


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            echo json_encode(['result' => false, 'message' => 'Passwords do not match.']);
            exit;
        }

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $result = insertNewUser($pdo, $username, $email, $hashedPassword);
        echo json_encode($result);
        exit;
    }
}

// Login AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'login') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Ensure fields are not empty
        if (empty($email) || empty($password)) {
            echo json_encode(['result' => false, 'message' => 'Please fill in both fields.']);
            exit;
        }

        $loginQuery = checkIfUserExists($pdo, $email);

        // Check if user exists
        if ($loginQuery['result']) {
            $userPasswordFromDB = $loginQuery['userInfo']['password'];

            if (password_verify($password, $userPasswordFromDB)) {
                // Successful login, start session
                $_SESSION['user_id'] = $loginQuery['userInfo']['id'];
                $_SESSION['username'] = $loginQuery['userInfo']['username'];

                echo json_encode(['result' => true, 'message' => 'Login successful.']);
                exit;
            } else {
                // Incorrect password
                echo json_encode(['result' => false, 'message' => 'Incorrect password.']);
                exit;
            }
        } else {
            // User not found
            echo json_encode(['result' => false, 'message' => 'No user found with that email.']);
            exit;
        }
    }
}


if (isset($_GET['logoutAUser'])) {
    unset($_SESSION['email']);
    unset($_SESSION['username']);
    header("Location: ../acc/login.php");
    exit;
}

// ----------------------- Product Review ----------------------- //

// Inserting a new post into the database
if (isset($_POST['insertProductBtn'])) {
    $productName = sanitizeInput($_POST['productName']);
    $description = sanitizeInput($_POST['description']);
    $user_id = $_SESSION['user_id'];

    if (!empty($productName) && !empty($description) && !empty($user_id)) {
        $insertQuery = insertNewProduct($pdo, $productName, $description, $user_id);

        if ($insertQuery['status'] == '200') {
            $_SESSION['message'] = $insertQuery['message'];
            $_SESSION['status'] = $insertQuery['status'];
            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['message'] = $insertQuery['message'];
            $_SESSION['status'] = $insertQuery['status'];
            header("Location: ../index.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "Please make sure the input fields are not empty for the review!";
        $_SESSION['status'] = '400';
        header("Location: ../index.php");
        exit;
    }
}

// Inserting a new review in a post into the database
if (isset($_POST['submitReviewBtn'])) {
    $stars = sanitizeInput($_POST['stars']);
    $reviewText = sanitizeInput($_POST['reviewText']);
    $product_id = sanitizeInput($_POST['product_id']);
    $user_id = $_SESSION['user_id'];

    if (!empty($stars) && !empty($reviewText) && !empty($product_id)) {
        $insertQuery = insertNewReviewInAPost($pdo, $product_id, $stars, $reviewText, $user_id);

        $_SESSION['message'] = $insertQuery['message'];
        $_SESSION['status'] = $insertQuery['status'];
        header("Location: ../index.php");
        exit;
    } else {
        $_SESSION['message'] = "Please make sure all input fields are filled out!";
        $_SESSION['status'] = '400';
        header("Location: ../index.php");
        exit;
    }
}

// Get all reviews by product ID
if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $reviews = getAllReviewsByProductID($pdo, $product_id);
    echo json_encode($reviews);
    exit;
}


// ----------------------------------- AJAX ----------------------------------- //
if (isset($_POST['updateProductBtn'])) {
    $productId = $_POST['product_id'];
    $name = $_POST['product_name'];
    $description = $_POST['description'];
    
    // Verify the user owns this product
    $stmt = $pdo->prepare("SELECT user_id FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();
    
    if ($product && $product['user_id'] == $_SESSION['user_id']) {
        updateProduct($pdo, $productId, $name, $description);
        echo json_encode(['status' => 'success']);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
        exit;
    }
}

if (isset($_POST['deleteBtn'])) {
    $productId = $_POST['product_id'];

    // Check if the logged-in user owns the product
    $stmt = $pdo->prepare("SELECT user_id FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch();

    if ($product && $product['user_id'] == $_SESSION['user_id']) {
        if (deleteProduct($pdo, $productId)) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete product']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    }
    exit;
}

if (isset($_POST['updateCommentBtn'])) {
    header('Content-Type: application/json');
    
    try {
        $success = updateReview($pdo, $_POST['review_id'], $_POST['review_text'], $_POST['stars'], $_SESSION['user_id']);
        echo json_encode([
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Comment updated successfully' : 'Failed to update comment'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

if (isset($_POST['deleteCommentBtn'])) {
    header('Content-Type: application/json');
    
    try {
        $success = deleteReview($pdo, $_POST['review_id'], $_SESSION['user_id']); // Changed parameter name
        echo json_encode([
            'status' => $success ? 'success' : 'error',
            'message' => $success ? 'Comment deleted successfully' : 'Failed to delete comment'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit();
}


?>