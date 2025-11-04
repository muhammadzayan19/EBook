<?php
session_start();
$page_title = "Privacy Policy";
require_once '../config/db.php';
include '../includes/header.php';
?>

<!-- Privacy Policy Page -->
<section class="terms-hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="terms-hero-badge">
                    <i class="bi bi-shield-lock"></i>
                    Legal Document
                </div>
                <h1 class="terms-hero-title">Privacy <span class="gradient-text">Policy</span></h1>
                <p class="terms-hero-subtitle">Last Updated: October 30, 2025</p>
            </div>
        </div>
    </div>
</section>

<!-- Privacy Content Section -->
<section class="terms-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="terms-sidebar sticky-sidebar">
                    <h5 class="sidebar-title">Quick Navigation</h5>
                    <nav class="terms-nav" id="termsNav">
                        <a href="#introduction" class="terms-nav-item active">
                            <i class="bi bi-file-text"></i> Introduction
                        </a>
                        <a href="#information-collect" class="terms-nav-item">
                            <i class="bi bi-database"></i> Information We Collect
                        </a>
                        <a href="#how-we-use" class="terms-nav-item">
                            <i class="bi bi-gear"></i> How We Use Information
                        </a>
                        <a href="#information-sharing" class="terms-nav-item">
                            <i class="bi bi-lock"></i> Information Sharing
                        </a>
                        <a href="#data-security" class="terms-nav-item">
                            <i class="bi bi-shield-lock"></i> Data Security
                        </a>
                        <a href="#cookies" class="terms-nav-item">
                            <i class="bi bi-cookie"></i> Cookies & Tracking
                        </a>
                        <a href="#your-rights" class="terms-nav-item">
                            <i class="bi bi-person-check"></i> Your Rights
                        </a>
                        <a href="#third-party" class="terms-nav-item">
                            <i class="bi bi-link-45deg"></i> Third-Party Services
                        </a>
                        <a href="#children" class="terms-nav-item">
                            <i class="bi bi-people"></i> Children's Privacy
                        </a>
                        <a href="#changes" class="terms-nav-item">
                            <i class="bi bi-arrow-repeat"></i> Policy Changes
                        </a>
                        <a href="#contact" class="terms-nav-item">
                            <i class="bi bi-envelope"></i> Contact Us
                        </a>
                    </nav>

                    <div class="sidebar-cta mt-4">
                        <h6>Need Help?</h6>
                        <p>Get in touch with us</p>
                        <a href="/contact" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-chat-dots me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="terms-content">
                    <!-- Introduction -->
                    <div id="introduction" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-file-text"></i>
                            Introduction
                        </h2>
                        <p>
                            Welcome to <strong>Online Book Store</strong> ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.
                        </p>
                        <p>
                            Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site or use our services.
                        </p>
                        
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="bi bi-shield-check me-3 fs-4"></i>
                                <div>
                                    <strong>Your Privacy Matters:</strong> We take your privacy seriously and are transparent about our data practices. This policy outlines what information we collect, why we collect it, and how we protect it.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Information We Collect -->
                    <div id="information-collect" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-database"></i>
                            Information We Collect
                        </h2>
                        
                        <h4 class="terms-subheading">Personal Information You Provide</h4>
                        <p>We collect personal information that you voluntarily provide to us when you:</p>
                        <ul class="terms-list">
                            <li>Register an account on our platform</li>
                            <li>Make a purchase or subscribe to services</li>
                            <li>Subscribe to our newsletter</li>
                            <li>Participate in writing competitions</li>
                            <li>Contact us via email or support forms</li>
                        </ul>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-person-badge text-primary"></i> Types of Personal Information
                            </h5>
                            <ul>
                                <li><strong>Contact Information:</strong> Name, email address, phone number, mailing address</li>
                                <li><strong>Account Information:</strong> Username, password (encrypted), profile details</li>
                                <li><strong>Payment Information:</strong> Billing address, payment method details</li>
                                <li><strong>Competition Entries:</strong> Submitted essays, stories, and creative content</li>
                                <li><strong>Communications:</strong> Messages, feedback, support requests</li>
                            </ul>
                        </div>

                        <h4 class="terms-subheading">Information Automatically Collected</h4>
                        <p>When you visit our website, we automatically collect certain information:</p>
                        <ul class="terms-list">
                            <li><strong>Device Information:</strong> IP address, browser type, operating system</li>
                            <li><strong>Usage Information:</strong> Pages viewed, time spent, links clicked</li>
                            <li><strong>Location Information:</strong> General location based on IP address</li>
                            <li><strong>Cookie Information:</strong> Data collected through cookies and similar technologies</li>
                        </ul>
                    </div>

                    <!-- How We Use Information -->
                    <div id="how-we-use" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-gear"></i>
                            How We Use Your Information
                        </h2>
                        <p>We use the information we collect for various purposes:</p>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <div class="terms-feature-card">
                                    <i class="bi bi-box-seam"></i>
                                    <h6>Service Delivery</h6>
                                    <p>To provide books, process orders, manage subscriptions, and deliver requested content.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="terms-feature-card">
                                    <i class="bi bi-envelope"></i>
                                    <h6>Communication</h6>
                                    <p>To send updates, newsletters, competition announcements, and important service notifications.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="terms-feature-card">
                                    <i class="bi bi-shield-check"></i>
                                    <h6>Security & Protection</h6>
                                    <p>To detect and prevent fraud, unauthorized access, and technical issues.</p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="terms-feature-card">
                                    <i class="bi bi-graph-up"></i>
                                    <h6>Analytics & Improvement</h6>
                                    <p>To analyze usage patterns and enhance user experience.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="terms-subheading">Competition Management</h4>
                        <p>For writing competitions, we use your information to:</p>
                        <ul class="terms-list">
                            <li>Process and review competition entries</li>
                            <li>Announce winners and distribute prizes</li>
                            <li>Showcase winning entries (with permission)</li>
                            <li>Send competition updates and deadlines</li>
                        </ul>
                    </div>

                    <!-- Information Sharing -->
                    <div id="information-sharing" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-lock"></i>
                            Information Sharing and Disclosure
                        </h2>
                        <p>We may share your information in the following circumstances:</p>

                        <h4 class="terms-subheading">With Service Providers</h4>
                        <div class="terms-card">
                            <p>We share information with third-party service providers who perform services on our behalf:</p>
                            <ul>
                                <li>Payment processors (e.g., PayPal, Stripe)</li>
                                <li>Email service providers</li>
                                <li>Web hosting and cloud storage</li>
                                <li>Analytics services</li>
                            </ul>
                            <p class="mb-0"><strong>Important:</strong> These providers are contractually obligated to protect your information.</p>
                        </div>

                        <h4 class="terms-subheading">Legal Requirements</h4>
                        <p>We may disclose your information if required by law or in response to:</p>
                        <ul class="terms-list">
                            <li>Valid legal requests from authorities</li>
                            <li>Court orders or legal processes</li>
                            <li>Protection of our legal rights</li>
                            <li>Investigation of fraud or security issues</li>
                        </ul>

                        <div class="alert alert-success">
                            <div class="d-flex">
                                <i class="bi bi-shield-check me-3 fs-4"></i>
                                <div>
                                    <strong>We Never Sell Your Data:</strong> We do not sell, rent, or trade your personal information to third parties for marketing purposes.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Data Security -->
                    <div id="data-security" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-shield-lock"></i>
                            Data Security
                        </h2>
                        <p>
                            We implement appropriate technical and organizational security measures to protect your personal information.
                        </p>

                        <h4 class="terms-subheading">Security Measures Include:</h4>
                        <ul class="terms-list">
                            <li><strong>Encryption:</strong> SSL/TLS encryption for data transmission</li>
                            <li><strong>Password Protection:</strong> Passwords are hashed using industry-standard algorithms</li>
                            <li><strong>Access Controls:</strong> Limited employee access to personal information</li>
                            <li><strong>Regular Audits:</strong> Periodic security assessments and updates</li>
                            <li><strong>Secure Storage:</strong> Protected servers and secure databases</li>
                        </ul>

                        <div class="alert alert-warning">
                            <div class="d-flex">
                                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Important Notice:</strong> While we strive to protect your information, no method of internet transmission is 100% secure. We cannot guarantee absolute security.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cookies -->
                    <div id="cookies" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-cookie"></i>
                            Cookies and Tracking Technologies
                        </h2>
                        <p>
                            We use cookies and similar tracking technologies to collect information about your browsing activities.
                        </p>

                        <h4 class="terms-subheading">Types of Cookies We Use:</h4>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="terms-feature-card text-center">
                                    <i class="bi bi-check-circle"></i>
                                    <h6>Essential Cookies</h6>
                                    <p>Required for website functionality and security. Cannot be disabled.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="terms-feature-card text-center">
                                    <i class="bi bi-speedometer2"></i>
                                    <h6>Performance Cookies</h6>
                                    <p>Help us understand visitor interactions through anonymous analytics data.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="terms-feature-card text-center">
                                    <i class="bi bi-sliders"></i>
                                    <h6>Functional Cookies</h6>
                                    <p>Remember your preferences and settings to enhance experience.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="terms-subheading">Managing Cookies</h4>
                        <p>You can control cookies through your browser settings to accept, reject, or delete cookies.</p>
                        <p><strong>Note:</strong> Disabling cookies may affect website functionality.</p>
                    </div>

                    <!-- Your Rights -->
                    <div id="your-rights" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-person-check"></i>
                            Your Privacy Rights
                        </h2>
                        <p>You have certain rights regarding your personal information:</p>

                        <h4 class="terms-subheading">General Rights</h4>
                        <ul class="terms-list">
                            <li><strong>Access:</strong> Request a copy of personal information we hold</li>
                            <li><strong>Correction:</strong> Request correction of inaccurate information</li>
                            <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                            <li><strong>Portability:</strong> Request transfer of your data</li>
                            <li><strong>Opt-Out:</strong> Unsubscribe from marketing communications</li>
                            <li><strong>Object:</strong> Object to certain processing purposes</li>
                        </ul>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-hand-index text-primary"></i> How to Exercise Your Rights
                            </h5>
                            <p>To exercise any of these rights, please contact us at:</p>
                            <p><strong>Email:</strong> privacy@onlinebookstore.com</p>
                            <p class="mb-0">We will respond to your request within 30 days.</p>
                        </div>
                    </div>

                    <!-- Third-Party Services -->
                    <div id="third-party" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-link-45deg"></i>
                            Third-Party Services and Links
                        </h2>
                        <p>
                            Our website may contain links to third-party websites or services. We are not responsible for their privacy practices.
                        </p>

                        <h4 class="terms-subheading">Third-Party Services We Use:</h4>
                        <ul class="terms-list">
                            <li><strong>Analytics:</strong> Website analytics and tracking</li>
                            <li><strong>Payment Processors:</strong> Secure payment processing</li>
                            <li><strong>Email Services:</strong> Email marketing and communications</li>
                            <li><strong>Social Media:</strong> Social sharing and engagement</li>
                        </ul>

                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Recommendation:</strong> Review the privacy policies of any third-party services you access through our website.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Children's Privacy -->
                    <div id="children" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-people"></i>
                            Children's Privacy
                        </h2>
                        <p>
                            Our services are not intended for individuals under the age of 13. We do not knowingly collect personal information from children under 13.
                        </p>
                        <p>
                            If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.
                        </p>
                        
                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-shield-shaded text-primary"></i> Parental Responsibilities
                            </h5>
                            <p>Parents and guardians should:</p>
                            <ul class="mb-0">
                                <li>Monitor their children's online activities</li>
                                <li>Use parental control tools when appropriate</li>
                                <li>Report any concerns about children's data</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Policy Changes -->
                    <div id="changes" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-arrow-repeat"></i>
                            Changes to This Privacy Policy
                        </h2>
                        <p>
                            We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements.
                        </p>
                        <p>When we make changes, we will:</p>
                        <ul class="terms-list">
                            <li>Update the "Last Updated" date at the top</li>
                            <li>Post the revised policy on our website</li>
                            <li>Notify you via email for material changes</li>
                            <li>Provide a reasonable notice period before changes take effect</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="bi bi-bell-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Stay Informed:</strong> We encourage you to review this Privacy Policy periodically to stay informed about how we protect your information.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div id="contact" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-envelope"></i>
                            Contact Us
                        </h2>
                        <p>
                            If you have any questions or concerns regarding this Privacy Policy, please contact us:
                        </p>

                        <div class="terms-card">
                            <h5 class="card-title">Online Book Store</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong><i class="bi bi-envelope me-2"></i>Email:</strong></p>
                                    <p>privacy@onlinebookstore.com</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong><i class="bi bi-telephone me-2"></i>Phone:</strong></p>
                                    <p>+1 (234) 567-890</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong><i class="bi bi-geo-alt me-2"></i>Address:</strong></p>
                                    <p>123 Book Street, Reading City, RC 12345</p>
                                </div>
                                <div class="col-md-6">
                                    <p class="mb-2"><strong><i class="bi bi-clock me-2"></i>Response Time:</strong></p>
                                    <p>Within 30 days for privacy inquiries</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success">
                            <div class="d-flex">
                                <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Acknowledgment:</strong> By using our website and services, you acknowledge that you have read, understood, and agree to be bound by this Privacy Policy.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Smooth scroll and active navigation
