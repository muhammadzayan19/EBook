<?php
session_start();
$page_title = "Privacy Policy";
require_once '../config/db.php';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/privacy.css">

<div class="legalPage privacyPage">
    <!-- Hero Section -->
    <section class="legalHero">
        <div class="legalHeroGlow"></div>
        <div class="legalHeroContent">
            <div class="legalBadge">
                <i class="bi bi-shield-lock"></i>
                Legal Document
            </div>
            <h1>
                Privacy <span>Policy</span>
            </h1>
            <p class="legalLastUpdated">
                <i class="bi bi-calendar-check"></i>
                Last Updated: October 30, 2025
            </p>
        </div>
    </section>

    <!-- Content Section -->
    <div class="legalContainer">
        <!-- Sidebar Navigation -->
        <aside class="legalSidebar">
            <div class="legalSidebarSticky">
                <h3>Table of Contents</h3>
                <nav class="legalNav" id="legalNav">
                    <!-- Will be populated dynamically -->
                </nav>

                <div class="legalSidebarCTA">
                    <h4>Need Help?</h4>
                    <p>Get in touch with us</p>
                    <a href="/contact" class="legalCTABtn">
                        <i class="bi bi-envelope"></i>
                        Contact Us
                    </a>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="legalContent">
            <!-- Introduction -->
            <section id="introduction" class="legalSection">
                <h2>
                    <i class="bi bi-file-text"></i>
                    Introduction
                </h2>
                <p>
                    Welcome to Online Book Store ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website or use our services.
                </p>
                <p>
                    Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site or use our services.
                </p>
                <div class="legalAlert">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <strong>Your Privacy Matters:</strong> We take your privacy seriously and are transparent about our data practices. This policy outlines what information we collect, why we collect it, and how we protect it.
                    </div>
                </div>
            </section>

            <!-- Information We Collect -->
            <section id="information-collect" class="legalSection">
                <h2>
                    <i class="bi bi-database"></i>
                    Information We Collect
                </h2>
                
                <h3>Personal Information You Provide</h3>
                <p>We collect personal information that you voluntarily provide to us when you:</p>
                <ul>
                    <li>Register an account on our platform</li>
                    <li>Make a purchase or subscribe to services</li>
                    <li>Subscribe to our newsletter</li>
                    <li>Participate in writing competitions</li>
                    <li>Contact us via email or support forms</li>
                </ul>

                <div class="legalCard">
                    <h4><i class="bi bi-person-badge"></i> Types of Personal Information</h4>
                    <ul>
                        <li><strong>Contact Information:</strong> Name, email address, phone number, mailing address</li>
                        <li><strong>Account Information:</strong> Username, password (encrypted), profile details</li>
                        <li><strong>Payment Information:</strong> Billing address, payment method details</li>
                        <li><strong>Competition Entries:</strong> Submitted essays, stories, and creative content</li>
                        <li><strong>Communications:</strong> Messages, feedback, support requests</li>
                    </ul>
                </div>

                <h3>Information Automatically Collected</h3>
                <p>When you visit our website, we automatically collect certain information:</p>
                <ul>
                    <li><strong>Device Information:</strong> IP address, browser type, operating system</li>
                    <li><strong>Usage Information:</strong> Pages viewed, time spent, links clicked</li>
                    <li><strong>Location Information:</strong> General location based on IP address</li>
                    <li><strong>Cookie Information:</strong> Data collected through cookies and similar technologies</li>
                </ul>
            </section>

            <!-- How We Use Information -->
            <section id="how-we-use" class="legalSection">
                <h2>
                    <i class="bi bi-gear"></i>
                    How We Use Your Information
                </h2>
                <p>We use the information we collect for various purposes:</p>

                <div class="legalServiceGrid">
                    <div class="legalCard">
                        <i class="bi bi-box-seam"></i>
                        <h4>Service Delivery</h4>
                        <p>To provide books, process orders, manage subscriptions, and deliver requested content.</p>
                    </div>
                    <div class="legalCard">
                        <i class="bi bi-envelope"></i>
                        <h4>Communication</h4>
                        <p>To send updates, newsletters, competition announcements, and important service notifications.</p>
                    </div>
                    <div class="legalCard">
                        <i class="bi bi-shield-check"></i>
                        <h4>Security & Protection</h4>
                        <p>To detect and prevent fraud, unauthorized access, and technical issues.</p>
                    </div>
                    <div class="legalCard">
                        <i class="bi bi-graph-up"></i>
                        <h4>Analytics & Improvement</h4>
                        <p>To analyze usage patterns and enhance user experience.</p>
                    </div>
                </div>

                <h3>Competition Management</h3>
                <p>For writing competitions, we use your information to:</p>
                <ul>
                    <li>Process and review competition entries</li>
                    <li>Announce winners and distribute prizes</li>
                    <li>Showcase winning entries (with permission)</li>
                    <li>Send competition updates and deadlines</li>
                </ul>
            </section>

            <!-- Information Sharing -->
            <section id="information-sharing" class="legalSection">
                <h2>
                    <i class="bi bi-lock"></i>
                    Information Sharing and Disclosure
                </h2>
                <p>We may share your information in the following circumstances:</p>

                <h3>With Service Providers</h3>
                <div class="legalCard">
                    <p>We share information with third-party service providers who perform services on our behalf:</p>
                    <ul>
                        <li>Payment processors (e.g., PayPal, Stripe)</li>
                        <li>Email service providers</li>
                        <li>Web hosting and cloud storage</li>
                        <li>Analytics services</li>
                    </ul>
                    <p><strong>Important:</strong> These providers are contractually obligated to protect your information.</p>
                </div>

                <h3>Legal Requirements</h3>
                <p>We may disclose your information if required by law or in response to:</p>
                <ul>
                    <li>Valid legal requests from authorities</li>
                    <li>Court orders or legal processes</li>
                    <li>Protection of our legal rights</li>
                    <li>Investigation of fraud or security issues</li>
                </ul>

                <div class="legalAlert warning">
                    <i class="bi bi-shield-check"></i>
                    <div>
                        <strong>We Never Sell Your Data:</strong> We do not sell, rent, or trade your personal information to third parties for marketing purposes.
                    </div>
                </div>
            </section>

            <!-- Data Security -->
            <section id="data-security" class="legalSection">
                <h2>
                    <i class="bi bi-shield-lock"></i>
                    Data Security
                </h2>
                <p>
                    We implement appropriate technical and organizational security measures to protect your personal information.
                </p>

                <h3>Security Measures Include:</h3>
                <ul>
                    <li><strong>Encryption:</strong> SSL/TLS encryption for data transmission</li>
                    <li><strong>Password Protection:</strong> Passwords are hashed using industry-standard algorithms</li>
                    <li><strong>Access Controls:</strong> Limited employee access to personal information</li>
                    <li><strong>Regular Audits:</strong> Periodic security assessments and updates</li>
                    <li><strong>Secure Storage:</strong> Protected servers and secure databases</li>
                </ul>

                <div class="legalAlert warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <strong>Important Notice:</strong> While we strive to protect your information, no method of internet transmission is 100% secure. We cannot guarantee absolute security.
                    </div>
                </div>
            </section>

            <!-- Cookies -->
            <section id="cookies" class="legalSection">
                <h2>
                    <i class="bi bi-cookie"></i>
                    Cookies and Tracking Technologies
                </h2>
                <p>
                    We use cookies and similar tracking technologies to collect information about your browsing activities.
                </p>

                <h3>Types of Cookies We Use:</h3>
                <div class="legalCard">
                    <h4>Essential Cookies</h4>
                    <p>Required for website functionality and security. Cannot be disabled.</p>
                </div>
                <div class="legalCard">
                    <h4>Performance Cookies</h4>
                    <p>Help us understand visitor interactions through anonymous analytics data.</p>
                </div>
                <div class="legalCard">
                    <h4>Functional Cookies</h4>
                    <p>Remember your preferences and settings to enhance experience.</p>
                </div>

                <h3>Managing Cookies</h3>
                <p>You can control cookies through your browser settings to accept, reject, or delete cookies.</p>
                <p><strong>Note:</strong> Disabling cookies may affect website functionality.</p>
            </section>

            <!-- Your Rights -->
            <section id="your-rights" class="legalSection">
                <h2>
                    <i class="bi bi-person-check"></i>
                    Your Privacy Rights
                </h2>
                <p>You have certain rights regarding your personal information:</p>

                <h3>General Rights</h3>
                <ul>
                    <li><strong>Access:</strong> Request a copy of personal information we hold</li>
                    <li><strong>Correction:</strong> Request correction of inaccurate information</li>
                    <li><strong>Deletion:</strong> Request deletion of your personal information</li>
                    <li><strong>Portability:</strong> Request transfer of your data</li>
                    <li><strong>Opt-Out:</strong> Unsubscribe from marketing communications</li>
                    <li><strong>Object:</strong> Object to certain processing purposes</li>
                </ul>

                <div class="legalCard">
                    <h4>How to Exercise Your Rights</h4>
                    <p>To exercise any of these rights, please contact us at:</p>
                    <p><strong>Email:</strong> privacy@onlinebookstore.com</p>
                    <p>We will respond to your request within 30 days.</p>
                </div>
            </section>

            <!-- Third-Party Services -->
            <section id="third-party" class="legalSection">
                <h2>
                    <i class="bi bi-link-45deg"></i>
                    Third-Party Services and Links
                </h2>
                <p>
                    Our website may contain links to third-party websites or services. We are not responsible for their privacy practices.
                </p>

                <h3>Third-Party Services We Use:</h3>
                <ul>
                    <li><strong>Analytics:</strong> Website analytics and tracking</li>
                    <li><strong>Payment Processors:</strong> Secure payment processing</li>
                    <li><strong>Email Services:</strong> Email marketing and communications</li>
                    <li><strong>Social Media:</strong> Social sharing and engagement</li>
                </ul>

                <div class="legalAlert">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Recommendation:</strong> Review the privacy policies of any third-party services you access through our website.
                    </div>
                </div>
            </section>

            <!-- Children's Privacy -->
            <section id="children" class="legalSection">
                <h2>
                    <i class="bi bi-people"></i>
                    Children's Privacy
                </h2>
                <p>
                    Our services are not intended for individuals under the age of 13. We do not knowingly collect personal information from children under 13.
                </p>
                <p>
                    If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately.
                </p>
            </section>

            <!-- Policy Changes -->
            <section id="changes" class="legalSection">
                <h2>
                    <i class="bi bi-arrow-repeat"></i>
                    Changes to This Privacy Policy
                </h2>
                <p>
                    We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements.
                </p>
                <p>When we make changes, we will:</p>
                <ul>
                    <li>Update the "Last Updated" date at the top</li>
                    <li>Post the revised policy on our website</li>
                    <li>Notify you via email for material changes</li>
                </ul>
                <p>
                    We encourage you to review this Privacy Policy periodically.
                </p>
            </section>

            <!-- Contact -->
            <section id="contact" class="legalSection">
                <h2>
                    <i class="bi bi-envelope"></i>
                    Contact Us
                </h2>
                <p>
                    If you have any questions or concerns regarding this Privacy Policy, please contact us:
                </p>

                <div class="legalCard">
                    <h4>Online Book Store</h4>
                    <p><strong>Email:</strong> privacy@onlinebookstore.com</p>
                    <p><strong>Phone:</strong> +1 (234) 567-890</p>
                    <p><strong>Address:</strong> 123 Book Street, Reading City, RC 12345</p>
                </div>

                <div class="legalAlert">
                    <i class="bi bi-clock"></i>
                    <div>
                        <strong>Response Time:</strong> We aim to respond to all privacy-related inquiries within 30 days.
                    </div>
                </div>
            </section>

            <!-- Acknowledgment -->
            <section class="legalSection">
                <div class="legalAlert">
                    <i class="bi bi-check-circle"></i>
                    <div>
                        <strong>Acknowledgment:</strong> By using our website and services, you acknowledge that you have read, understood, and agree to be bound by this Privacy Policy.
                    </div>
                </div>
            </section>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Define navigation items with their icons
    const navItems = [
        { id: 'introduction', icon: 'bi-file-text', label: 'Introduction' },
        { id: 'information-collect', icon: 'bi-database', label: 'Information We Collect' },
        { id: 'how-we-use', icon: 'bi-gear', label: 'How We Use Information' },
        { id: 'information-sharing', icon: 'bi-lock', label: 'Information Sharing' },
        { id: 'data-security', icon: 'bi-shield-lock', label: 'Data Security' },
        { id: 'cookies', icon: 'bi-cookie', label: 'Cookies & Tracking' },
        { id: 'your-rights', icon: 'bi-person-check', label: 'Your Rights' },
        { id: 'third-party', icon: 'bi-link-45deg', label: 'Third-Party Services' },
        { id: 'children', icon: 'bi-people', label: "Children's Privacy" },
        { id: 'changes', icon: 'bi-arrow-repeat', label: 'Policy Changes' },
        { id: 'contact', icon: 'bi-envelope', label: 'Contact Us' }
    ];

    const legalNav = document.getElementById('legalNav');
    let activeSection = '';

    // Generate navigation links
    navItems.forEach(item => {
        const button = document.createElement('button');
        button.className = 'legalNavLink';
        button.dataset.section = item.id;
        button.innerHTML = `<i class="bi ${item.icon}"></i>${item.label}`;
        
        button.addEventListener('click', () => scrollToSection(item.id));
        legalNav.appendChild(button);
    });

    // Smooth scroll to section
    function scrollToSection(id) {
        const element = document.getElementById(id);
        if (element) {
            const offset = 100;
            const elementPosition = element.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - offset;
            
            window.scrollTo({
                top: offsetPosition,
                behavior: 'smooth'
            });
        }
    }

    // Update active section on scroll
    function updateActiveSection(sectionId) {
        if (activeSection === sectionId) return;
        
        activeSection = sectionId;
        
        // Remove active class from all links
        document.querySelectorAll('.legalNavLink').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to current section link
        const activeLink = document.querySelector(`.legalNavLink[data-section="${sectionId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
        }
    }

    // Intersection Observer for active section tracking
    const observerOptions = {
        root: null,
        rootMargin: '-20% 0px -70% 0px',
        threshold: 0
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                updateActiveSection(entry.target.id);
            }
        });
    }, observerOptions);

    // Observe all sections
    document.querySelectorAll('.legalSection[id]').forEach(section => {
        observer.observe(section);
    });

    // Set initial active section
    const firstSection = document.querySelector('.legalSection[id]');
    if (firstSection) {
        updateActiveSection(firstSection.id);
    }

    // Handle hash navigation on page load
    if (window.location.hash) {
        const targetId = window.location.hash.substring(1);
        setTimeout(() => scrollToSection(targetId), 100);
    }
});
</script>

<?php include '../includes/footer.php'; ?>