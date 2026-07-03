// Client-side Form Validation
document.addEventListener('DOMContentLoaded', function() {
    
    // Helpers
    function showError(input, message) {
        if (!input) return;
        input.classList.remove('is-valid');
        input.classList.add('is-invalid');
        
        let errorEl = input.parentElement.querySelector('.form-error');
        if (!errorEl) {
            errorEl = document.createElement('div');
            errorEl.className = 'form-error';
            input.parentElement.appendChild(errorEl);
        }
        errorEl.textContent = message;
        errorEl.style.display = 'block';
    }

    function showSuccess(input) {
        if (!input) return;
        input.classList.remove('is-invalid');
        input.classList.add('is-valid');
        
        const errorEl = input.parentElement.querySelector('.form-error');
        if (errorEl) {
            errorEl.style.display = 'none';
        }
    }

    function validateEmail(email) {
        const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(String(email).toLowerCase());
    }

    // 1. REGISTRATION FORM VALIDATION
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        const nameInput = document.getElementById('nama');
        const emailInput = document.getElementById('email');
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('confirm_password');
        const telpInput = document.getElementById('no_telp');

        // Real-time validations
        nameInput.addEventListener('input', () => {
            if (nameInput.value.trim() === '') {
                showError(nameInput, 'Nama lengkap tidak boleh kosong');
            } else {
                showSuccess(nameInput);
            }
        });

        emailInput.addEventListener('input', () => {
            if (emailInput.value.trim() === '') {
                showError(emailInput, 'Email tidak boleh kosong');
            } else if (!validateEmail(emailInput.value.trim())) {
                showError(emailInput, 'Format email tidak valid');
            } else {
                showSuccess(emailInput);
            }
        });

        passwordInput.addEventListener('input', () => {
            if (passwordInput.value.length < 6) {
                showError(passwordInput, 'Password minimal terdiri dari 6 karakter');
            } else {
                showSuccess(passwordInput);
            }
            // re-validate confirm password if it has value
            if (confirmPasswordInput.value !== '') {
                confirmPasswordInput.dispatchEvent(new Event('input'));
            }
        });

        confirmPasswordInput.addEventListener('input', () => {
            if (confirmPasswordInput.value !== passwordInput.value) {
                showError(confirmPasswordInput, 'Konfirmasi password tidak cocok');
            } else {
                showSuccess(confirmPasswordInput);
            }
        });

        if (telpInput) {
            telpInput.addEventListener('input', () => {
                const val = telpInput.value.trim();
                const phoneRe = /^[0-9+-\s]{8,15}$/;
                if (val !== '' && !phoneRe.test(val)) {
                    showError(telpInput, 'Nomor telepon tidak valid (8-15 digit angka)');
                } else {
                    showSuccess(telpInput);
                }
            });
        }

        registerForm.addEventListener('submit', function(e) {
            // Trigger all input events to display errors
            nameInput.dispatchEvent(new Event('input'));
            emailInput.dispatchEvent(new Event('input'));
            passwordInput.dispatchEvent(new Event('input'));
            confirmPasswordInput.dispatchEvent(new Event('input'));
            if (telpInput) telpInput.dispatchEvent(new Event('input'));

            const hasErrors = registerForm.querySelectorAll('.is-invalid').length > 0;
            if (hasErrors) {
                e.preventDefault();
                window.showToast('Harap perbaiki kesalahan input sebelum mendaftar!', 'error');
            }
        });
    }

    // 2. DONATION FORM VALIDATION
    const donationForm = document.getElementById('donation-form');
    if (donationForm) {
        const titleInput = document.getElementById('judul_buku');
        const authorInput = document.getElementById('penulis');
        const categoryInput = document.getElementById('kategori');
        const quantityInput = document.getElementById('jumlah');
        const fileInput = document.getElementById('foto');

        titleInput.addEventListener('input', () => {
            if (titleInput.value.trim() === '') {
                showError(titleInput, 'Judul buku wajib diisi');
            } else {
                showSuccess(titleInput);
            }
        });

        authorInput.addEventListener('input', () => {
            if (authorInput.value.trim() === '') {
                showError(authorInput, 'Nama penulis wajib diisi');
            } else {
                showSuccess(authorInput);
            }
        });

        categoryInput.addEventListener('change', () => {
            if (categoryInput.value === '') {
                showError(categoryInput, 'Pilih kategori buku');
            } else {
                showSuccess(categoryInput);
            }
        });

        quantityInput.addEventListener('input', () => {
            const val = parseInt(quantityInput.value, 10);
            if (isNaN(val) || val <= 0) {
                showError(quantityInput, 'Jumlah buku minimal adalah 1');
            } else {
                showSuccess(quantityInput);
            }
        });

        // Validation for image file in donation submit
        donationForm.addEventListener('submit', function(e) {
            titleInput.dispatchEvent(new Event('input'));
            authorInput.dispatchEvent(new Event('input'));
            categoryInput.dispatchEvent(new Event('change'));
            quantityInput.dispatchEvent(new Event('input'));

            // Check if photo is uploaded (only on create, not update)
            if (fileInput && fileInput.files.length === 0) {
                const wrapper = fileInput.parentElement;
                showError(fileInput, 'Unggah foto buku wajib dilakukan');
                wrapper.style.borderColor = 'var(--danger)';
            } else if (fileInput) {
                showSuccess(fileInput);
                fileInput.parentElement.style.borderColor = 'var(--border-color)';
            }

            const hasErrors = donationForm.querySelectorAll('.is-invalid').length > 0;
            if (hasErrors) {
                e.preventDefault();
                window.showToast('Harap isi semua kolom formulir dengan benar!', 'error');
            }
        });
    }

    // 3. PROFILE FORM VALIDATION
    const profileForm = document.getElementById('profile-form');
    if (profileForm) {
        const nameInput = document.getElementById('nama');
        const telpInput = document.getElementById('no_telp');

        nameInput.addEventListener('input', () => {
            if (nameInput.value.trim() === '') {
                showError(nameInput, 'Nama tidak boleh kosong');
            } else {
                showSuccess(nameInput);
            }
        });

        if (telpInput) {
            telpInput.addEventListener('input', () => {
                const val = telpInput.value.trim();
                const phoneRe = /^[0-9+-\s]{8,15}$/;
                if (val !== '' && !phoneRe.test(val)) {
                    showError(telpInput, 'Nomor telepon tidak valid');
                } else {
                    showSuccess(telpInput);
                }
            });
        }

        profileForm.addEventListener('submit', function(e) {
            nameInput.dispatchEvent(new Event('input'));
            if (telpInput) telpInput.dispatchEvent(new Event('input'));

            const hasErrors = profileForm.querySelectorAll('.is-invalid').length > 0;
            if (hasErrors) {
                e.preventDefault();
                window.showToast('Perbaiki data profil sebelum menyimpan!', 'error');
            }
        });
    }
});