document.addEventListener('DOMContentLoaded', function() {
    const navLinks = document.querySelectorAll('.terms-nav-item');
    const sections = document.querySelectorAll('.terms-block');
    
    // Smooth scroll on nav click
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetSection = document.querySelector(targetId);
            
            if (targetSection) {
                const offset = 100;
                const targetPosition = targetSection.offsetTop - offset;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
    
    // Update active nav on scroll
    window.addEventListener('scroll', function() {
        let current = '';
        
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            const sectionHeight = section.clientHeight;
            
            if (pageYOffset >= (sectionTop - 150)) {
                current = section.getAttribute('id');
            }
        });
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('active');
            }
        });
    });
    
    // Sticky sidebar
    const sidebar = document.querySelector('.sticky-sidebar');
    if (sidebar && window.innerWidth >= 992) {
        const sidebarTop = sidebar.offsetTop;
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset >= sidebarTop - 100) {
                sidebar.style.position = 'sticky';
                sidebar.style.top = '100px';
            }
        });
    }

    // Handle hash navigation on page load
    if (window.location.hash) {
        const targetId = window.location.hash;
        const targetSection = document.querySelector(targetId);
        if (targetSection) {
            setTimeout(() => {
                const offset = 100;
                const targetPosition = targetSection.offsetTop - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }, 100);
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>