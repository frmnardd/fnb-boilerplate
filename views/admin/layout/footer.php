<?php
declare(strict_types=1);

/**
 * FnB Boilerplate - TailAdmin Layout Footer
 */
?>
                </div>
            </main>
        </div>
    </div>

    <!-- Global Helpers for API Calls with CSRF -->
    <script>
        window.getCsrfToken = function() {
            const meta = document.querySelector('meta[name="csrf-token"]');
            return meta ? meta.getAttribute('content') : '';
        };
    </script>
</body>
</html>
