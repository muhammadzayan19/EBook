<?php
session_start();
$page_title = "Terms of Service";
require_once '../config/db.php';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/terms.css">

<div class="legalPage termsPage">
    <!-- Hero Section -->
    <section class="legalHero">
        <div class="legalHeroGlow"></div>
        <div class="legalHeroContent">
            <div class="legalBadge">
                <i class="bi bi-file-earmark-text"></i>
                Legal Document
            </div>
            <h1>
                Terms of <span>Service</span>
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
                    Agreement to Terms
                </h2>
                <p>
                    Welcome to Online Book Store. These Terms of Service ("Terms") govern your access to and use of our website, services, and applications (collectively, the "Services"). By accessing or using our Services, you agree to be bound by these Terms.
                </p>
                <p>
                    Please read these Terms carefully before using our Services. If you do not agree to these Terms, you may not access or use our Services.
                </p>
                <div class="legalAlert">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <strong>Important Notice:</strong> These Terms constitute a legally binding agreement between you and Online Book Store. By creating an account or using our Services, you acknowledge that you have read, understood, and agree to be bound by these Terms.
                    </div>
                </div>
            </section>

            <!-- Account Registration -->
            <section id="account" class="legalSection">
                <h2>
                    <i class="bi bi-person-plus"></i>
                    Account Registration and Eligibility
                </h2>
                
                <h3>Eligibility Requirements</h3>
                <p>To use our Services, you must:</p>
                <ul>
                    <li>Be at least 13 years of age</li>
                    <li>Have the legal capacity to enter into binding contracts</li>
                    <li>Not be prohibited from using our Services under applicable law</li>
                    <li>Provide accurate and complete registration information</li>
                </ul>

                <div class="legalCard">
                    <h4><i class="bi bi-shield-check"></i> Account Security</h4>
                    <p>You are responsible for:</p>
                    <ul>
                        <li>Maintaining the confidentiality of your account credentials</li>
                        <li>All activities that occur under your account</li>
                        <li>Notifying us immediately of any unauthorized access</li>
                        <li>Ensuring your account information remains accurate and current</li>
                    </ul>
                </div>

                <h3>Account Termination</h3>
                <p>We reserve the right to suspend or terminate your account if you:</p>
                <ul>
                    <li>Violate these Terms or any applicable laws</li>
                    <li>Engage in fraudulent or abusive behavior</li>
                    <li>Provide false or misleading information</li>
                    <li>Infringe upon intellectual property rights</li>
                </ul>

                <div class="legalAlert warning">
                    <i class="bi bi-exclamation-circle"></i>
                    <div>
                        <strong>Warning:</strong> Creating multiple accounts to circumvent restrictions or engage in prohibited activities may result in permanent account suspension and legal action.
                    </div>
                </div>
            </section>

            <!-- User Content -->
            <section id="user-content" class="legalSection">
                <h2>
                    <i class="bi bi-file-earmark-richtext"></i>
                    User-Generated Content
                </h2>
                
                <h3>Content You Submit</h3>
                <p>When you submit content to our platform (including competition entries, reviews, and comments), you grant us:</p>
                <ul>
                    <li>A worldwide, non-exclusive, royalty-free license to use, reproduce, and display your content</li>
                    <li>The right to modify or adapt your content for technical or presentation purposes</li>
                    <li>Permission to showcase winning competition entries on our platform</li>
                </ul>

                <div class="legalCard">
                    <h4><i class="bi bi-copyright"></i> Content Ownership</h4>
                    <p>You retain all ownership rights to your content. However, you represent and warrant that:</p>
                    <ul>
                        <li>You own or have the necessary rights to submit the content</li>
                        <li>Your content does not infringe on third-party rights</li>
                        <li>Your content complies with all applicable laws and regulations</li>
                        <li>Your content does not contain harmful, offensive, or illegal material</li>
                    </ul>
                </div>

                <h3>Prohibited Content</h3>
                <p>You may not submit content that:</p>
                <ul>
                    <li>Infringes on intellectual property or proprietary rights</li>
                    <li>Contains hate speech, harassment, or discrimination</li>
                    <li>Promotes violence, illegal activities, or harm to others</li>
                    <li>Includes malware, viruses, or malicious code</li>
                    <li>Violates privacy rights or discloses personal information without consent</li>
                    <li>Is false, misleading, or fraudulent</li>
                </ul>

                <div class="legalAlert warning">
                    <i class="bi bi-shield-x"></i>
                    <div>
                        <strong>Content Removal:</strong> We reserve the right to remove any content that violates these Terms without prior notice. Repeated violations may result in account termination.
                    </div>
                </div>
            </section>

            <!-- Purchases -->
            <section id="purchases" class="legalSection">
                <h2>
                    <i class="bi bi-cart-check"></i>
                    Purchases and Payments
                </h2>
                
                <h3>Book Purchases and Subscriptions</h3>
                <p>When purchasing books or subscribing to our services:</p>
                <ul>
                    <li>All prices are displayed in USD unless otherwise stated</li>
                    <li>Prices are subject to change without notice</li>
                    <li>Payment must be made through our authorized payment processors</li>
                    <li>You are responsible for all applicable taxes and fees</li>
                </ul>

                <div class="legalServiceGrid">
                    <div class="legalCard">
                        <i class="bi bi-credit-card"></i>
                        <h4>Payment Methods</h4>
                        <p>We accept major credit cards, debit cards, and approved third-party payment services.</p>
                    </div>
                    <div class="legalCard">
                        <i class="bi bi-arrow-clockwise"></i>
                        <h4>Refund Policy</h4>
                        <p>Digital books may be refunded within 14 days if not accessed or downloaded.</p>
                    </div>
                    <div class="legalCard">
                        <i class="bi bi-receipt"></i>
                        <h4>Billing</h4>
                        <p>Subscription charges are billed on a recurring basis until cancelled.</p>
                    </div>
                </div>

                <h3>License to Digital Content</h3>
                <div class="legalCard">
                    <h4><i class="bi bi-book"></i> E-Book License Terms</h4>
                    <p>When you purchase an e-book, you receive a limited, non-exclusive, non-transferable license to:</p>
                    <ul>
                        <li>Access and read the content for personal, non-commercial use</li>
                        <li>Download the content to authorized devices</li>
                    </ul>
                    <p><strong>You may not:</strong></p>
                    <ul>
                        <li>Resell, distribute, or share purchased content</li>
                        <li>Remove or modify copyright notices or DRM protection</li>
                        <li>Use content for commercial purposes without permission</li>
                    </ul>
                </div>

                <div class="legalAlert">
                    <i class="bi bi-info-circle"></i>
                    <div>
                        <strong>Cancellation Policy:</strong> Subscriptions can be cancelled at any time through your account settings. Cancellation will take effect at the end of the current billing period.
                    </div>
                </div>
            </section>

            <!-- Competitions -->
            <section id="competitions" class="legalSection">
                <h2>
                    <i class="bi bi-trophy"></i>
                    Writing Competitions
                </h2>
                
                <h3>Competition Rules</h3>
                <p>By participating in our writing competitions, you agree to:</p>
                <ul>
                    <li>Submit only original work that you have created</li>
                    <li>Meet all specified deadlines and requirements</li>
                    <li>Allow us to display winning entries on our platform</li>
                    <li>Accept the judges' decisions as final</li>
                </ul>

                <div class="legalCard">
                    <h4><i class="bi bi-award"></i> Prizes and Recognition</h4>
                    <p>Competition winners will receive:</p>
                    <ul>
                        <li>Prizes as specified in the competition announcement</li>
                        <li>Recognition on our website and promotional materials</li>
                        <li>Publication opportunities (where applicable)</li>
                    </ul>
                    <p><strong>Important:</strong> Prize fulfillment may take up to 30 days. Winners must respond within 14 days of notification.</p>
                </div>

                <h3>Entry Requirements</h3>
                <ul>
                    <li>All submissions must be your original work</li>
                    <li>Previously published work may be ineligible (check specific competition rules)</li>
                    <li>Entries must comply with word count and format specifications</li>
                    <li>Multiple entries may be allowed per person (varies by competition)</li>
                </ul>

                <div class="legalAlert">
                    <i class="bi bi-lightbulb"></i>
                    <div>
                        <strong>Intellectual Property:</strong> You retain all rights to your competition entries. However, winning entries may be featured in our marketing materials with proper attribution.
                    </div>
                </div>
            </section>

            <!-- Intellectual Property -->
            <section id="intellectual-property" class="legalSection">
                <h2>
                    <i class="bi bi-patch-check"></i>
                    Intellectual Property Rights
                </h2>
                
                <h3>Our Content and Trademarks</h3>
                <p>All content on our platform, including but not limited to:</p>
                <ul>
                    <li>Text, graphics, logos, and images</li>
                    <li>Software, code, and functionality</li>
                    <li>Design elements and user interface</li>
                    <li>Trademarks and service marks</li>
                </ul>
                <p>
                    These are owned by Online Book Store or our licensors and are protected by copyright, trademark, and other intellectual property laws.
                </p>

                <div class="legalCard">
                    <h4><i class="bi bi-slash-circle"></i> Prohibited Uses</h4>
                    <p>You may not, without our express written permission:</p>
                    <ul>
                        <li>Copy, reproduce, or distribute our content</li>
                        <li>Modify, adapt, or create derivative works</li>
                        <li>Reverse engineer or extract source code</li>
                        <li>Use our trademarks or branding</li>
                        <li>Frame or mirror any part of our website</li>
                    </ul>
                </div>

                <h3>DMCA Copyright Policy</h3>
                <p>
                    We respect intellectual property rights and expect our users to do the same. If you believe your copyrighted work has been infringed, please contact us with:
                </p>
                <ul>
                    <li>Description of the copyrighted work</li>
                    <li>Location of the infringing material</li>
                    <li>Your contact information</li>
                    <li>A statement of good faith belief</li>
                    <li>Electronic or physical signature</li>
                </ul>
            </section>

            <!-- Disclaimers -->
            <section id="disclaimers" class="legalSection">
                <h2>
                    <i class="bi bi-exclamation-diamond"></i>
                    Disclaimers and Limitations
                </h2>
                
                <h3>Service Availability</h3>
                <div class="legalCard">
                    <p>Our Services are provided "as is" and "as available." We do not guarantee that:</p>
                    <ul>
                        <li>Services will be uninterrupted or error-free</li>
                        <li>All content will be accurate or reliable</li>
                        <li>Defects will be corrected immediately</li>
                        <li>Services will meet your specific requirements</li>
                    </ul>
                </div>

                <h3>Limitation of Liability</h3>
                <p>To the maximum extent permitted by law, Online Book Store shall not be liable for:</p>
                <ul>
                    <li>Indirect, incidental, or consequential damages</li>
                    <li>Loss of profits, data, or business opportunities</li>
                    <li>Damages resulting from unauthorized access to your account</li>
                    <li>Third-party content or services</li>
                </ul>

                <div class="legalAlert warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        <strong>Important:</strong> Our total liability for any claim arising from your use of our Services is limited to the amount you paid us in the twelve (12) months preceding the claim.
                    </div>
                </div>

                <h3>Indemnification</h3>
                <p>You agree to indemnify and hold harmless Online Book Store from any claims, damages, or expenses arising from:</p>
                <ul>
                    <li>Your violation of these Terms</li>
                    <li>Your infringement of third-party rights</li>
                    <li>Your use of our Services</li>
                    <li>Content you submit to our platform</li>
                </ul>
            </section>

            <!-- User Conduct -->
            <section id="conduct" class="legalSection">
                <h2>
                    <i class="bi bi-hand-thumbs-up"></i>
                    Acceptable Use and Conduct
                </h2>
                
                <h3>Prohibited Activities</h3>
                <p>When using our Services, you agree not to:</p>
                <ul>
                    <li>Violate any applicable laws or regulations</li>
                    <li>Impersonate any person or entity</li>
                    <li>Engage in spamming or phishing activities</li>
                    <li>Distribute malware or harmful code</li>
                    <li>Attempt to gain unauthorized access to systems</li>
                    <li>Scrape or harvest data from our platform</li>
                    <li>Interfere with the proper functioning of Services</li>
                    <li>Bypass security measures or restrictions</li>
                </ul>

                <div class="legalCard">
                    <h4><i class="bi bi-people"></i> Community Guidelines</h4>
                    <p>We expect all users to:</p>
                    <ul>
                        <li>Treat others with respect and courtesy</li>
                        <li>Engage in constructive discussions</li>
                        <li>Report violations of these Terms</li>
                        <li>Contribute positively to our community</li>
                    </ul>
                </div>

                <div class="legalAlert warning">
                    <i class="bi bi-shield-exclamation"></i>
                    <div>
                        <strong>Enforcement:</strong> Violations of these conduct rules may result in immediate account suspension, content removal, and potential legal action.
                    </div>
                </div>
            </section>

            <!-- Dispute Resolution -->
            <section id="disputes" class="legalSection">
                <h2>
                    <i class="bi bi-balance-scale"></i>
                    Dispute Resolution and Governing Law
                </h2>
                
                <h3>Governing Law</h3>
                <p>
                    These Terms shall be governed by and construed in accordance with the laws of the jurisdiction in which Online Book Store operates, without regard to its conflict of law provisions.
                </p>

                <h3>Dispute Resolution Process</h3>
                <div class="legalCard">
                    <h4>Step 1: Informal Resolution</h4>
                    <p>Contact our support team to resolve the issue informally before pursuing formal action.</p>
                </div>
                <div class="legalCard">
                    <h4>Step 2: Mediation</h4>
                    <p>If informal resolution fails, disputes may be submitted to mediation.</p>
                </div>
                <div class="legalCard">
                    <h4>Step 3: Binding Arbitration</h4>
                    <p>Unresolved disputes shall be settled through binding arbitration in accordance with applicable arbitration rules.</p>
                </div>

                <h3>Class Action Waiver</h3>
                <p>
                    You agree that any dispute resolution proceedings will be conducted only on an individual basis and not as a class, consolidated, or representative action.
                </p>
            </section>

            <!-- Modifications -->
            <section id="modifications" class="legalSection">
                <h2>
                    <i class="bi bi-arrow-repeat"></i>
                    Modifications to Terms
                </h2>
                <p>
                    We reserve the right to modify these Terms at any time. When we make material changes, we will:
                </p>
                <ul>
                    <li>Update the "Last Updated" date at the top of this page</li>
                    <li>Post the revised Terms on our website</li>
                    <li>Notify you via email for significant changes</li>
                    <li>Provide a reasonable notice period before changes take effect</li>
                </ul>
                
                <div class="legalAlert">
                    <i class="bi bi-bell"></i>
                    <div>
                        <strong>Your Responsibility:</strong> Your continued use of our Services after Terms modifications constitutes acceptance of the updated Terms. We recommend reviewing these Terms periodically.
                    </div>
                </div>
            </section>

            <!-- Termination -->
            <section id="termination" class="legalSection">
                <h2>
                    <i class="bi bi-x-circle"></i>
                    Service Termination
                </h2>
                
                <h3>Termination by You</h3>
                <p>You may terminate your account at any time by:</p>
                <ul>
                    <li>Accessing your account settings and selecting "Delete Account"</li>
                    <li>Contacting our support team</li>
                    <li>Sending a written request to our address</li>
                </ul>

                <h3>Termination by Us</h3>
                <p>We may suspend or terminate your access to Services:</p>
                <ul>
                    <li>For violation of these Terms</li>
                    <li>For suspected fraudulent activity</li>
                    <li>If required by law or legal process</li>
                    <li>At our sole discretion with or without notice</li>
                </ul>

                <div class="legalCard">
                    <h4><i class="bi bi-info-circle"></i> Effect of Termination</h4>
                    <p>Upon termination:</p>
                    <ul>
                        <li>Your right to access Services will immediately cease</li>
                        <li>You will lose access to purchased digital content (unless otherwise required by law)</li>
                        <li>We may delete your account data after a reasonable period</li>
                        <li>Outstanding payment obligations remain due</li>
                    </ul>
                </div>
            </section>

            <!-- Miscellaneous -->
            <section id="miscellaneous" class="legalSection">
                <h2>
                    <i class="bi bi-three-dots"></i>
                    Miscellaneous Provisions
                </h2>
                
                <h3>Entire Agreement</h3>
                <p>
                    These Terms, together with our Privacy Policy and any other legal notices published on our platform, constitute the entire agreement between you and Online Book Store.
                </p>

                <h3>Severability</h3>
                <p>
                    If any provision of these Terms is found to be unenforceable, the remaining provisions will continue in full force and effect.
                </p>

                <h3>Waiver</h3>
                <p>
                    Our failure to enforce any right or provision of these Terms will not be considered a waiver of those rights.
                </p>

                <h3>Assignment</h3>
                <p>
                    You may not assign or transfer these Terms without our written consent. We may assign our rights without restriction.
                </p>

                <h3>Force Majeure</h3>
                <p>
                    We shall not be liable for any failure to perform due to circumstances beyond our reasonable control, including natural disasters, wars, or technical failures.
                </p>
            </section>

            <!-- Contact -->
            <section id="contact" class="legalSection">
                <h2>
                    <i class="bi bi-envelope"></i>
                    Contact Information
                </h2>
                <p>
                    If you have questions about these Terms of Service, please contact us:
                </p>

                <div class="legalCard">
                    <h4>Online Book Store</h4>
                    <p><strong>Email:</strong> legal@onlinebookstore.com</p>
                    <p><strong>Phone:</strong> +1 (234) 567-890</p>
                    <p><strong>Address:</strong> 123 Book Street, Reading City, RC 12345</p>
                    <p><strong>Business Hours:</strong> Monday - Friday, 9:00 AM - 5:00 PM (EST)</p>
                </div>

                <div class="legalAlert">
                    <i class="bi bi-clock"></i>
                    <div>
                        <strong>Response Time:</strong> We aim to respond to all inquiries within 3-5 business days.
                    </div>
                </div>
            </section>

            <!-- Acknowledgment -->
            <section class="legalSection">
                <div class="legalAlert">
                    <i class="bi bi-check-circle"></i>
                    <div>
                        <strong>Acknowledgment:</strong> By using Online Book Store's website and services, you acknowledge that you have read, understood, and agree to be bound by these Terms of Service. Thank you for being part of our community!
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
        { id: 'introduction', icon: 'bi-file-text', label: 'Agreement to Terms' },
        { id: 'account', icon: 'bi-person-plus', label: 'Account & Eligibility' },
        { id: 'user-content', icon: 'bi-file-earmark-richtext', label: 'User Content' },
        { id: 'purchases', icon: 'bi-cart-check', label: 'Purchases & Payments' },
        { id: 'competitions', icon: 'bi-trophy', label: 'Writing Competitions' },
        { id: 'intellectual-property', icon: 'bi-patch-check', label: 'Intellectual Property' },
        { id: 'disclaimers', icon: 'bi-exclamation-diamond', label: 'Disclaimers & Limits' },
        { id: 'conduct', icon: 'bi-hand-thumbs-up', label: 'User Conduct' },
        { id: 'disputes', icon: 'bi-balance-scale', label: 'Dispute Resolution' },
        { id: 'modifications', icon: 'bi-arrow-repeat', label: 'Terms Modifications' },
        { id: 'termination', icon: 'bi-x-circle', label: 'Service Termination' },
        { id: 'miscellaneous', icon: 'bi-three-dots', label: 'Miscellaneous' },
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