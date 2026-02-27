<?php
require_once __DIR__ . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id === 0) {
    header("Location: messages.php");
    exit;
}

// Fetch Message
$stmt = $pdo->prepare("SELECT * FROM messages WHERE id = ?");
$stmt->execute([$id]);
$msg = $stmt->fetch();

if (!$msg) {
    echo "<div class='p-8 text-center text-red-600 font-bold'>Message not found.</div>";
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Mark as read if not already read
if ($msg['is_read'] == 0) {
    $updateStmt = $pdo->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
    $updateStmt->execute([$id]);
}
?>

<div class="flex items-center mb-8">
    <a href="messages.php" class="text-dusty-grape hover:text-space-indigo mr-4 transition-colors">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
    </a>
    <h2 class="text-3xl font-bold text-space-indigo">View Message</h2>
</div>

<div class="bg-white rounded-xl shadow-sm border border-almond-silk p-8 max-w-4xl">
    
    <div class="flex flex-col md:flex-row md:items-center justify-between border-b border-almond-silk pb-6 mb-6">
        <div>
            <h3 class="text-2xl font-bold text-space-indigo"><?php echo htmlspecialchars($msg['subject']); ?></h3>
            <p class="text-dusty-grape mt-1">From: <span class="font-medium text-space-indigo"><?php echo htmlspecialchars($msg['name']); ?></span> &lt;<a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="hover:underline text-space-indigo"><?php echo htmlspecialchars($msg['email']); ?></a>&gt;</p>
        </div>
        <div class="mt-4 md:mt-0 text-sm text-lilac-ash whitespace-nowrap">
            Received: <?php echo date('M d, Y \a\t H:i A', strtotime($msg['created_at'])); ?>
        </div>
    </div>
    
    <div class="prose max-w-none text-space-indigo font-sans whitespace-pre-wrap leading-relaxed">
        <?php echo htmlspecialchars($msg['message']); ?>
    </div>
    
    <div class="mt-10 pt-6 border-t border-almond-silk flex space-x-4">
        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>?subject=Re: <?php echo rawurlencode($msg['subject']); ?>" class="bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-2.5 px-6 rounded-lg transition-colors inline-block text-center">
            Reply via Email
        </a>
        <a href="messages.php?delete=<?php echo $msg['id']; ?>" class="bg-red-50 text-red-600 hover:bg-red-100 border border-red-200 font-bold py-2.5 px-6 rounded-lg transition-colors inline-block text-center" onclick="return confirm('Are you sure you want to delete this message?');">
            Delete Message
        </a>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
