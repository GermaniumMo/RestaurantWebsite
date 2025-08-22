<?php
// Shared footer for admin pages
if (!defined('ADMIN_PAGE')) {
    die('Direct access not allowed');
}
?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    
    <?php if (isset($extra_js)): ?>
        <?= $extra_js ?>
    <?php endif; ?>
</body>
</html>
