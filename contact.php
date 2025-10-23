<?php
require_once 'php/config.php';

$message_sent = false;
$error_message = '';

if ($_POST) {
    try {
        $pdo = getPDO();
        
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $subject = $_POST['subject'] ?? '';
        $message = $_POST['message'] ?? '';
        
        if (empty($name) || empty($email) || empty($message)) {
            throw new Exception('Please fill in all required fields.');
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO contact_messages (name, email, phone, subject, message) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        
        $message_sent = true;
        
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

// Get contact information from settings
try {
    $pdo = getPDO();
    $stmt = $pdo->query("SELECT * FROM settings WHERE setting_key IN ('contact_email', 'contact_phone')");
    $settings = $stmt->fetchAll();
    
    $contact_email = 'info@mazvikadei.com';
    $contact_phone = '+263 77 123 4567';
    
    foreach ($settings as $setting) {
        if ($setting['setting_key'] === 'contact_email') {
            $contact_email = $setting['setting_value'];
        } elseif ($setting['setting_key'] === 'contact_phone') {
            $contact_phone = $setting['setting_value'];
        }
    }
} catch (Exception $e) {
    $contact_email = 'info@mazvikadei.com';
    $contact_phone = '+263 77 123 4567';
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Contact Us - Mazvikadei Resort</title>
    <link rel="stylesheet" href="styles.css" />
    <style>
        .page-header {
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
            padding: 3rem 0;
            text-align: center;
        }
        .page-header h1 {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 1rem;
        }
        .page-header p {
            font-size: 1.125rem;
            opacity: 0.9;
        }
        .contact-container {
            max-width: 1000px;
            margin: 0 auto;
        }
        .contact-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin: 3rem 0;
        }
        .contact-info {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .contact-info h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        .contact-item {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f9fafb;
            border-radius: 8px;
        }
        .contact-icon {
            font-size: 1.5rem;
            margin-right: 1rem;
            color: #3b82f6;
        }
        .contact-details h4 {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.25rem;
        }
        .contact-details p {
            color: #6b7280;
            margin: 0;
        }
        .contact-form {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .contact-form h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #1f2937;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        .form-group {
            display: flex;
            flex-direction: column;
        }
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        .form-group label {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #374151;
        }
        .form-group input,
        .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
        }
        .btn-submit {
            background: linear-gradient(90deg, #3b82f6, #1d4ed8);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 100%;
            font-size: 1rem;
        }
        .btn-submit:hover {
            background: linear-gradient(90deg, #2563eb, #1e40af);
            transform: translateY(-1px);
        }
        .success-message {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .error-message {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }
        .map-section {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }
        .map-placeholder {
            background: #f3f4f6;
            border: 2px dashed #d1d5db;
            border-radius: 8px;
            padding: 3rem;
            text-align: center;
            color: #6b7280;
        }
        .faq-section {
            background: #f8fafc;
            padding: 3rem 0;
        }
        .faq-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        .faq-item {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .faq-question {
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }
        .faq-answer {
            color: #6b7280;
            line-height: 1.6;
        }
        @media (max-width: 768px) {
            .contact-grid {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="container header-inner">
            <div class="brand">
                <img src="assets/logo.jpg" style="height:42px;margin-right:.6rem;vertical-align:middle">
                Mazvikadei Resort
            </div>
            <nav class="nav">
                <a href="index.php">Home</a>
                <a href="rooms.php">Rooms</a>
                <a href="activities.php">Activities</a>
                <a href="events.php">Events</a>
                <a href="bookings.php">Bookings</a>
                <a href="about.php">About</a>
                <a href="contact.php" class="active">Contact</a>
                <a href="admin/login.php">Admin</a>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with us for bookings, inquiries, or any assistance you need</p>
        </div>
    </div>

    <main class="container">
        <div class="contact-container">
            <div class="contact-grid">
                <!-- Contact Information -->
                <div class="contact-info">
                    <h3>Get in Touch</h3>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📧</div>
                        <div class="contact-details">
                            <h4>Email</h4>
                            <p><?php echo htmlspecialchars($contact_email); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📞</div>
                        <div class="contact-details">
                            <h4>Phone</h4>
                            <p><?php echo htmlspecialchars($contact_phone); ?></p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">📍</div>
                        <div class="contact-details">
                            <h4>Address</h4>
                            <p>Mazvikadei Dam, Banket, Zimbabwe</p>
                        </div>
                    </div>
                    
                    <div class="contact-item">
                        <div class="contact-icon">🕒</div>
                        <div class="contact-details">
                            <h4>Business Hours</h4>
                            <p>Monday - Sunday: 8:00 AM - 8:00 PM</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Form -->
                <div class="contact-form">
                    <h3>Send us a Message</h3>
                    
                    <?php if ($message_sent): ?>
                    <div class="success-message">
                        Thank you for your message! We'll get back to you as soon as possible.
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($error_message): ?>
                    <div class="error-message">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="contact.php">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Full Name *</label>
                                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="email">Email Address *</label>
                                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group full-width">
                            <label for="message">Message *</label>
                            <textarea id="message" name="message" required placeholder="Tell us how we can help you..."><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" class="btn-submit">Send Message</button>
                    </form>
                </div>
            </div>

            <!-- Map Section -->
            <div class="map-section">
                <h3>Find Us</h3>
                <div class="map-placeholder">
                    <h4>📍 Location Map</h4>
                    <p>Mazvikadei Resort is located near Banket, Zimbabwe, by the beautiful Mazvikadei Dam.</p>
                    <p>We're easily accessible by road from Harare and other major cities.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <div style="text-align: center; margin-bottom: 2rem;">
                <h2>Frequently Asked Questions</h2>
                <p class="muted">Find answers to common questions about our resort</p>
            </div>
            <div class="faq-grid">
                <div class="faq-item">
                    <div class="faq-question">What are your check-in and check-out times?</div>
                    <div class="faq-answer">Check-in is from 2:00 PM and check-out is before 10:00 AM. Early check-in and late check-out may be available upon request.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">What payment methods do you accept?</div>
                    <div class="faq-answer">We accept EcoCash, Paynow, PayPal, and bank transfers. Payment instructions will be provided after booking.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Do you offer group discounts?</div>
                    <div class="faq-answer">Yes, we offer special rates for group bookings of 10 or more people. Contact us for more information.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">What activities are included in the room rate?</div>
                    <div class="faq-answer">Swimming pool and beach access are included. Other activities like boat cruising and fishing have separate charges.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Is there Wi-Fi available?</div>
                    <div class="faq-answer">Yes, complimentary Wi-Fi is available in all rooms and common areas.</div>
                </div>
                <div class="faq-item">
                    <div class="faq-question">Can I cancel my booking?</div>
                    <div class="faq-answer">Yes, you can cancel up to 48 hours before check-in for a full refund. Cancellation policies may vary for special events.</div>
                </div>
            </div>
        </div>
    </section>

    <footer class="site-footer container">© <span id="year"></span> Mazvikadei Resort</footer>
    
    <script>
        // Set current year
        document.getElementById('year').textContent = new Date().getFullYear();
    </script>
</body>
</html>
