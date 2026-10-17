</main>
    <footer class="border-t border-gray-200 py-6 text-center text-xs text-gray-400">
        Library System &middot; Web Application Development Final Project
    </footer>
</div>

    <div id="toast-root" class="fixed right-4 top-20 z-50 flex w-80 max-w-[calc(100vw-2rem)] flex-col gap-3"></div>
    <?php if (!empty($GLOBALS['toasts'])): ?>
        <script type="application/json" id="toast-data"><?= json_encode($GLOBALS['toasts'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <?php endif; ?>

    <button type="button" id="back-to-top" aria-label="Back to top"
            class="fixed bottom-6 right-6 z-40 hidden h-12 w-12 items-center justify-center rounded-full bg-orange-500 text-white shadow-lg hover:bg-orange-600">
        <?= icon('arrow-up', 'h-6 w-6') ?>
    </button>

    <script src="<?= asset('js/script.js') ?>"></script>
</body>
</html>