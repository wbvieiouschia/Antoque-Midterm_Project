<?php if (isLoggedIn()): ?>
    </main>

    <footer class="border-t border-gray-200 bg-white mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col sm:flex-row justify-between items-center gap-2 text-xs text-gray-500">
            <p>&copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars(APP_NAME); ?></p>
            <p>Antoque, Johanna Lorraine</p>
        </div>
    </footer>
<?php else: ?>
    </div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script src="/js/live_search.js"></script>
<script>
    document.querySelectorAll('[data-confirm]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm(btn.getAttribute('data-confirm') || 'Are you sure you want to delete this record?')) {
                e.preventDefault();
            }
        });
    });
</script>
<script>
    (function () {
        var btn = document.getElementById('mobileMenuToggle');
        var menu = document.getElementById('mobileMenu');
        if (btn && menu) {
            btn.addEventListener('click', function () {
                menu.classList.toggle('hidden');
                btn.setAttribute('aria-expanded', String(!menu.classList.contains('hidden')));
            });
        }
    })();
</script>
</body>
</html>
