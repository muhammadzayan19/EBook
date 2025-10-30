<?php
session_start();
$page_title = "Privacy Policy";
require_once '../config/db.php';
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/privacy.css">

<section class="legal-page-header">
    <div class="container">
        <div class="legal-header-content">
            <div class="legal-icon">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1>Privacy Policy</h1>
            <p class="lead">Your privacy is important to us. This policy explains how we collect, use, and protect your personal information.</p>
            <div class="legal-meta">
                <div class="legal-meta-item">
                    <i class="bi bi-calendar-check"></i>
                    <span>Last Updated: October 29, 2025</span>
                </div>
                <div class="legal-meta-item">
                    <i class="bi bi-clock-history"></i>
                    <span>Effective Date: January 1, 2024</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="legal-content-section">
    <div class="container">
        <div class="legal-container">
            <div class="legal-layout">
                <!-- Sidebar Table of Contents -->
                <aside class="legal-sidebar-toc" id="sidebarToc">
                    <div class="toc-progress-bar" id="tocProgress"></div>
                    <h5><i class="bi bi-list-ul"></i> Contents</h5>
                    <ul class="legal-toc-list" id="tocList">
                        <!-- Will be populated dynamically -->
                    </ul>
                </aside>

                <!-- Main Content -->
                <div class="legal-main-content">
                    <!-- Mobile Quick Navigation (Fallback) -->
                    <div class="legal-quick-nav">
                        <h5><i class="bi bi-list-ul"></i> Quick Navigation</h5>
                        <ul id="mobileNav">
                            <!-- Will be populated dynamically -->
                        </ul>
                    </div>

                    <!-- Introduction -->
                    <div class="legal-section" id="introduction">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-info-circle"></i>
                            </div>
                            <h2>Introduction</h2>
                        </div>
                        <p>Welcome to our Privacy Policy. This document outlines how Online Book Store ("we", "us", "our") collects, uses, maintains, and discloses information collected from users of our website and services.</p>
                        <p>By accessing or using our services, you acknowledge that you have read, understood, and agree to be bound by this Privacy Policy. If you do not agree with our policies and practices, please do not use our services.</p>
                        
                        <div class="legal-highlight success">
                            <h4><i class="bi bi-shield-check"></i> Our Commitment</h4>
                            <p>We are committed to protecting your privacy and ensuring the security of your personal information. We employ industry-standard security measures to safeguard your data.</p>
                        </div>
                    </div>

                    <!-- Information Collection -->
                    <div class="legal-section" id="information-collection">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-database"></i>
                            </div>
                            <h2>Information We Collect</h2>
                        </div>
                        
                        <h3>Personal Information</h3>
                        <p>We collect personal information that you voluntarily provide to us when you:</p>
                        <ul>
                            <li><strong>Register an account:</strong> Full name, email address, password, phone number, and mailing address</li>
                            <li><strong>Make a purchase:</strong> Payment information, billing address, and transaction details</li>
                            <li><strong>Subscribe to services:</strong> Payment method and subscription preferences</li>
                            <li><strong>Participate in competitions:</strong> Submitted essays, stories, and competition entries</li>
                            <li><strong>Contact us:</strong> Communication content, email address, and any information you choose to provide</li>
                        </ul>

                        <h3>Automatically Collected Information</h3>
                        <p>When you visit our website, we automatically collect certain information:</p>
                        <ul>
                            <li>IP address and device information</li>
                            <li>Browser type and version</li>
                            <li>Operating system</li>
                            <li>Pages visited and time spent on pages</li>
                            <li>Referring website addresses</li>
                            <li>Date and time of access</li>
                        </ul>

                        <div class="legal-definition-list">
                            <div class="legal-definition-item">
                                <div class="legal-definition-term">Personal Data</div>
                                <div class="legal-definition-desc">Any information that can be used to identify you as an individual, including your name, email address, and phone number.</div>
                            </div>
                            <div class="legal-definition-item">
                                <div class="legal-definition-term">Usage Data</div>
                                <div class="legal-definition-desc">Information about how you interact with our services, including pages viewed, features used, and actions taken.</div>
                            </div>
                            <div class="legal-definition-item">
                                <div class="legal-definition-term">Device Information</div>
                                <div class="legal-definition-desc">Technical information about the device you use to access our services, such as device type, browser, and operating system.</div>
                            </div>
                        </div>
                    </div>

                    <!-- How We Use Information -->
                    <div class="legal-section" id="usage">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-gear"></i>
                            </div>
                            <h2>How We Use Your Information</h2>
                        </div>
                        
                        <p>We use the collected information for various purposes:</p>
                        
                        <div class="legal-highlight">
                            <h4><i class="bi bi-check-circle"></i> Primary Uses</h4>
                            <ul>
                                <li><strong>Service Delivery:</strong> To provide, operate, and maintain our services</li>
                                <li><strong>Order Processing:</strong> To process your book orders and manage subscriptions</li>
                                <li><strong>Account Management:</strong> To create and manage your user account</li>
                                <li><strong>Communication:</strong> To send you service-related emails and notifications</li>
                                <li><strong>Competition Management:</strong> To administer writing competitions and announce winners</li>
                                <li><strong>Customer Support:</strong> To respond to your inquiries and provide assistance</li>
                                <li><strong>Improvements:</strong> To analyze usage patterns and improve our services</li>
                                <li><strong>Security:</strong> To detect, prevent, and address technical issues and fraudulent activity</li>
                            </ul>
                        </div>

                        <h3>Marketing Communications</h3>
                        <p>With your consent, we may send you:</p>
                        <ul>
                            <li>Newsletter updates about new books and features</li>
                            <li>Special offers and promotional materials</li>
                            <li>Competition announcements and deadlines</li>
                            <li>Subscription renewal reminders</li>
                        </ul>
                        <p><strong>You can opt-out</strong> of marketing emails at any time by clicking the unsubscribe link in any email or updating your preferences in your account settings.</p>
                    </div>

                    <!-- Information Sharing -->
                    <div class="legal-section" id="sharing">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-share"></i>
                            </div>
                            <h2>How We Share Your Information</h2>
                        </div>
                        
                        <p>We do not sell, trade, or rent your personal information to third parties. We may share your information only in the following circumstances:</p>

                        <h3>Service Providers</h3>
                        <p>We may share your information with trusted third-party service providers who assist us in:</p>
                        <ul>
                            <li>Payment processing (e.g., PayPal, Stripe)</li>
                            <li>Email delivery services</li>
                            <li>Cloud storage and hosting</li>
                            <li>Analytics and performance monitoring</li>
                            <li>Customer support tools</li>
                        </ul>
                        <p>These service providers are bound by confidentiality agreements and are only permitted to use your information as necessary to provide services to us.</p>

                        <h3>Legal Requirements</h3>
                        <p>We may disclose your information if required to do so by law or in response to:</p>
                        <ul>
                            <li>Valid legal processes (court orders, subpoenas)</li>
                            <li>Government or regulatory requests</li>
                            <li>Protection of our rights and property</li>
                            <li>Prevention of fraud or illegal activities</li>
                            <li>Emergency situations involving public safety</li>
                        </ul>

                        <div class="legal-highlight warning">
                            <h4><i class="bi bi-exclamation-triangle"></i> Business Transfers</h4>
                            <p>In the event of a merger, acquisition, or sale of assets, your information may be transferred to the acquiring entity. We will notify you via email and/or prominent notice on our website of any change in ownership.</p>
                        </div>
                    </div>

                    <!-- Data Security -->
                    <div class="legal-section" id="security">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                            <h2>Data Security</h2>
                        </div>
                        
                        <p>We implement robust security measures to protect your personal information:</p>

                        <div class="legal-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Security Measure</th>
                                        <th>Description</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>Encryption</strong></td>
                                        <td>SSL/TLS encryption for data transmission</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Password Protection</strong></td>
                                        <td>Passwords are hashed using industry-standard algorithms</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Access Controls</strong></td>
                                        <td>Limited employee access to personal information</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Regular Audits</strong></td>
                                        <td>Periodic security assessments and updates</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Secure Storage</strong></td>
                                        <td>Data stored in secure, encrypted databases</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="legal-highlight danger">
                            <h4><i class="bi bi-exclamation-circle"></i> Important Notice</h4>
                            <p>While we strive to protect your personal information, no method of transmission over the Internet or electronic storage is 100% secure. We cannot guarantee absolute security, and you transmit information at your own risk.</p>
                        </div>
                    </div>

                    <!-- Cookies -->
                    <div class="legal-section" id="cookies">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-cookie"></i>
                            </div>
                            <h2>Cookies and Tracking Technologies</h2>
                        </div>
                        
                        <p>We use cookies and similar tracking technologies to enhance your experience on our website.</p>

                        <h3>Types of Cookies We Use</h3>
                        <ul>
                            <li><strong>Essential Cookies:</strong> Required for basic site functionality (login, shopping cart)</li>
                            <li><strong>Performance Cookies:</strong> Help us understand how visitors use our site</li>
                            <li><strong>Functionality Cookies:</strong> Remember your preferences and settings</li>
                            <li><strong>Analytics Cookies:</strong> Track site usage and performance metrics</li>
                        </ul>

                        <h3>Managing Cookies</h3>
                        <p>You can control and manage cookies through your browser settings. Most browsers allow you to:</p>
                        <ul>
                            <li>View what cookies are stored and delete them individually</li>
                            <li>Block third-party cookies</li>
                            <li>Block cookies from specific sites</li>
                            <li>Delete all cookies when you close your browser</li>
                        </ul>
                        <p><strong>Note:</strong> Disabling cookies may affect the functionality of our website.</p>
                    </div>

                    <!-- Your Rights -->
                    <div class="legal-section" id="rights">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-person-check"></i>
                            </div>
                            <h2>Your Privacy Rights</h2>
                        </div>
                        
                        <p>You have certain rights regarding your personal information:</p>

                        <div class="legal-highlight success">
                            <h4><i class="bi bi-hand-thumbs-up"></i> Your Rights Include</h4>
                            <ul>
                                <li><strong>Access:</strong> Request a copy of the personal information we hold about you</li>
                                <li><strong>Correction:</strong> Request correction of inaccurate or incomplete information</li>
                                <li><strong>Deletion:</strong> Request deletion of your personal information (subject to legal obligations)</li>
                                <li><strong>Portability:</strong> Request transfer of your data to another service provider</li>
                                <li><strong>Objection:</strong> Object to certain types of processing of your information</li>
                                <li><strong>Withdrawal:</strong> Withdraw consent for data processing at any time</li>
                                <li><strong>Restriction:</strong> Request restriction of processing in certain circumstances</li>
                            </ul>
                        </div>

                        <h3>How to Exercise Your Rights</h3>
                        <p>To exercise any of these rights, please:</p>
                        <ol>
                            <li>Log into your account and update your settings</li>
                            <li>Contact us at <a href="mailto:privacy@onlinebookstore.com">privacy@onlinebookstore.com</a></li>
                            <li>Submit a written request to our mailing address</li>
                        </ol>
                        <p>We will respond to your request within 30 days.</p>
                    </div>

                    <!-- Children's Privacy -->
                    <div class="legal-section" id="childrens-privacy">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-people"></i>
                            </div>
                            <h2>Children's Privacy</h2>
                        </div>
                        
                        <p>Our services are not directed to individuals under the age of 13. We do not knowingly collect personal information from children under 13.</p>
                        <p>If you are a parent or guardian and believe your child has provided us with personal information, please contact us immediately. We will take steps to remove such information from our systems.</p>

                        <div class="legal-highlight warning">
                            <h4><i class="bi bi-exclamation-triangle"></i> Parental Notice</h4>
                            <p>For users between 13 and 18 years of age, we recommend parental guidance and supervision when using our services.</p>
                        </div>
                    </div>

                    <!-- Data Retention -->
                    <div class="legal-section" id="data-retention">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-archive"></i>
                            </div>
                            <h2>Data Retention</h2>
                        </div>
                        
                        <p>We retain your personal information only for as long as necessary to:</p>
                        <ul>
                            <li>Provide our services to you</li>
                            <li>Comply with legal, accounting, or reporting requirements</li>
                            <li>Resolve disputes and enforce our agreements</li>
                            <li>Protect against fraud and abuse</li>
                        </ul>

                        <h3>Retention Periods</h3>
                        <div class="legal-table">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Data Type</th>
                                        <th>Retention Period</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Account Information</td>
                                        <td>Until account deletion requested</td>
                                    </tr>
                                    <tr>
                                        <td>Transaction Records</td>
                                        <td>7 years (legal requirement)</td>
                                    </tr>
                                    <tr>
                                        <td>Competition Entries</td>
                                        <td>2 years after competition ends</td>
                                    </tr>
                                    <tr>
                                        <td>Marketing Preferences</td>
                                        <td>Until opt-out requested</td>
                                    </tr>
                                    <tr>
                                        <td>Support Communications</td>
                                        <td>3 years from last contact</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Changes to Policy -->
                    <div class="legal-section" id="changes">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-arrow-repeat"></i>
                            </div>
                            <h2>Changes to This Privacy Policy</h2>
                        </div>
                        
                        <p>We may update this Privacy Policy from time to time to reflect changes in our practices or legal requirements. When we make changes:</p>
                        <ul>
                            <li>We will update the "Last Updated" date at the top of this page</li>
                            <li>For material changes, we will notify you via email or prominent notice on our website</li>
                            <li>Your continued use of our services after changes constitutes acceptance of the updated policy</li>
                        </ul>

                        <div class="legal-timeline">
                            <div class="legal-timeline-item">
                                <div class="legal-timeline-date">October 29, 2025</div>
                                <div class="legal-timeline-content">Current version - Added subscription data handling details</div>
                            </div>
                            <div class="legal-timeline-item">
                                <div class="legal-timeline-date">January 1, 2024</div>
                                <div class="legal-timeline-content">Initial publication of Privacy Policy</div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="legal-section" id="contact">
                        <div class="legal-section-header">
                            <div class="legal-section-icon">
                                <i class="bi bi-envelope"></i>
                            </div>
                            <h2>Contact Us</h2>
                        </div>
                        
                        <p>If you have any questions, concerns, or requests regarding this Privacy Policy or our data practices, please contact us:</p>

                        <div class="legal-contact-card">
                            <h4>Get in Touch</h4>
                            <p>Our Privacy Team is here to help</p>
                            <div class="legal-contact-info">
                                <a href="mailto:privacy@onlinebookstore.com" class="legal-contact-item">
                                    <i class="bi bi-envelope-fill"></i>
                                    <span>privacy@onlinebookstore.com</span>
                                </a>
                                <a href="tel:+1234567890" class="legal-contact-item">
                                    <i class="bi bi-telephone-fill"></i>
                                    <span>+1 (234) 567-890</span>
                                </a>
                                <div class="legal-contact-item">
                                    <i class="bi bi-geo-alt-fill"></i>
                                    <span>123 Book Street, Reading City, RC 12345</span>
                                </div>
                            </div>
                            <a href="contact.php" class="btn btn-light btn-lg mt-3">
                                <i class="bi bi-chat-dots me-2"></i>Contact Support
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Footer Actions -->
<section class="legal-footer-actions">
    <div class="container">
        <div class="legal-action-cards">
            <div class="legal-action-card">
                <i class="bi bi-file-text"></i>
                <h5>Terms & Conditions</h5>
                <p>Read our terms of service and usage guidelines</p>
                <a href="terms.php" class="btn btn-primary">View Terms</a>
            </div>
            <div class="legal-action-card">
                <i class="bi bi-question-circle"></i>
                <h5>FAQs</h5>
                <p>Find answers to commonly asked questions</p>
                <a href="faq.php" class="btn btn-outline-primary">Browse FAQs</a>
            </div>
            <div class="legal-action-card">
                <i class="bi bi-headset"></i>
                <h5>Support Center</h5>
                <p>Get help with your account or orders</p>
                <a href="contact.php" class="btn btn-outline-primary">Get Help</a>
            </div>
        </div>
    </div>
