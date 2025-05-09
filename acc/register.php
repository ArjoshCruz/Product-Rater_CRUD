<?php 
  session_start();
  require_once '../core/models.php';
  require_once '../core/dbConfig.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Product Rater</title>

    <!-- Tailwind Link -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Flowbite Link -->
    <link
      href="https://cdn.jsdelivr.net/npm/flowbite@3.1.2/dist/flowbite.min.css"
      rel="stylesheet"
    />
  </head>
  <body class="bg-gradient-to-r from-purple-500 via-purple-600 to-purple-700 min-h-screen flex flex-col items-center justify-center p-4">

    <header class="mb-6">
      <h1 class="text-4xl font-bold text-white">Product Rater</h1>
    </header>

    <?php
    if (isset($_SESSION['message'])) {
      $color = (isset($_SESSION['status']) && $_SESSION['status'] === 'success') ? 'text-green-600' : 'text-red-600';
      echo "<h2 class='text-lg font-semibold mb-4 $color'>{$_SESSION['message']}</h2>";
      unset($_SESSION['message'], $_SESSION['status']);
    }
    ?>

    <section class="w-full max-w-md bg-white p-6 rounded-lg shadow-md">
      <h2 class="text-2xl font-bold mb-4 text-center">REGISTER</h2>
      <form id="registerForm" action="../core/handleForms.php" method="POST" class="space-y-4">
        <div>
          <label for="username" class="block font-medium">Username</label>
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Arjottt"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
          />
        </div>
        <div>
          <label for="email" class="block font-medium">Email</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="arjottt@gmail.com"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
          />
        </div>
        <div>
          <label for="password" class="block font-medium">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="**********"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
          />
        </div>
        <div>
          <label for="confirm_password" class="block font-medium">Confirm Password</label>
          <input
            type="password"
            id="confirm_password"
            name="confirm_password"
            placeholder="**********"
            required
            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500"
          />
        </div>
        <div class="text-center">
          <input
            class="bg-gradient-to-r from-purple-500 to-purple-700 text-black hover:text-white font-semibold px-6 py-2 rounded-md hover:bg-purple-800 transition duration-200 cursor-pointer"
            type="submit"
            name="registerUserBtn"
            value="Register"
          />
        </div>
      </form>
      <p class="mt-4 text-center text-sm text-gray-600">
        Already have an account?
        <a href="login.php" class="text-blue-600 hover:underline">Login here</a>
      </p>
    </section>

    <!-- Script -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../scripts/script.js"></script>
  </body>
</html>
