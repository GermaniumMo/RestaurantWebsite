<?php
if (!defined('ADMIN_PAGE')) {
    die('Direct access not allowed');
}

require_once __DIR__ . '/../../includes/admin-components.php';

render_admin_footer($extra_js ?? '');
?>
