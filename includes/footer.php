<?php
$current_page = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Determine if we're in a subdirectory and which one
$in_subdirectory = in_array($current_page, ['login.php', 'register.php', 'books.php', 'book_details.php', 'order.php', 'competition.php', 'upload_essay.php', 'profile.php', 'my_orders.php', 'my_submissions.php']) ? '../' : '';

// Check if we're in the legal directory
$in_legal_dir = ($current_dir === 'legal');

// Set paths based on current directory
$legal_path = $in_legal_dir ? '' : ($in_subdirectory ? '../legal/' : 'legal/');
$admin_path = $in_legal_dir ? '../admin/' : ($in_subdirectory ? '../admin/' : 'admin/');
?>

    <footer class="footer">
        <div class="container">
            <div class="row py-4">
                <div class="col-lg-4 col-md-6 mb-4 mb-lg-0">
                    <h5><i class="bi bi-book-half me-2"></i>Online E-Book System</h5>
                    <p class="mb-3">Your gateway to digital learning and creative writing. Access thousands of e-books and participate in exciting competitions.</p>
                    <div class="social-links">
                        <a href="#" title="Facebook" aria-label="Facebook">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" title="Twitter" aria-label="Twitter">
                            <i class="bi bi-twitter"></i>
                        </a>
                        <a href="#" title="Instagram" aria-label="Instagram">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" title="LinkedIn" aria-label="LinkedIn">
                            <i class="bi bi-linkedin"></i>
                        </a>
                    </div>
                </div>
                
                <div class="col-lg-2 col-md-6 mb-4 mb-lg-0">
                    <h6>Quick Links</h6>
                    <ul>
                        <li><a href="<?php echo $in_subdirectory; ?>index.php">Home</a></li>
                        <li><a href="<?php echo $in_subdirectory; ?>about.php">About Us</a></li>
                        <li><a href="<?php echo $in_subdirectory; ?>user/books.php">Browse Books</a></li>
                        <li><a href="<?php echo $in_subdirectory; ?>user/competition.php">Competitions</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6>Support</h6>
                    <ul>
                        <li><a href="<?php echo $in_subdirectory; ?>contact.php">Contact</a></li>
                        <li><a href="<?php echo $legal_path; ?>privacy.php">Privacy Policy</a></li>
                        <li><a href="<?php echo $legal_path; ?>terms.php">Terms of Service</a></li>
                        <li><a href="<?php echo $admin_path; ?>login.php">Admin Portal</a></li>
                    </ul>
                </div>
                
                <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
                    <h6>Contact Info</h6>
                    <ul>
                        <li><i class="bi bi-geo-alt me-2"></i>Karachi, Pakistan</li>
                        <li><i class="bi bi-envelope me-2"></i>info@ebooksystem.com</li>
                        <li><i class="bi bi-phone me-2"></i>+92 300 1234567</li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="text-center">
            <div class="container">
                <p>
                    &copy; <?php echo date('Y'); ?> Online E-Book System. All Rights Reserved. | 
                    Developed by <span class="fw-bold">Zayan</span> | <span class="text-primary"><a class="text-primary" href="https://primecreators.co">Prime Creators</a></span>
                </p>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        const alerts = document.querySelectorAll('.alert-danger');
        alerts.forEach(alert => {
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }, 5000);
        });

        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    </script>
    <script src="../assets/js/admin_sidebar.js"></script>
</body>
</html>