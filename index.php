<?php
// index.php
require_once 'config.php';

// Redirect to dashboard if already logged in
if (isLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        require_once 'auth.php';
        $result = registerUser($_POST['name'], $_POST['email'], $_POST['password']);
        if ($result === true) {
            $success = "Registration successful! Please login.";
        } else {
            $error = $result;
        }
    } elseif (isset($_POST['login'])) {
        require_once 'auth.php';
        $result = loginUser($_POST['email'], $_POST['password']);
        if ($result === true) {
            header("Location: dashboard.php");
            exit();
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QuickShare - Temporary File & Text Sharing</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-4xl font-bold text-indigo-600 mb-2">QuickShare</h1>
                <h2 class="text-2xl font-semibold text-gray-900">
                    Temporary File & Text Sharing
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Share files and text with self-destructing links
                </p>
            </div>
            
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>
            
            <div class="bg-white p-6 rounded-lg shadow-lg">
                <div class="mb-4 border-b border-gray-200">
                    <ul class="flex flex-wrap -mb-px text-sm font-medium text-center">
                        <li class="mr-2">
                            <button id="login-tab" class="inline-block p-4 text-indigo-600 border-b-2 border-indigo-600 active">Login</button>
                        </li>
                        <li class="mr-2">
                            <button id="register-tab" class="inline-block p-4 text-gray-500 border-b-2 border-transparent">Register</button>
                        </li>
                    </ul>
                </div>
                
                <!-- Login Form -->
                <div id="login-form">
                    <form class="mt-8 space-y-6" method="POST">
                        <input type="hidden" name="login" value="1">
                        <div class="rounded-md shadow-sm -space-y-px">
                            <div>
                                <label for="login-email" class="sr-only">Email address</label>
                                <input id="login-email" name="email" type="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Email address">
                            </div>
                            <div>
                                <label for="login-password" class="sr-only">Password</label>
                                <input id="login-password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Password">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Sign in
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Register Form -->
                <div id="register-form" class="hidden">
                    <form class="mt-8 space-y-6" method="POST">
                        <input type="hidden" name="register" value="1">
                        <div class="rounded-md shadow-sm -space-y-px">
                            <div>
                                <label for="register-name" class="sr-only">Full Name</label>
                                <input id="register-name" name="name" type="text" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Full Name">
                            </div>
                            <div>
                                <label for="register-email" class="sr-only">Email address</label>
                                <input id="register-email" name="email" type="email" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Email address">
                            </div>
                            <div>
                                <label for="register-password" class="sr-only">Password</label>
                                <input id="register-password" name="password" type="password" required class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm" placeholder="Password">
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                Register
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('login-tab').addEventListener('click', function() {
            document.getElementById('login-form').classList.remove('hidden');
            document.getElementById('register-form').classList.add('hidden');
            document.getElementById('login-tab').classList.add('text-indigo-600', 'border-indigo-600');
            document.getElementById('login-tab').classList.remove('text-gray-500', 'border-transparent');
            document.getElementById('register-tab').classList.remove('text-indigo-600', 'border-indigo-600');
            document.getElementById('register-tab').classList.add('text-gray-500', 'border-transparent');
        });

        document.getElementById('register-tab').addEventListener('click', function() {
            document.getElementById('register-form').classList.remove('hidden');
            document.getElementById('login-form').classList.add('hidden');
            document.getElementById('register-tab').classList.add('text-indigo-600', 'border-indigo-600');
            document.getElementById('register-tab').classList.remove('text-gray-500', 'border-transparent');
            document.getElementById('login-tab').classList.remove('text-indigo-600', 'border-indigo-600');
            document.getElementById('login-tab').classList.add('text-gray-500', 'border-transparent');
        });
    </script>
</body>
</html>