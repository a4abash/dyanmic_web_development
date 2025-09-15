<?php 
require 'config/auth.php';

include 'includes/header.php'; ?>
<title>Contact Us - Ripper Tech & Solutions</title>
<section class="contact-section">
    <h1 style="text-align:center;">Contact Us</h1>

    <form id="contactForm" aria-label="Contact Form">

        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required minlength="3" placeholder="Your Full Name"
            aria-required="true">

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required placeholder="you@example.com">

        <label for="message">Message:</label>
        <textarea id="message" name="message" rows="5" required placeholder="Your Message"></textarea>

        <div class="form-group">
            <input type="checkbox" id="consent" name="consent" required>
            <label for="consent">
                I consent to the processing of my data in accordance with the
                <a href="privacy.php" target="_blank">Privacy Policy</a>.
            </label>
        </div>


        <button type="submit">Send Message</button>
        <p class="form-message" id="formMessage" role="status" aria-live="polite"></p>
    </form>
</section>
<?php include 'includes/footer.php'; ?>