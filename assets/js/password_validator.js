const PasswordValidator = {
    validate: function(password) {
        const results = {
            isValid: true,
            errors: [],
            checks: {
                minLength: false,
                hasUpperCase: false,
                hasLowerCase: false,
                hasNumber: false,
                hasSpecialChar: false,
                notCommon: false
            }
        };

        // Check minimum length (8 characters)
        if (password.length >= 8) {
            results.checks.minLength = true;
        } else {
            results.isValid = false;
            results.errors.push('Password must be at least 8 characters long');
        }

        // Check for uppercase letter
        if (/[A-Z]/.test(password)) {
            results.checks.hasUpperCase = true;
        } else {
            results.isValid = false;
            results.errors.push('Password must include at least one uppercase letter');
        }

        // Check for lowercase letter
        if (/[a-z]/.test(password)) {
            results.checks.hasLowerCase = true;
        } else {
            results.isValid = false;
            results.errors.push('Password must include at least one lowercase letter');
        }

        // Check for number
        if (/[0-9]/.test(password)) {
            results.checks.hasNumber = true;
        } else {
            results.isValid = false;
            results.errors.push('Password must include at least one number');
        }

        // Check for special character
        if (/[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)) {
            results.checks.hasSpecialChar = true;
        } else {
            results.isValid = false;
            results.errors.push('Password must include at least one special character');
        }

        // Check for common passwords
        const commonPasswords = [
            'password', '12345678', 'qwerty', 'abc123', 'password123',
            'admin123', 'welcome', 'letmein', 'monkey', '1234567890'
        ];
        
        if (!commonPasswords.includes(password.toLowerCase())) {
            results.checks.notCommon = true;
        } else {
            results.isValid = false;
            results.errors.push('Password is too common. Please choose a stronger password');
        }

        return results;
    },

    updateVisualFeedback: function(password, tipsContainer) {
        if (!tipsContainer) return;

        const validation = this.validate(password);
        const tipItems = tipsContainer.querySelectorAll('li');

        const tipMapping = [
            'minLength',
            'hasUpperCase', // Combined with hasLowerCase
            'hasNumber', // Combined with hasSpecialChar
            'notCommon'
        ];

        tipItems.forEach((item, index) => {
            const checkIcon = item.querySelector('i');
            
            // Determine if requirement is met
            let isMet = false;
            switch(index) {
                case 0: // Minimum 8 characters
                    isMet = validation.checks.minLength;
                    break;
                case 1: // Upper and lowercase
                    isMet = validation.checks.hasUpperCase && validation.checks.hasLowerCase;
                    break;
                case 2: // Numbers and special chars
                    isMet = validation.checks.hasNumber && validation.checks.hasSpecialChar;
                    break;
                case 3: // Not common
                    isMet = validation.checks.notCommon;
                    break;
            }

            // Update styling
            if (password.length === 0) {
                // Reset state when empty
                item.style.color = '';
                checkIcon.className = 'bi bi-check-circle';
            } else if (isMet) {
                item.style.color = '#10b981';
                item.style.fontWeight = '600';
                checkIcon.className = 'bi bi-check-circle-fill';
            } else {
                item.style.color = '#ef4444';
                item.style.fontWeight = '600';
                checkIcon.className = 'bi bi-x-circle-fill';
            }
        });
    },

    getStrength: function(password) {
        let score = 0;
        const validation = this.validate(password);

        // Calculate score based on checks
        if (validation.checks.minLength) score += 20;
        if (validation.checks.hasUpperCase) score += 15;
        if (validation.checks.hasLowerCase) score += 15;
        if (validation.checks.hasNumber) score += 20;
        if (validation.checks.hasSpecialChar) score += 20;
        if (validation.checks.notCommon) score += 10;

        // Bonus for longer passwords
        if (password.length >= 12) score += 10;
        if (password.length >= 16) score += 10;

        // Cap at 100
        score = Math.min(score, 100);

        // Determine strength label
        let label = '';
        let color = '';
        if (score < 40) {
            label = 'Weak';
            color = '#ef4444';
        } else if (score < 70) {
            label = 'Fair';
            color = '#f59e0b';
        } else if (score < 90) {
            label = 'Good';
            color = '#3b82f6';
        } else {
            label = 'Strong';
            color = '#10b981';
        }

        return { score, label, color };
    },

    displayStrength: function(password, container) {
        if (!container) return;

        const strength = this.getStrength(password);
        
        container.innerHTML = `
            <div style="margin-top: 0.5rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.25rem;">
                    <span style="font-size: 0.875rem; font-weight: 600;">Password Strength:</span>
                    <span style="font-size: 0.875rem; font-weight: 700; color: ${strength.color};">${strength.label}</span>
                </div>
                <div style="width: 100%; height: 6px; background: #e5e7eb; border-radius: 3px; overflow: hidden;">
                    <div style="width: ${strength.score}%; height: 100%; background: ${strength.color}; transition: all 0.3s ease;"></div>
                </div>
            </div>
        `;
    }
};

// Export for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PasswordValidator;
}