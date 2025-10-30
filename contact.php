<?php
$page_title = "Contact Us";

require_once 'config/db.php';
include 'includes/header.php';
?>

    <!-- Page Header -->
    <section class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <h1 class="display-4 fw-bold text-white mb-3">Get In Touch</h1>
                    <p class="lead text-white opacity-90">We'd love to hear from you. Send us a message and we'll respond as soon as possible.</p>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb justify-content-center">
                            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Contact</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Information Section -->
    <section class="contact-info-section py-5 bg-white">
        <div class="container">
            <div class="row text-center">
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="bi bi-geo-alt"></i>
                        </div>
                        <h4 class="contact-info-title">Visit Us</h4>
                        <p class="contact-info-text">
                            Karachi, Sindh<br>
                            Pakistan
                        </p>
                        <a href="https://maps.google.com" target="_blank" class="contact-info-link">
                            Get Directions <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <h4 class="contact-info-title">Email Us</h4>
                        <p class="contact-info-text">
                            info@ebooksystem.com<br>
                            support@ebooksystem.com
                        </p>
                        <a href="mailto:info@ebooksystem.com" class="contact-info-link">
                            Send Email <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <div class="contact-info-card">
                        <div class="contact-info-icon">
                            <i class="bi bi-telephone"></i>
                        </div>
                        <h4 class="contact-info-title">Call Us</h4>
                        <p class="contact-info-text">
                            +92 300 1234567<br>
                            +92 321 7654321
                        </p>
                        <a href="tel:+923001234567" class="contact-info-link">
                            Call Now <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Contact Form Section -->
    <section class="contact-form-section py-5">
        <div class="container">
            <div class="row">
                <!-- Form Column -->
                <div class="col-lg-7 mb-4 mb-lg-0">
                    <div class="contact-form-wrapper">
                        <div class="contact-form-header">
                            <span class="section-label">Send Message</span>
                            <h2 class="section-title mb-3">Have Questions? Contact Us</h2>
                            <p class="text-muted">Fill out the form below and our team will get back to you within 24 hours.</p>
                        </div>

                        <form id="contactForm" class="contact-form">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="name" name="name" 
                                               placeholder="Your Name" required>
                                        <label for="name"><i class="bi bi-person me-2"></i>Your Name *</label>
                                        <div class="invalid-feedback">
                                            Please enter your name.
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="email" class="form-control" id="email" name="email" 
                                               placeholder="Your Email" required>
                                        <label for="email"><i class="bi bi-envelope me-2"></i>Your Email *</label>
                                        <div class="invalid-feedback">
                                            Please enter a valid email address.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               placeholder="Phone Number">
                                        <label for="phone"><i class="bi bi-telephone me-2"></i>Phone Number</label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="form-floating">
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="" selected disabled>Subject</option>
                                            <option value="general">General Inquiry</option>
                                            <option value="technical">Technical Support</option>
                                            <option value="billing">Billing & Payment</option>
                                            <option value="competition">Competition Query</option>
                                            <option value="partnership">Partnership Opportunity</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            Please select a subject.
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="form-floating">
                                    <textarea class="form-control" id="message" name="message" 
                                              placeholder="Your Message" style="height: 180px" required></textarea>
                                    <label for="message"><i class="bi bi-chat-dots me-2"></i>Your Message *</label>
                                    <div class="invalid-feedback">
                                        Please enter your message (minimum 10 characters).
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-lg w-100" id="submitBtn">
                                <i class="bi bi-send me-2"></i>Send Message
                            </button>
                        </form>

                        <script>
                        document.getElementById('contactForm').addEventListener('submit', function(e) {
                            e.preventDefault();
                            
                            const form = this;
                            const submitBtn = document.getElementById('submitBtn');
                            
                            // Get form values
                            const name = document.getElementById('name').value.trim();
                            const email = document.getElementById('email').value.trim();
                            const subject = document.getElementById('subject').value.trim();
                            const message = document.getElementById('message').value.trim();
                            
                            // Client-side validation
                            if (!name || !email || !subject || !message) {
                                alert('Please fill in all required fields.');
                                return;
                            }
                            
                            if (message.length < 10) {
                                alert('Message must be at least 10 characters long.');
                                return;
                            }
                            
                            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                            if (!emailRegex.test(email)) {
                                alert('Please enter a valid email address.');
                                return;
                            }
                            
                            const formData = new FormData(form);
                            
                            // Disable submit button
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Sending...';
                            
                            // Send AJAX request
                            fetch('includes/handle_contact.php', {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.json())
                            .then(data => {
                                // Re-enable submit button
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Send Message';
                                
                                if (data.success) {
                                    // Show success message
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-success alert-dismissible fade show';
                                    alertDiv.innerHTML = `
                                        <i class="bi bi-check-circle me-2"></i>${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    `;
                                    form.parentElement.insertBefore(alertDiv, form);
                                    
                                    // Clear form
                                    form.reset();
                                    
                                    // Remove alert after 5 seconds
                                    setTimeout(() => alertDiv.remove(), 5000);
                                } else {
                                    // Show error message
                                    const alertDiv = document.createElement('div');
                                    alertDiv.className = 'alert alert-danger alert-dismissible fade show';
                                    alertDiv.innerHTML = `
                                        <i class="bi bi-exclamation-triangle me-2"></i>${data.message}
                                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                    `;
                                    form.parentElement.insertBefore(alertDiv, form);
                                }
                            })
                            .catch(error => {
                                submitBtn.disabled = false;
                                submitBtn.innerHTML = '<i class="bi bi-send me-2"></i>Send Message';
                                alert('An error occurred. Please try again.');
                                console.error('Error:', error);
                            });
                        });
                        </script>
                    </div>
                </div>

                <!-- Info Column -->
                <div class="col-lg-5">
                    <div class="contact-sidebar">
                        <!-- Quick Response -->
                        <div class="contact-feature-box">
                            <div class="contact-feature-icon">
                                <i class="bi bi-clock-history"></i>
                            </div>
                            <div class="contact-feature-content">
                                <h5>Quick Response Time</h5>
                                <p>We typically respond to all inquiries within 24 hours during business days.</p>
                            </div>
                        </div>

                        <!-- Business Hours -->
                        <div class="contact-feature-box">
                            <div class="contact-feature-icon">
                                <i class="bi bi-calendar-check"></i>
                            </div>
                            <div class="contact-feature-content">
                                <h5>Business Hours</h5>
                                <ul class="business-hours-list">
                                    <li>
                                        <span>Monday - Friday</span>
                                        <strong>9:00 AM - 6:00 PM</strong>
                                    </li>
                                    <li>
                                        <span>Saturday</span>
                                        <strong>10:00 AM - 4:00 PM</strong>
                                    </li>
                                    <li>
                                        <span>Sunday</span>
                                        <strong>Closed</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Support Channels -->
                        <div class="contact-feature-box">
                            <div class="contact-feature-icon">
                                <i class="bi bi-headset"></i>
                            </div>
                            <div class="contact-feature-content">
                                <h5>Multiple Support Channels</h5>
                                <p>Reach out through email, phone, or our contact form. We're here to help!</p>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="contact-social">
                            <h5 class="mb-3">Follow Us</h5>
                            <div class="social-links-contact">
                                <a href="#" class="social-link-contact" title="Facebook">
                                    <i class="bi bi-facebook"></i>
                                </a>
                                <a href="#" class="social-link-contact" title="Twitter">
                                    <i class="bi bi-twitter"></i>
                                </a>
                                <a href="#" class="social-link-contact" title="Instagram">
                                    <i class="bi bi-instagram"></i>
                                </a>
                                <a href="#" class="social-link-contact" title="LinkedIn">
                                    <i class="bi bi-linkedin"></i>
                                </a>
                                <a href="#" class="social-link-contact" title="YouTube">
                                    <i class="bi bi-youtube"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="contact-faq-section py-5 bg-white">
        <div class="container">
            <div class="text-center mb-5">
                <span class="section-label">FAQ</span><br>
                <h2 class="section-title mb-3">Frequently Asked Questions</h2>
                <p class="text-muted">Quick answers to common questions about our platform</p>
            </div>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion accordion-flush" id="faqAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq1">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                                    <i class="bi bi-question-circle me-2"></i>
                                    How do I purchase an e-book?
                                </button>
                            </h2>
                            <div id="collapse1" class="accordion-collapse collapse show" 
                                 aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Browse our books section, select your desired e-book, and click "Buy Now". You'll need to create an account and complete the payment process. Once purchased, you can download the book in your preferred format (PDF, EPUB, or MOBI).
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq2">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                    <i class="bi bi-question-circle me-2"></i>
                                    How do I participate in writing competitions?
                                </button>
                            </h2>
                            <div id="collapse2" class="accordion-collapse collapse" 
                                 aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Visit the Competitions page, select an active competition, and click "Join Competition". You'll have a specified time limit to write and submit your essay. Make sure you're logged in before starting.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq3">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                    <i class="bi bi-question-circle me-2"></i>
                                    What payment methods do you accept?
                                </button>
                            </h2>
                            <div id="collapse3" class="accordion-collapse collapse" 
                                 aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    We accept all major credit cards, debit cards, and online payment methods including PayPal. All transactions are secure and encrypted.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq4">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                    <i class="bi bi-question-circle me-2"></i>
                                    Can I get a refund for purchased e-books?
                                </button>
                            </h2>
                            <div id="collapse4" class="accordion-collapse collapse" 
                                 aria-labelledby="faq4" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Due to the digital nature of our products, we offer refunds within 7 days of purchase if the book hasn't been downloaded. Please contact our support team for refund requests.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="faq5">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                                        data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                    <i class="bi bi-question-circle me-2"></i>
                                    How are competition winners selected?
                                </button>
                            </h2>
                            <div id="collapse5" class="accordion-collapse collapse" 
                                 aria-labelledby="faq5" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    All submissions are reviewed by our panel of judges based on creativity, originality, grammar, and adherence to the competition theme. Winners are announced within 2 weeks after the competition ends.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="contact-map-section">
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d924237.7100683448!2d66.49604853408654!3d25.193202404385103!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3eb33e06651d4bbf%3A0x9cf92f44555a0c23!2sKarachi%2C%20Karachi%20City%2C%20Sindh%2C%20Pakistan!5e0!3m2!1sen!2s!4v1635000000000!5m2!1sen!2s" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy"
                title="Our Location">
            </iframe>
        </div>
    </section>

<?php
include 'includes/footer.php';
?>