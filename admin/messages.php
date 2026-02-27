<?php
require_once __DIR__ . '/includes/header.php';

$message = '';
$messageType = '';

// Handle Delete Request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM messages WHERE id = ?");
    if ($stmt->execute([$id])) {
        $message = "Message deleted successfully.";
        $messageType = "success";
    } else {
        $message = "Failed to delete message.";
        $messageType = "error";
    }
}

// Fetch Messages
$stmt = $pdo->query("SELECT * FROM messages ORDER BY created_at DESC");
$messages = $stmt->fetchAll();
?>

<div class="flex items-center justify-between mb-8">
    <h2 class="text-3xl font-bold text-space-indigo">Contact Messages</h2>
</div>

<?php if ($message): ?>
    <div class="mb-6 p-4 rounded-lg <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<div class="bg-white rounded-xl shadow-sm border border-almond-silk overflow-hidden">
    <?php if(count($messages) > 0): ?>
        <div class="overflow-x-auto">
            <table class="w-full text-left whitespace-nowrap">
                <thead>
                    <tr class="bg-parchment border-b border-almond-silk">
                        <th class="py-3 px-6 font-semibold text-space-indigo">Date</th>
                        <th class="py-3 px-6 font-semibold text-space-indigo">Name</th>
                        <th class="py-3 px-6 font-semibold text-space-indigo">Email</th>
                        <th class="py-3 px-6 font-semibold text-space-indigo">Subject</th>
                        <th class="py-3 px-6 text-center font-semibold text-space-indigo">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-almond-silk">
                    <?php foreach($messages as $msg): ?>
                        <tr class="hover:bg-parchment hover:bg-opacity-50 transition-colors">
                            <td class="py-4 px-6 text-sm text-dusty-grape">
                                <?php echo date('M d, Y H:i', strtotime($msg['created_at'])); ?>
                            </td>
                            <td class="py-4 px-6 font-medium text-space-indigo">
                                <?php echo htmlspecialchars($msg['name']); ?>
                            </td>
                            <td class="py-4 px-6 text-sm text-dusty-grape">
                                <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="hover:text-space-indigo hover:underline">
                                    <?php echo htmlspecialchars($msg['email']); ?>
                                </a>
                            </td>
                            <td class="py-4 px-6 text-sm text-dusty-grape max-w-xs truncate" title="<?php echo htmlspecialchars($msg['subject']); ?>">
                                <?php echo htmlspecialchars($msg['subject']); ?>
                            </td>
                            <td class="py-4 px-6 text-center">
                                <a href="view_message.php?id=<?php echo $msg['id']; ?>" class="text-space-indigo hover:text-dusty-grape font-medium px-2 py-1 bg-almond-silk bg-opacity-30 rounded mr-2 transition-colors">Read</a>
                                <a href="?delete=<?php echo $msg['id']; ?>" class="text-red-600 hover:text-red-800 font-medium px-2 py-1 bg-red-50 rounded transition-colors" onclick="return confirm('Are you sure you want to delete this message?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="p-8 text-center text-dusty-grape">
            <svg class="w-12 h-12 mx-auto mb-4 text-lilac-ash" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
            <p class="text-lg font-medium">No messages found.</p>
            <p class="text-sm mt-1">When users submit the contact form, their messages will appear here.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
