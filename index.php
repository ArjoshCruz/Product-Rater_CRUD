<?php
session_start();
require_once 'core/dbConfig.php';
require_once 'core/models.php';

if (!isset($_SESSION['username'])) {
    header("Location: acc/login.php");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Rater</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen">

    <!-- Header -->
    <header class="bg-gradient-to-r from-purple-600 via-indigo-500 to-pink-400 text-white p-4 flex items-center justify-between shadow-md rounded-md mb-6 w-full">
    <h1 class="text-3xl font-bold">Product Rater</h1>
    <nav class="space-x-4">
        <a href="#audit-log" class="hover:underline">Audit Log</a>
        <a href="core/handleForms.php?logoutAUser=1" class="underline hover:text-gray-200">Logout</a>
    </nav>
    </header>
    
    <!-- Main Content -->
    <main class="flex justify-center items-center min-h-screen p-4">
        <div class="w-full max-w-3xl bg-white p-8 rounded-lg shadow-lg">

            <!-- Greeting -->
            <h1 class="text-2xl font-semibold mb-4 text-center text-purple-800">Hello, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>

            <!-- Create Review Form -->
            <div class="bg-white p-6 rounded-lg shadow-lg mb-6">
                <h2 class="text-xl font-medium mb-4 text-purple-800">Create a Review</h2>
                <form id="reviewForm" action="core/handleForms.php" method="POST">
                    <div class="mb-4">
                        <label for="productName" class="block text-sm font-medium text-gray-700">Product Name:</label>
                        <input type="text" name="productName" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700">Product Description:</label>
                        <textarea id="reviewText" name="description" rows="5" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" placeholder="Write your review here..." required></textarea>
                    </div>

                    <div class="mb-4">
                        <input class="bg-purple-600 text-white px-6 py-2 rounded-lg cursor-pointer" type="submit" name="insertProductBtn" value="Insert Review">
                    </div>
                </form>
            </div>

            <hr class="my-6">

            <!-- Display Reviews -->
            <div>
                <?php
                $reviews = getAllProducts($pdo); 

                if (empty($reviews)) {
                    echo "<p class='text-center text-gray-600'>No products found.</p>";
                } else {
                    foreach ($reviews as $review) {
                        ?>
                        <div class="product-review bg-white p-6 rounded-lg shadow-lg mb-6" id="product-<?= $review['id']; ?>">
                            <h3 class="text-xl font-semibold text-purple-800">Product Name: <?= htmlspecialchars($review['name']); ?></h3>
                            <p class="text-sm text-gray-600">Posted by: <?= htmlspecialchars($review['username']); ?></p>
                            <p class="mt-2"><?= nl2br(htmlspecialchars($review['description'])); ?></p>

                            <?php if ($_SESSION['user_id'] == $review['user_id']): ?>
                                <div class="mt-4">
                                    <button class="editBtn bg-yellow-500 text-white px-4 py-2 rounded-md" data-id="<?= $review['id']; ?>" data-name="<?= htmlspecialchars($review['name']); ?>" data-description="<?= htmlspecialchars($review['description']); ?>">
                                        Edit Product
                                    </button>
                                    <button class="deleteBtn bg-red-500 text-white px-4 py-2 rounded-md" data-id="<?= $review['id']; ?>">
                                        Delete Product
                                    </button>
                                </div>
                            <?php endif; ?>

                            <div class="editFormContainer hidden mt-4" id="editFormContainer-<?= $review['id']; ?>">
                                <h2 class="text-xl font-medium text-purple-800">Edit Product</h2>
                                <form class="editProductForm" data-product-id="<?= $review['id']; ?>">
                                    <input type="hidden" name="product_id" value="<?= $review['id']; ?>">
                                    <div class="mb-4">
                                        <label for="editProductName-<?= $review['id']; ?>" class="block text-sm font-medium text-gray-700">Product Name:</label>
                                        <input type="text" name="product_name" id="editProductName-<?= $review['id']; ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                    </div>
                                    <div class="mb-4">
                                        <label for="editDescription-<?= $review['id']; ?>" class="block text-sm font-medium text-gray-700">Description:</label>
                                        <textarea name="description" id="editDescription-<?= $review['id']; ?>" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" required></textarea>
                                    </div>
                                    <div class="mb-4">
                                        <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded-lg">Save Changes</button>
                                        <button type="button" class="cancelEdit bg-gray-500 text-white px-6 py-2 rounded-lg">Cancel</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Display reviews for each product -->
                            <?php
                            $comments = getReviewsByProductId($pdo, $review['id']);
                            if ($comments && count($comments) > 0): ?>
                                <div class="comments mt-6">
                                    <h4 class="text-lg font-medium text-purple-800">Reviews:</h4>
                                    <ul class="space-y-4">
                                        <?php foreach ($comments as $comment): ?>
                                        <li class="border-t pt-4">
                                            <?php if ($_SESSION['user_id'] == $comment['user_id']): ?>
                                                <div class="flex space-x-4 mb-2">
                                                    <button class="editCommentBtn bg-yellow-500 text-white px-4 py-2 rounded-md" 
                                                            data-id="<?= $comment['id']; ?>">
                                                        Edit Comment
                                                    </button>
                                                    <button class="deleteCommentBtn bg-red-500 text-white px-4 py-2 rounded-md" 
                                                            data-id="<?= $comment['id']; ?>">
                                                        Delete Comment
                                                    </button>
                                                </div>
                                            <?php endif; ?>

                                            <p><strong>Username:</strong> <?= htmlspecialchars($comment['username']) ?></p>
                                            
                                            <!-- Rating Display/Edit -->
                                            <p class="mb-2">
                                                <strong>Rating:</strong>
                                                <span class="display-rating" id="rating-display-<?= $comment['id']; ?>">
                                                    <?= htmlspecialchars($comment['stars']) ?>/5
                                                </span>
                                                <select class="edit-rating hidden" id="rating-edit-<?= $comment['id']; ?>">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <option value="<?= $i; ?>" <?= $i == $comment['stars'] ? 'selected' : '' ?>>
                                                            <?= $i; ?>
                                                        </option>
                                                    <?php endfor; ?>
                                                </select>
                                            </p>
                                            
                                            <!-- Comment Display/Edit -->
                                            <p class="mb-2">
                                                <strong>Comment:</strong>
                                                <span class="display-comment" id="comment-text-<?= $comment['id']; ?>">
                                                    <?= nl2br(htmlspecialchars($comment['review_text'])) ?>
                                                </span>
                                                <textarea class="edit-comment hidden w-full px-3 py-2 border rounded" 
                                                        id="comment-edit-<?= $comment['id']; ?>" 
                                                        rows="3"><?= htmlspecialchars($comment['review_text']) ?></textarea>
                                            </p>
                                            
                                            <!-- Save Button -->
                                            <button class="saveCommentBtn bg-green-500 text-white px-4 py-2 rounded-md hidden" 
                                                    data-id="<?= $comment['id']; ?>">
                                                Save Changes
                                            </button>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php else: ?>
                                <p>No reviews yet.</p>
                            <?php endif; ?>

                            <!-- Form for submitting a comment -->
                            <form action="core/handleForms.php" method="POST" class="mt-6">
                                <input type="hidden" name="product_id" value="<?= $review['id']; ?>">
                                <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">

                                <div class="mb-4">
                                    <label for="stars" class="block text-sm font-medium text-gray-700">Stars (1-5):</label>
                                    <select name="stars" required class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <option value="<?= $i; ?>"><?= $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="reviewText" class="block text-sm font-medium text-gray-700">Your Comment:</label>
                                    <textarea name="reviewText" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500" required></textarea>
                                </div>

                                <div class="mb-4">
                                    <input type="submit" name="submitReviewBtn" value="Submit Comment" class="bg-purple-600 text-white px-6 py-2 rounded-lg cursor-pointer">
                                </div>
                            </form>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    </main>

    <!-- Audit Logs Section -->
    <section id="audit-log" class="bg-purple-100 p-6 mt-8 rounded-lg">
        <h2 class="text-3xl font-semibold mb-4 text-purple-800 text-center">Audit Logs</h2>

        <?php $logs = getAllAuditLog($pdo); ?>
        <?php if (!empty($logs)) : ?>
            <?php foreach ($logs as $log) : ?>
                <p class="text-gray-700 mb-2">
                    <strong><?php echo htmlspecialchars($log['username']); ?></strong>  
                    <?php echo htmlspecialchars($log['action_made']); ?> 
                    at <strong><?php echo date("F j, Y, g:i a", strtotime($log['created_at'])); ?></strong>
                </p>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No audit logs available.</p>
        <?php endif; ?>
    </section>

    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="scripts/script.js"></script>

</body>
</html>
