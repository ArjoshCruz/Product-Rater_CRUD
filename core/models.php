<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ----------------------- User Account ----------------------- //

function insertNewUser($pdo, $username, $email, $password) {
    $response = array();

    $checkIfUserExists = checkIfUserExists($pdo, $email);

    if (!$checkIfUserExists['result']) {
        $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);

        if ($stmt->execute([$username, $email, $password])) {
            insertAuditLog($pdo, $username, 'Created account', $pdo->lastInsertId(), 'Email: ' . $email);
            $response = array(
                'result' => true,
                'status' => '200',
                'message' => 'User created successfully'
            );
        } else {
            $response = array(
                'result' => false,
                'status' => '400',
                'message' => 'Error creating user'
            );
        }
    } else {
        $response = array(
            'result' => false,
            'status' => '409',
            'message' => 'User already exists'
        );
    }
    return $response;
}

function checkIfUserExists($pdo, $email) {
    $response = array();
    $sql = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute([$email])) {
        $userInfoArray = $stmt->fetch();
        if ($stmt->rowCount() > 0) {
            $response = array(
                'result' => true,
                'status' => '200',
                'userInfo' => $userInfoArray
            );
        } else {
            $response = array(
                'result' => false,
                'status' => '404',
                'message' => 'User not found'
            );
        }
    }

    return $response;
}

// ----------------------- Product Review ----------------------- //

function insertNewProduct($pdo, $name, $description, $user_id) {
    $sql = "INSERT INTO products (name, description, user_id) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$name, $description, $user_id])) {
        insertAuditLog($pdo, $_SESSION['username'], 'Created a new product', $pdo->lastInsertId(), 'Name: ' . $name);
        return array(
            'result' => true,
            'status' => '200',
            'message' => 'Product created successfully'
        );
    } else {
        return array(
            'result' => false,
            'status' => '400',
            'message' => 'Error creating product'
        );
    }
}

function getAllProducts($pdo) {
    $sql = "SELECT products.*, users.username 
            FROM products 
            JOIN users ON products.user_id = users.id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}

function getProductByID($pdo, $product_id) {
    $sql = "SELECT * FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    return $stmt->fetch();
}

function insertNewReviewInAPost($pdo, $product_id, $stars, $reviewText, $user_id) {
    $sql = "INSERT INTO reviews (product_id, stars, review_text, user_id, created_by, updated_by)
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$product_id, $stars, $reviewText, $user_id, $user_id, $user_id])) {
        insertAuditLog($pdo, $_SESSION['username'], 'Created a new review', $pdo->lastInsertId(), 'Product ID: ' . $product_id);
        return [
            'result' => true,
            'status' => '200',
            'message' => 'Review created successfully'
        ];
    } else {
        return [
            'result' => false,
            'status' => '400',
            'message' => 'Error creating review'
        ];
    }
}

function getReviewsByProductId($pdo, $product_id) {
    $sql = "SELECT reviews.*, users.username 
            FROM reviews 
            JOIN users ON reviews.user_id = users.id 
            WHERE product_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updateProduct($pdo, $product_id, $name, $description) {
    $sql = "UPDATE products SET name = ?, description = ? WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    insertAuditLog($pdo, $_SESSION['username'], 'Updated product', $product_id, 'Name: ' . $name);
    return $stmt->execute([$name, $description, $product_id]);
}

function deleteProduct($pdo, $product_id) {
    $sql = "DELETE FROM products WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    insertAuditLog($pdo, $_SESSION['username'], 'Deleted product', $product_id, 'Product ID: ' . $product_id);
    return $stmt->execute([$product_id]);
}

function updateReview($pdo, $review_id, $review_text, $stars, $user_id) {
    $sql = "UPDATE reviews SET review_text = ?, stars = ?, updated_at = NOW() WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    insertAuditLog($pdo, $_SESSION['username'], 'Updated review', $review_id, 'Stars: ' . $stars);
    return $stmt->execute([$review_text, $stars, $review_id, $user_id]);
}

function deleteReview($pdo, $review_id, $user_id) {
    $sql = "DELETE FROM reviews WHERE id = ? AND user_id = ?";
    $stmt = $pdo->prepare($sql);
    insertAuditLog($pdo, $_SESSION['username'], 'Deleted review', $review_id, 'Review ID: ' . $review_id);
    return $stmt->execute([$review_id, $user_id]);
}

// ----------------------- Audit Log ----------------------- //

function insertAuditLog($pdo, $username, $action_made, $attribute_id, $details) {
    $sql = "INSERT INTO audit_log (username, action_made, attribute_id, details) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $action_made, $attribute_id, $details]);
}

function getAllAuditLog($pdo) {
    $sql = "SELECT * FROM audit_log ORDER BY created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll();
}
?>