</section>

<!-- Scroll to Top Button -->
<button class="scroll-to-top" id="scrollTopBtn" onclick="scrollToTop()">
    <i class="bi bi-arrow-up"></i>
</button>

<script>
// Dynamic Table of Contents Generator
document.addEventListener('DOMContentLoaded', function() {
    const sections = document.querySelectorAll('.legal-section');
    const tocList = document.getElementById('tocList');
    const mobileNav = document.getElementById('mobileNav');
    const progressBar = document.getElementById('tocProgress');
    
    // Generate TOC items
    sections.forEach((section, index) => {
        const id = section.id;
        const title = section.querySelector('h2').textContent;
        const icon = section.querySelector('.legal-section-icon i').className;
        
        // Create sidebar TOC item
        const tocItem = document.createElement('li');
        const tocLink = document.createElement('a');
        tocLink.href = `#${id}`;
        tocLink.innerHTML = `<i class="${icon}"></i> ${title}`;
        tocLink.setAttribute('data-section', id);
        tocItem.appendChild(tocLink);
        tocList.appendChild(tocItem);
        
        // Create mobile nav item
        const mobileItem = document.createElement('li');
        const mobileLink = document.createElement('a');
        mobileLink.href = `#${id}`;
        mobileLink.innerHTML = `<i class="bi bi-chevron-right"></i> ${title}`;
        mobileItem.appendChild(mobileLink);
        mobileNav.appendChild(mobileItem);
    });
    
    // Smooth scroll for TOC links
    document.querySelectorAll('.legal-toc-list a, .legal-quick-nav a').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            if (targetElement) {
                const offsetTop = targetElement.offsetTop - 100;
                window.scrollTo({
                    top: offsetTop,
                    behavior: 'smooth'
                });
                
                // Update URL without scrolling
                history.pushState(null, null, targetId);
            }
        });
    });
    
    // Enhanced Intersection Observer for active section highlighting
    const observerOptions = {
        root: null,
        rootMargin: '-10% 0px -85% 0px',
        threshold: [0, 0.1, 0.2, 0.3, 0.4, 0.5, 0.6, 0.7, 0.8, 0.9, 1]
    };
    
    let activeSection = null;
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && entry.intersectionRatio > 0) {
                activeSection = entry.target.id;
                updateActiveLink(activeSection);
            }
        });
    }, observerOptions);
    
    // Function to update active link
    function updateActiveLink(sectionId) {
        // Remove active class from all links
        document.querySelectorAll('.legal-toc-list a').forEach(link => {
            link.classList.remove('active');
        });
        
        // Add active class to current section link
        const activeLink = document.querySelector(`.legal-toc-list a[data-section="${sectionId}"]`);
        if (activeLink) {
            activeLink.classList.add('active');
            
            // Scroll the TOC list to keep active item visible
            const tocListContainer = document.querySelector('.legal-toc-list');
            const linkTop = activeLink.offsetTop;
            const linkHeight = activeLink.offsetHeight;
            const containerHeight = tocListContainer.clientHeight;
            const scrollTop = tocListContainer.scrollTop;
            
            // Check if active link is outside visible area
            if (linkTop < scrollTop || linkTop + linkHeight > scrollTop + containerHeight) {
                activeLink.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        }
    }
    
    // Observe all sections
    sections.forEach(section => {
        observer.observe(section);
    });
    
    // Fallback scroll detection for more accurate active state
    let scrollTimeout;
    window.addEventListener('scroll', function() {
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            const scrollPosition = window.scrollY + 150;
            
            let currentSection = null;
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionBottom = sectionTop + section.offsetHeight;
                
                if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                    currentSection = section.id;
                }
            });
            
            if (currentSection && currentSection !== activeSection) {
                activeSection = currentSection;
                updateActiveLink(currentSection);
            }
        }, 50);
    });
    
    // Update progress bar on scroll
    window.addEventListener('scroll', function() {
        const windowHeight = window.innerHeight;
        const documentHeight = document.documentElement.scrollHeight;
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        const scrollPercentage = (scrollTop / (documentHeight - windowHeight)) * 100;
        
        if (progressBar) {
            progressBar.style.height = Math.min(scrollPercentage, 100) + '%';
        }
    });
    
    // Set initial active state
    if (window.location.hash) {
        const initialSection = window.location.hash.substring(1);
        updateActiveLink(initialSection);
    } else if (sections.length > 0) {
        updateActiveLink(sections[0].id);
    }
});

// Scroll to Top functionality
window.onscroll = function() {
    const scrollBtn = document.getElementById('scrollTopBtn');
    if (document.body.scrollTop > 300 || document.documentElement.scrollTop > 300) {
        scrollBtn.classList.add('show');
    } else {
        scrollBtn.classList.remove('show');
    }
};

function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>

<?php include '../includes/footer.php'; ?>