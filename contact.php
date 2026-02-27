<?php
require_once __DIR__ . '/includes/header.php';

$message_status = '';
$status_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'No Subject');
    $message = trim($_POST['message'] ?? '');
    
    if (!empty($name) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
        $stmt = $pdo->prepare("INSERT INTO messages (name, email, subject, message) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$name, $email, $subject, $message])) {
            $message_status = "Thank you! Your message has been sent successfully.";
            $status_type = "success";
        } else {
            $message_status = "Sorry, something went wrong. Please try again later.";
            $status_type = "error";
        }
    } else {
        $message_status = "Please fill in all required fields correctly.";
        $status_type = "error";
    }
}
?>

<div class="bg-space-indigo text-parchment py-16 px-4">
    <div class="container mx-auto max-w-4xl text-center fade-in">
        <h1 class="text-4xl md:text-5xl font-bold mb-4 tracking-tight">Contact Us</h1>
        <p class="text-xl text-lilac-ash max-w-2xl mx-auto font-light">
            We'd love to hear from you.
        </p>
    </div>
</div>

<section class="py-20 px-4 flex-grow">
    <div class="container mx-auto max-w-4xl fade-in">
        
        <?php if ($message_status): ?>
            <div class="mb-8 p-4 rounded-lg <?php echo $status_type === 'success' ? 'bg-green-100 text-green-700 border border-green-400' : 'bg-red-100 text-red-700 border border-red-400'; ?>">
                <?php echo htmlspecialchars($message_status); ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row gap-12 bg-parchment rounded-2xl p-8 md:p-12 shadow-md border border-almond-silk">
            
            <div class="md:w-1/2">
                <h2 class="text-2xl font-bold text-space-indigo mb-6">Get in Touch</h2>
                <p class="text-dusty-grape mb-8">
                    Have questions, feedback, or a news tip? Fill out the form, and our team will get back to you as soon as possible.
                </p>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-4">
                        <div class="bg-almond-silk p-3 rounded-full text-space-indigo">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-space-indigo">Email</h3>
                            <p class="text-dusty-grape text-sm">contact@60secondsnews.com</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4">
                        <div class="bg-almond-silk p-3 rounded-full text-space-indigo">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-space-indigo">Office</h3>
                            <p class="text-dusty-grape text-sm">123 News Avenue, Media City</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="md:w-1/2">
                <form action="contact.php" method="POST" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-space-indigo mb-2">Your Name *</label>
                        <input type="text" id="name" name="name" class="w-full bg-white border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" placeholder="John Doe" required>
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-space-indigo mb-2">Your Email *</label>
                        <input type="email" id="email" name="email" class="w-full bg-white border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" placeholder="john@example.com" required>
                    </div>
                    <div>
                        <label for="subject" class="block text-sm font-medium text-space-indigo mb-2">Subject</label>
                        <input type="text" id="subject" name="subject" class="w-full bg-white border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" placeholder="General Inquiry">
                    </div>
                    <div>
                        <label for="message" class="block text-sm font-medium text-space-indigo mb-2">Message *</label>
                        <textarea id="message" name="message" rows="5" class="w-full bg-white border border-almond-silk rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-space-indigo transition-colors" placeholder="How can we help you?" required></textarea>
                    </div>
                    <button type="submit" class="w-full bg-space-indigo text-parchment hover:bg-dusty-grape font-bold py-3 px-4 rounded-lg transition-colors">
                        Send Message
                    </button>
                </form>
            </div>
            
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
