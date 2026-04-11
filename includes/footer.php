        </main>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Sidebar Toggle
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');

            if (sidebar && overlay) {
                sidebar.classList.toggle('-translate-x-full');
                overlay.classList.toggle('hidden');
            }
        }

        // Mobile menu button
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', toggleSidebar);
        }

        // Close sidebar on window resize if screen becomes large
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 1024) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('overlay');

                if (sidebar) {
                    sidebar.classList.remove('-translate-x-full');
                }
                if (overlay) {
                    overlay.classList.add('hidden');
                }
            }
        });

        // Confirm delete action
        function confirmDelete(message) {
            return Swal.fire({
                title: 'Apakah Anda yakin?',
                text: message || 'Data akan dihapus permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                return result.isConfirmed;
            });
        }

        // Show success message
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                timer: 3000,
                showConfirmButton: false
            });
        }

        // Show error message
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message,
                confirmButtonColor: '#2563eb'
            });
        }

        // Show loading
        function showLoading(message = 'Memproses...') {
            Swal.fire({
                title: message,
                html: '<div class="spinner mx-auto"></div>',
                showConfirmButton: false,
                allowOutsideClick: false
            });
        }

        // Close loading
        function closeLoading() {
            Swal.close();
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('[role="alert"]');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Format currency input
        function formatCurrency(input) {
            let value = input.value.replace(/[^\d]/g, '');
            input.value = new Intl.NumberFormat('id-ID').format(value);
        }

        // Copy to clipboard
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(() => {
                showSuccess('Teks berhasil disalin!');
            });
        }

        // Initialize tooltips
        document.addEventListener('DOMContentLoaded', function() {
            // Add smooth scroll behavior
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    const href = this.getAttribute('href');
                    if (href !== '#') {
                        e.preventDefault();
                        const target = document.querySelector(href);
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    }
                });
            });

            // Add fade-in animation to cards
            const cards = document.querySelectorAll('.card-hover');
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('fade-in');
                    }
                });
            }, { threshold: 0.1 });

            cards.forEach(card => observer.observe(card));
        });

        // Print function
        function printElement(elementId) {
            const element = document.getElementById(elementId);
            if (element) {
                const originalContents = document.body.innerHTML;
                const printContents = element.innerHTML;

                document.body.innerHTML = printContents;
                window.print();
                document.body.innerHTML = originalContents;
                location.reload();
            }
        }
    </script>
</body>
</html>
