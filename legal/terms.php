<?php
session_start();
$page_title = "Terms of Service";
require_once '../config/db.php';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/terms.css">

<!-- Terms of Service Page -->
<section class="terms-hero">
    <div class="container">
        <div class="row">
            <div class="col-lg-12 text-center">
                <div class="terms-hero-badge">
                    <i class="bi bi-shield-check"></i>
                    Legal Agreement
                </div>
                <h1 class="terms-hero-title">Terms of <span class="gradient-text">Service</span></h1>
                <p class="terms-hero-subtitle">Last Updated: October 30, 2025</p>
            </div>
        </div>
    </div>
</section>

<!-- Terms Content Section -->
<section class="terms-section py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar Navigation -->
            <div class="col-lg-3 mb-4">
                <div class="terms-sidebar sticky-sidebar">
                    <h5 class="sidebar-title">Quick Navigation</h5>
                    <nav class="terms-nav" id="termsNav">
                        <a href="#agreement" class="terms-nav-item active">
                            <i class="bi bi-file-text"></i> Agreement to Terms
                        </a>
                        <a href="#account" class="terms-nav-item">
                            <i class="bi bi-person-plus"></i> Account Registration
                        </a>
                        <a href="#user-content" class="terms-nav-item">
                            <i class="bi bi-file-earmark-richtext"></i> User Content
                        </a>
                        <a href="#purchases" class="terms-nav-item">
                            <i class="bi bi-cart-check"></i> Purchases & Payments
                        </a>
                        <a href="#competitions" class="terms-nav-item">
                            <i class="bi bi-trophy"></i> Writing Competitions
                        </a>
                        <a href="#intellectual" class="terms-nav-item">
                            <i class="bi bi-patch-check"></i> Intellectual Property
                        </a>
                        <a href="#disclaimers" class="terms-nav-item">
                            <i class="bi bi-exclamation-diamond"></i> Disclaimers
                        </a>
                        <a href="#conduct" class="terms-nav-item">
                            <i class="bi bi-hand-thumbs-up"></i> User Conduct
                        </a>
                        <a href="#disputes" class="terms-nav-item">
                            <i class="bi bi-balance-scale"></i> Dispute Resolution
                        </a>
                        <a href="#modifications" class="terms-nav-item">
                            <i class="bi bi-arrow-repeat"></i> Modifications
                        </a>
                        <a href="#termination" class="terms-nav-item">
                            <i class="bi bi-x-circle"></i> Termination
                        </a>
                        <a href="#contact" class="terms-nav-item">
                            <i class="bi bi-envelope"></i> Contact Us
                        </a>
                    </nav>

                    <div class="sidebar-cta mt-4">
                        <h6>Need Assistance?</h6>
                        <p>Our team is here to help</p>
                        <a href="/contact" class="btn btn-primary btn-sm w-100">
                            <i class="bi bi-chat-dots me-2"></i>Contact Support
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <div class="terms-content">
                    <!-- Agreement to Terms -->
                    <div id="agreement" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-file-text"></i>
                            Agreement to Terms
                        </h2>
                        <p>Welcome to <strong>Online Book Store</strong>. These Terms of Service ("Terms") govern your access to and use of our website, services, and applications (collectively, the "Services"). By accessing or using our Services, you agree to be bound by these Terms.</p>
                        <p>Please read these Terms carefully before using our Services. If you do not agree to these Terms, you may not access or use our Services.</p>
                        
                        <div class="alert alert-warning">
                            <div class="d-flex">
                                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Important Notice:</strong> These Terms constitute a legally binding agreement between you and Online Book Store. By creating an account or using our Services, you acknowledge that you have read, understood, and agree to be bound by these Terms.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Account Registration -->
                    <div id="account" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-person-plus"></i>
                            Account Registration and Eligibility
                        </h2>
                        
                        <h4 class="terms-subheading">Eligibility Requirements</h4>
                        <p>To use our Services, you must:</p>
                        <ul class="terms-list">
                            <li>Be at least 13 years of age</li>
                            <li>Have the legal capacity to enter into binding contracts</li>
                            <li>Not be prohibited from using our Services under applicable law</li>
                            <li>Provide accurate and complete registration information</li>
                        </ul>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-shield-check text-primary"></i> Account Security
                            </h5>
                            <p>You are responsible for:</p>
                            <ul>
                                <li>Maintaining the confidentiality of your account credentials</li>
                                <li>All activities that occur under your account</li>
                                <li>Notifying us immediately of any unauthorized access</li>
                                <li>Ensuring your account information remains accurate and current</li>
                            </ul>
                        </div>

                        <h4 class="terms-subheading">Account Termination</h4>
                        <p>We reserve the right to suspend or terminate your account if you:</p>
                        <ul class="terms-list">
                            <li>Violate these Terms or any applicable laws</li>
                            <li>Engage in fraudulent or abusive behavior</li>
                            <li>Provide false or misleading information</li>
                            <li>Infringe upon intellectual property rights</li>
                        </ul>
                    </div>

                    <!-- User Content -->
                    <div id="user-content" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-file-earmark-richtext"></i>
                            User-Generated Content
                        </h2>
                        
                        <h4 class="terms-subheading">Content You Submit</h4>
                        <p>When you submit content to our platform (including competition entries, reviews, and comments), you grant us:</p>
                        <ul class="terms-list">
                            <li>A worldwide, non-exclusive, royalty-free license to use, reproduce, and display your content</li>
                            <li>The right to modify or adapt your content for technical or presentation purposes</li>
                            <li>Permission to showcase winning competition entries on our platform</li>
                        </ul>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-copyright text-primary"></i> Content Ownership
                            </h5>
                            <p>You retain all ownership rights to your content. However, you represent and warrant that:</p>
                            <ul>
                                <li>You own or have the necessary rights to submit the content</li>
                                <li>Your content does not infringe on third-party rights</li>
                                <li>Your content complies with all applicable laws and regulations</li>
                                <li>Your content does not contain harmful, offensive, or illegal material</li>
                            </ul>
                        </div>

                        <h4 class="terms-subheading">Prohibited Content</h4>
                        <p>You may not submit content that:</p>
                        <ul class="terms-list">
                            <li>Infringes on intellectual property or proprietary rights</li>
                            <li>Contains hate speech, harassment, or discrimination</li>
                            <li>Promotes violence, illegal activities, or harm to others</li>
                            <li>Includes malware, viruses, or malicious code</li>
                            <li>Violates privacy rights or discloses personal information without consent</li>
                            <li>Is false, misleading, or fraudulent</li>
                        </ul>
                    </div>

                    <!-- Purchases -->
                    <div id="purchases" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-cart-check"></i>
                            Purchases and Payments
                        </h2>
                        
                        <h4 class="terms-subheading">Book Purchases and Subscriptions</h4>
                        <p>When purchasing books or subscribing to our services:</p>
                        <ul class="terms-list">
                            <li>All prices are displayed in USD unless otherwise stated</li>
                            <li>Prices are subject to change without notice</li>
                            <li>Payment must be made through our authorized payment processors</li>
                            <li>You are responsible for all applicable taxes and fees</li>
                        </ul>

                        <div class="row g-3 mb-4">
                            <div class="col-md-4">
                                <div class="terms-feature-card text-center">
                                    <i class="bi bi-credit-card"></i>
                                    <h6>Payment Methods</h6>
                                    <p>We accept major credit cards, debit cards, and approved third-party payment services.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="terms-feature-card text-center">
                                    <i class="bi bi-arrow-clockwise"></i>
                                    <h6>Refund Policy</h6>
                                    <p>Digital books may be refunded within 14 days if not accessed or downloaded.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="terms-feature-card text-center">
                                    <i class="bi bi-receipt"></i>
                                    <h6>Billing</h6>
                                    <p>Subscription charges are billed on a recurring basis until cancelled.</p>
                                </div>
                            </div>
                        </div>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-book text-primary"></i> E-Book License Terms
                            </h5>
                            <p>When you purchase an e-book, you receive a limited, non-exclusive, non-transferable license to:</p>
                            <ul>
                                <li>Access and read the content for personal, non-commercial use</li>
                                <li>Download the content to authorized devices</li>
                            </ul>
                            <p class="mb-2"><strong>You may not:</strong></p>
                            <ul>
                                <li>Resell, distribute, or share purchased content</li>
                                <li>Remove or modify copyright notices or DRM protection</li>
                                <li>Use content for commercial purposes without permission</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Competitions -->
                    <div id="competitions" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-trophy"></i>
                            Writing Competitions
                        </h2>
                        
                        <h4 class="terms-subheading">Competition Rules</h4>
                        <p>By participating in our writing competitions, you agree to:</p>
                        <ul class="terms-list">
                            <li>Submit only original work that you have created</li>
                            <li>Meet all specified deadlines and requirements</li>
                            <li>Allow us to display winning entries on our platform</li>
                            <li>Accept the judges' decisions as final</li>
                        </ul>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-award text-primary"></i> Prizes and Recognition
                            </h5>
                            <p>Competition winners will receive:</p>
                            <ul>
                                <li>Prizes as specified in the competition announcement</li>
                                <li>Recognition on our website and promotional materials</li>
                                <li>Publication opportunities (where applicable)</li>
                            </ul>
                            <p class="mb-0"><strong>Important:</strong> Prize fulfillment may take up to 30 days. Winners must respond within 14 days of notification.</p>
                        </div>

                        <h4 class="terms-subheading">Entry Requirements</h4>
                        <ul class="terms-list">
                            <li>All submissions must be your original work</li>
                            <li>Previously published work may be ineligible (check specific competition rules)</li>
                            <li>Entries must comply with word count and format specifications</li>
                            <li>Multiple entries may be allowed per person (varies by competition)</li>
                        </ul>
                    </div>

                    <!-- Intellectual Property -->
                    <div id="intellectual" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-patch-check"></i>
                            Intellectual Property Rights
                        </h2>
                        
                        <h4 class="terms-subheading">Our Content and Trademarks</h4>
                        <p>All content on our platform, including but not limited to:</p>
                        <ul class="terms-list">
                            <li>Text, graphics, logos, and images</li>
                            <li>Software, code, and functionality</li>
                            <li>Design elements and user interface</li>
                            <li>Trademarks and service marks</li>
                        </ul>
                        <p>These are owned by Online Book Store or our licensors and are protected by copyright, trademark, and other intellectual property laws.</p>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-slash-circle text-primary"></i> Prohibited Uses
                            </h5>
                            <p>You may not, without our express written permission:</p>
                            <ul>
                                <li>Copy, reproduce, or distribute our content</li>
                                <li>Modify, adapt, or create derivative works</li>
                                <li>Reverse engineer or extract source code</li>
                                <li>Use our trademarks or branding</li>
                                <li>Frame or mirror any part of our website</li>
                            </ul>
                        </div>

                        <h4 class="terms-subheading">DMCA Copyright Policy</h4>
                        <p>We respect intellectual property rights and expect our users to do the same. If you believe your copyrighted work has been infringed, please contact us with:</p>
                        <ul class="terms-list">
                            <li>Description of the copyrighted work</li>
                            <li>Location of the infringing material</li>
                            <li>Your contact information</li>
                            <li>A statement of good faith belief</li>
                            <li>Electronic or physical signature</li>
                        </ul>
                    </div>

                    <!-- Disclaimers -->
                    <div id="disclaimers" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-exclamation-diamond"></i>
                            Disclaimers and Limitations
                        </h2>
                        
                        <h4 class="terms-subheading">Service Availability</h4>
                        <div class="terms-card">
                            <p>Our Services are provided "as is" and "as available." We do not guarantee that:</p>
                            <ul>
                                <li>Services will be uninterrupted or error-free</li>
                                <li>All content will be accurate or reliable</li>
                                <li>Defects will be corrected immediately</li>
                                <li>Services will meet your specific requirements</li>
                            </ul>
                        </div>

                        <h4 class="terms-subheading">Limitation of Liability</h4>
                        <p>To the maximum extent permitted by law, Online Book Store shall not be liable for:</p>
                        <ul class="terms-list">
                            <li>Indirect, incidental, or consequential damages</li>
                            <li>Loss of profits, data, or business opportunities</li>
                            <li>Damages resulting from unauthorized access to your account</li>
                            <li>Third-party content or services</li>
                        </ul>

                        <div class="alert alert-danger">
                            <div class="d-flex">
                                <i class="bi bi-exclamation-octagon-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Important:</strong> Our total liability for any claim arising from your use of our Services is limited to the amount you paid us in the twelve (12) months preceding the claim.
                                </div>
                            </div>
                        </div>

                        <h4 class="terms-subheading">Indemnification</h4>
                        <p>You agree to indemnify and hold harmless Online Book Store from any claims, damages, or expenses arising from:</p>
                        <ul class="terms-list">
                            <li>Your violation of these Terms</li>
                            <li>Your infringement of third-party rights</li>
                            <li>Your use of our Services</li>
                            <li>Content you submit to our platform</li>
                        </ul>
                    </div>

                    <!-- User Conduct -->
                    <div id="conduct" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-hand-thumbs-up"></i>
                            Acceptable Use and Conduct
                        </h2>
                        
                        <h4 class="terms-subheading">Prohibited Activities</h4>
                        <p>When using our Services, you agree not to:</p>
                        <ul class="terms-list">
                            <li>Violate any applicable laws or regulations</li>
                            <li>Impersonate any person or entity</li>
                            <li>Engage in spamming or phishing activities</li>
                            <li>Distribute malware or harmful code</li>
                            <li>Attempt to gain unauthorized access to systems</li>
                            <li>Scrape or harvest data from our platform</li>
                            <li>Interfere with the proper functioning of Services</li>
                            <li>Bypass security measures or restrictions</li>
                        </ul>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-people text-primary"></i> Community Guidelines
                            </h5>
                            <p>We expect all users to:</p>
                            <ul>
                                <li>Treat others with respect and courtesy</li>
                                <li>Engage in constructive discussions</li>
                                <li>Report violations of these Terms</li>
                                <li>Contribute positively to our community</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Dispute Resolution -->
                    <div id="disputes" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-balance-scale"></i>
                            Dispute Resolution and Governing Law
                        </h2>
                        
                        <h4 class="terms-subheading">Governing Law</h4>
                        <p>These Terms shall be governed by and construed in accordance with the laws of the jurisdiction in which Online Book Store operates, without regard to its conflict of law provisions.</p>

                        <h4 class="terms-subheading">Dispute Resolution Process</h4>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="terms-step-card">
                                    <div class="step-number">1</div>
                                    <h6>Informal Resolution</h6>
                                    <p>Contact our support team to resolve the issue informally.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="terms-step-card">
                                    <div class="step-number">2</div>
                                    <h6>Mediation</h6>
                                    <p>If informal resolution fails, disputes may be submitted to mediation.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="terms-step-card">
                                    <div class="step-number">3</div>
                                    <h6>Binding Arbitration</h6>
                                    <p>Unresolved disputes shall be settled through binding arbitration.</p>
                                </div>
                            </div>
                        </div>

                        <h4 class="terms-subheading">Class Action Waiver</h4>
                        <p>You agree that any dispute resolution proceedings will be conducted only on an individual basis and not as a class, consolidated, or representative action.</p>
                    </div>

                    <!-- Modifications -->
                    <div id="modifications" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-arrow-repeat"></i>
                            Modifications to Terms
                        </h2>
                        <p>We reserve the right to modify these Terms at any time. When we make material changes, we will:</p>
                        <ul class="terms-list">
                            <li>Update the "Last Updated" date at the top of this page</li>
                            <li>Post the revised Terms on our website</li>
                            <li>Notify you via email for significant changes</li>
                            <li>Provide a reasonable notice period before changes take effect</li>
                        </ul>
                        
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="bi bi-bell-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Your Responsibility:</strong> Your continued use of our Services after Terms modifications constitutes acceptance of the updated Terms. We recommend reviewing these Terms periodically.
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Termination -->
                    <div id="termination" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-x-circle"></i>
                            Service Termination
                        </h2>
                        
                        <h4 class="terms-subheading">Termination by You</h4>
                        <p>You may terminate your account at any time by:</p>
                        <ul class="terms-list">
                            <li>Accessing your account settings and selecting "Delete Account"</li>
                            <li>Contacting our support team</li>
                            <li>Sending a written request to our address</li>
                        </ul>

                        <h4 class="terms-subheading">Termination by Us</h4>
                        <p>We may suspend or terminate your access to Services:</p>
                        <ul class="terms-list">
                            <li>For violation of these Terms</li>
                            <li>For suspected fraudulent activity</li>
                            <li>If required by law or legal process</li>
                            <li>At our sole discretion with or without notice</li>
                        </ul>

                        <div class="terms-card">
                            <h5 class="card-title">
                                <i class="bi bi-info-circle text-primary"></i> Effect of Termination
                            </h5>
                            <p>Upon termination:</p>
                            <ul>
                                <li>Your right to access Services will immediately cease</li>
                                <li>You will lose access to purchased digital content (unless otherwise required by law)</li>
                                <li>We may delete your account data after a reasonable period</li>
                                <li>Outstanding payment obligations remain due</li>
                            </ul>
                        </div>
                    </div>

                    <!-- Contact -->
                    <div id="contact" class="terms-block">
                        <h2 class="terms-heading">
                            <i class="bi bi-envelope"></i>
                            Contact Information
                        </h2>
                        <p>If you have questions about these Terms of Service, please contact us:</p>

                        <div class="terms-card">
                            <h5 class="card-title">Online Book Store</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <p class="mb-2"><strong><i class="bi bi-envelope me-2"></i>Email:</strong></p>
                                    <p>legal@onlinebookstore.com</p>
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
                                    <p class="mb-2"><strong><i class="bi bi-clock me-2"></i>Business Hours:</strong></p>
                                    <p>Monday - Friday, 9:00 AM - 5:00 PM (EST)</p>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-success">
                            <div class="d-flex">
                                <i class="bi bi-check-circle-fill me-3 fs-4"></i>
                                <div>
                                    <strong>Acknowledgment:</strong> By using Online Book Store's website and services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service. Thank you for being part of our community!
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
});
</script>

<?php include '../includes/footer.php'; ?>