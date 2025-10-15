<?php
// dashboard.php
require_once 'config.php';
requireLogin();

require_once 'share_functions.php';

$message = '';
$share_url = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['share_text'])) {
        $text_content = $_POST['text_content'];
        $expiry_type = $_POST['expiry_type'];
        $expiry_value = $_POST['expiry_value'];
        
        if (empty($text_content)) {
            $message = "Please enter some text content.";
        } else {
            $data = ['text_content' => $text_content];
            $unique_id = createShare($_SESSION['user_id'], 'text', $data, $expiry_type, $expiry_value);
            
            if ($unique_id) {
                $share_url = BASE_URL . "share.php?id=" . $unique_id;
                $message = "Text shared successfully!";
            } else {
                $message = "Failed to create share.";
            }
        }
    } elseif (isset($_POST['share_file'])) {
        if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['file'];
            
            // Check file size
            if ($file['size'] > MAX_FILE_SIZE) {
                $message = "File size exceeds maximum limit of 50MB.";
            } else {
                // Generate unique filename
                $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
                $unique_filename = uniqid() . '_' . $file['name'];
                $file_path = UPLOAD_DIR . $unique_filename;
                
                // Move uploaded file
                if (move_uploaded_file($file['tmp_name'], $file_path)) {
                    $data = [
                        'file_path' => $file_path,
                        'file_name' => $file['name'],
                        'file_size' => $file['size']
                    ];
                    
                    $expiry_type = $_POST['expiry_type'];
                    $expiry_value = $_POST['expiry_value'];
                    
                    $unique_id = createShare($_SESSION['user_id'], 'file', $data, $expiry_type, $expiry_value);
                    
                    if ($unique_id) {
                        $share_url = BASE_URL . "share.php?id=" . $unique_id;
                        $message = "File shared successfully!";
                    } else {
                        unlink($file_path); // Clean up if database insert fails
                        $message = "Failed to create share.";
                    }
                } else {
                    $message = "Failed to upload file.";
                }
            }
        } else {
            $message = "Please select a valid file.";
        }
    }
}

// Get user's shares
$user_shares = getUserShares($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - QuickShare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-semibold text-indigo-600">QuickShare</h1>
                </div>
                <div class="flex items-center">
                    <span class="text-gray-700 mr-4">Welcome, <?php echo $_SESSION['user_name']; ?></span>
                    <a href="logout.php" class="text-gray-700 hover:text-indigo-600">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto py-6 sm:px-6 lg:px-8">
        <div class="px-4 py-6 sm:px-0">
            <?php if ($message): ?>
                <div class="mb-4 p-4 rounded-md <?php echo str_starts_with($message, 'Failed') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if ($share_url): ?>
                <div class="mb-4 p-4 bg-blue-100 border border-blue-400 text-blue-700 rounded">
                    <p class="font-semibold">Share this URL:</p>
                    <div class="flex items-center mt-2">
                        <input type="text" value="<?php echo $share_url; ?>" readonly class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md">
                        <button onclick="copyToClipboard('<?php echo $share_url; ?>')" class="bg-indigo-600 text-white px-4 py-2 rounded-r-md hover:bg-indigo-700">
                            Copy
                        </button>
                    </div>
                    <p class="text-sm mt-2">This link will expire after <?php echo $_POST['expiry_value']; ?> <?php echo $_POST['expiry_type'] === 'views' ? 'view(s)' : 'hour(s)'; ?></p>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Text Sharing Form -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Share Text</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="share_text" value="1">
                            <div class="space-y-4">
                                <div>
                                    <label for="text_content" class="block text-sm font-medium text-gray-700">Text Content</label>
                                    <textarea id="text_content" name="text_content" rows="6" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Paste your text here..."></textarea>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="text_expiry_type" class="block text-sm font-medium text-gray-700">Expire After</label>
                                        <select id="text_expiry_type" name="expiry_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="views">Views</option>
                                            <option value="time">Hours</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="text_expiry_value" class="block text-sm font-medium text-gray-700">Value</label>
                                        <input type="number" id="text_expiry_value" name="expiry_value" min="1" max="100" value="1" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                
                                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Share Text
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- File Sharing Form -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Share File</h3>
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="share_file" value="1">
                            <div class="space-y-4">
                                <div>
                                    <label for="file" class="block text-sm font-medium text-gray-700">Select File</label>
                                    <input type="file" id="file" name="file" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    <p class="text-xs text-gray-500 mt-1">Max file size: 50MB</p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label for="file_expiry_type" class="block text-sm font-medium text-gray-700">Expire After</label>
                                        <select id="file_expiry_type" name="expiry_type" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                            <option value="views">Views</option>
                                            <option value="time">Hours</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="file_expiry_value" class="block text-sm font-medium text-gray-700">Value</label>
                                        <input type="number" id="file_expiry_value" name="expiry_value" min="1" max="100" value="1" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                    </div>
                                </div>
                                
                                <button type="submit" class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    Share File
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Share History -->
            <div class="mt-8 bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Your Share History</h3>
                    <div class="space-y-4">
                        <?php if (empty($user_shares)): ?>
                            <p class="text-gray-500 text-center py-4">No shares yet. Create your first share above!</p>
                        <?php else: ?>
                            <?php foreach ($user_shares as $share): ?>
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo $share['type'] === 'file' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'; ?>">
                                                    <?php echo ucfirst($share['type']); ?>
                                                </span>
                                                <span class="text-sm text-gray-500">
                                                    <?php echo $share['unique_id']; ?>
                                                </span>
                                            </div>
                                            <p class="mt-1 text-sm font-medium text-gray-900">
                                                <?php if ($share['type'] === 'file'): ?>
                                                    <?php echo htmlspecialchars($share['file_name']); ?>
                                                    <span class="text-gray-500 text-xs ml-2">
                                                        (<?php echo formatFileSize($share['file_size']); ?>)
                                                    </span>
                                                <?php else: ?>
                                                    <?php echo substr(strip_tags($share['text_content']), 0, 100) . (strlen($share['text_content']) > 100 ? '...' : ''); ?>
                                                <?php endif; ?>
                                            </p>
                                            <div class="mt-1 text-xs text-gray-500">
                                                Expires after: <?php echo $share['expiry_value']; ?> <?php echo $share['expiry_type'] === 'views' ? 'view(s)' : 'hour(s)'; ?> |
                                                Views: <?php echo $share['views_count']; ?> |
                                                Created: <?php echo date('M j, Y g:i A', strtotime($share['created_at'])); ?>
                                            </div>
                                        </div>
                                        <a href="share.php?id=<?php echo $share['unique_id']; ?>" target="_blank" class="ml-4 bg-indigo-600 text-white px-3 py-1 rounded text-sm hover:bg-indigo-700">
                                            View
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                alert('URL copied to clipboard!');
            }, function(err) {
                console.error('Could not copy text: ', err);
            });
        }
    </script>
</body>
</html>