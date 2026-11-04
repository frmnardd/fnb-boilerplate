<?php
declare(strict_types=1);
// Close main content wrapper opened in header.php
?>
                </div><!-- end max-w-screen-2xl -->
            </main><!-- end main -->
        </div><!-- end flex-1 -->
    </div><!-- end flex h-screen -->

    <script>
    // Global CSRF helper
    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    }

    // Global fetch helper with CSRF
    async function csrfFetch(url, options = {}) {
        const headers = { 'X-Requested-With': 'XMLHttpRequest', ...(options.headers || {}) };
        if (!options.body || options.body instanceof FormData) {
            // For FormData, CSRF is already in body
        }
        return fetch(url, { ...options, headers });
    }

    // Auto-dismiss flash messages after 5 seconds
    document.addEventListener('DOMContentLoaded', () => {
        const flashes = document.querySelectorAll('[data-flash-auto-dismiss]');
        flashes.forEach(el => {
            setTimeout(() => {
                el.style.transition = 'opacity 0.5s';
                el.style.opacity = '0';
                setTimeout(() => el.remove(), 500);
            }, 5000);
        });
    });
    </script>
</body>
</html>
