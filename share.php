<?php
// share.php
require_once 'config.php';
require_once 'share_functions.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$unique_id = $_GET['id'];
$share = getShare($unique_id);

if (!$share) {
    die("Share not found or has expired.");
}

// Check if share is expired
if (isShareExpired($share)) {
    deleteShare($share['id']);
    die("This share has expired.");
}

// Increment view count (only if not already expired)
incrementViewCount($share['id']);

// Get updated share data after incrementing view count
$share = getShare($unique_id);

// Check again after incrementing (for view-based expiry)
if (isShareExpired($share)) {
    deleteShare($share['id']);
    die("This share has expired.");
}

// Serve content based on type
if ($share['type'] === 'file') {
    // Serve file for download
    if (file_exists($share['file_path'])) {
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($share['file_name']) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($share['file_path']));
        readfile($share['file_path']);
        exit;
    } else {
        die("File not found.");
    }
} else {
    // Display text content
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Shared Text - QuickShare</title>
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="bg-gray-100 min-h-screen">
        <div class="max-w-4xl mx-auto py-8 px-4">
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="bg-indigo-600 px-6 py-4">
                    <h1 class="text-xl font-semibold text-white">Shared Text</h1>
                    <p class="text-indigo-200 text-sm mt-1">
                        This content will expire after <?php echo $share['expiry_value']; ?> <?php echo $share['expiry_type'] === 'views' ? 'view(s)' : 'hour(s)'; ?> | 
                        Views: <?php echo $share['views_count']; ?>
                    </p>
                </div>
                <div class="p-6">
                    <pre class="whitespace-pre-wrap font-sans text-gray-800"><?php echo htmlspecialchars($share['text_content']); ?></pre>
                </div>
                <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                    <button onclick="copyText()" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700">
                        Copy Text
                    </button>
                    <a href="<?php echo BASE_URL; ?>dashboard.php" class="ml-2 bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                        Share Your Own
                    </a>
                </div>
            </div>
        </div>

        <script>
            function copyText() {
                const text = `<?php echo addslashes($share['text_content']); ?>`;
                navigator.clipboard.writeText(text).then(function() {
                    alert('Text copied to clipboard!');
                }, function(err) {
                    console.error('Could not copy text: ', err);
                    // Fallback for older browsers
                    const textArea = document.createElement('textarea');
                    textArea.value = text;
                    document.body.appendChild(textArea);
                    textArea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textArea);
                    alert('Text copied to clipboard!');
                });
            }
        </script>
    </body>
    </html>
    <?php
}
?>