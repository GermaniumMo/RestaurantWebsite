<?php
if (!defined('ADMIN_PAGE')) {
    die('Direct access not allowed');
}

require_once __DIR__ . '/../../includes/admin-components.php';

render_admin_document_head($page_title ?? 'Admin');
render_admin_header($page_title ?? 'Admin Panel', $page_subtitle ?? '', $current_page ?? '');
?>
