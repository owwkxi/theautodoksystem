<?php
if (!defined('APP_ACCESS')) {
    die('Direct access not permitted');
}
$brandingSettings = function_exists('getSystemBrandingSettings') ? getSystemBrandingSettings() : [];
$systemLogoUrl = $brandingSettings['system_logo_url'] ?? (APP_URL . '/assets/images/logo.png');
$activeShop = function_exists('getActiveShopOption') ? getActiveShopOption() : ['key' => 'autodok_main'];
$activeShopName = $activeShop['name'] ?? APP_NAME;
$themeClass = (($activeShop['key'] ?? '') === 'autodok_prime') ? 'theme-prime' : 'theme-main';
$activeAnnouncements = function_exists('getActiveAnnouncements') ? getActiveAnnouncements() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' — ' . $activeShopName : $activeShopName; ?></title>
    <link rel="icon" type="image/png" href="<?php echo escape($systemLogoUrl); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/assets/css/style.css?v=<?php echo time(); ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        window.APP_URL = <?php echo json_encode(APP_URL); ?>;
    </script>
</head>
<body class="<?php echo escape($themeClass); ?>">
<div class="dashboard-wrapper">

    <!-- Sidebar overlay for mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <div class="main-content">

        <!-- Top bar -->
        <div class="topbar">
            <button class="hamburger-btn" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <div class="topbar-title d-flex align-items-center" style="gap:8px;">
                <span><?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?></span>
            </div>
            <div class="topbar-actions">
                <button type="button" class="announcement-trigger" id="announcementTrigger" title="Announcements" aria-label="Announcements">
                    <i class="bi bi-megaphone-fill"></i>
                </button>
                <div class="bell-wrap" id="bellWrap" title="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    <span class="bell-dot" id="bellDot"></span>
                </div>
                <a href="<?php echo routeUrl('profile'); ?>" class="topbar-user topbar-user-link" title="My Profile">
                    <?php
                    // Show profile photo if staff user has one
                    $profilePhoto = null;
                    if (($_SESSION['user_type'] ?? '') === 'staff' && !empty($_SESSION['user_id'])) {
                        try {
                            $staffRow = Database::getInstance()->fetch(
                                "SELECT profile_photo FROM staff WHERE id = ? LIMIT 1",
                                [$_SESSION['user_id']]
                            );
                            if (!empty($staffRow['profile_photo'])) {
                                $profilePhoto = UPLOAD_URL . $staffRow['profile_photo'];
                            }
                        } catch (Exception $e) {}
                    } elseif (!empty($_SESSION['user_id'])) {
                        try {
                            $adminSettingKey = 'user_profile_photo_admin_' . (int)$_SESSION['user_id'];
                            $adminPhotoRow = Database::getInstance()->fetch(
                                "SELECT setting_value FROM system_settings WHERE setting_key = ? LIMIT 1",
                                [$adminSettingKey]
                            );
                            if (!empty($adminPhotoRow['setting_value'])) {
                                $profilePhoto = UPLOAD_URL . $adminPhotoRow['setting_value'];
                            }
                        } catch (Exception $e) {}
                    }
                    ?>
                    <div class="topbar-avatar" <?php if ($profilePhoto): ?>style="padding:0;overflow:hidden;"<?php endif; ?>>
                        <?php if ($profilePhoto): ?>
                            <img src="<?php echo escape($profilePhoto); ?>"
                                 alt="Profile"
                                 style="width:100%;height:100%;object-fit:cover;border-radius:50%;">
                        <?php else: ?>
                            <?php echo strtoupper(substr($_SESSION['full_name'] ?? 'U', 0, 2)); ?>
                        <?php endif; ?>
                    </div>
                    <span class="topbar-name"><?php echo escape($_SESSION['full_name'] ?? 'User'); ?></span>
                </a>
            </div>
        </div>

        <!-- Page body -->
        <div class="page-body">

            <?php if (function_exists('hasMessage') && hasMessage()):
                $msg = getMessage();
                $type = ($msg['type'] === 'error') ? 'danger' : $msg['type'];
            ?>
            <div id="flashToastMessage"
                 data-message="<?php echo escape($msg['message']); ?>"
                 data-type="<?php echo escape($type); ?>"
                 style="display:none;"></div>
            <?php endif; ?>

            <?php
            // Keep active announcements available in the notification bell at all times,
            // while still showing the modal only once per session.
            $activeAnnouncements = function_exists('getActiveAnnouncements') ? getActiveAnnouncements() : [];
            ?>
            <script>
                window.ACTIVE_ANNOUNCEMENTS = <?php echo json_encode(array_values($activeAnnouncements)); ?>;
            </script>

            <?php
                $announcementSeenByShop = [];
                if (isset($_SESSION['announcement_seen_by_shop']) && is_array($_SESSION['announcement_seen_by_shop'])) {
                    $announcementSeenByShop = $_SESSION['announcement_seen_by_shop'];
                }
                $activeShopAnnouncementKey = normalizeReportShopKey($activeShop['key'] ?? 'default');
                $hasSeenAnnouncementForShop = !empty($announcementSeenByShop[$activeShopAnnouncementKey]);
                $shouldAutoShowAnnouncement = !empty($activeAnnouncements) && !$hasSeenAnnouncementForShop;
            ?>
            <?php if ($shouldAutoShowAnnouncement): ?>
                <?php $_SESSION['announcement_seen_by_shop'][$activeShopAnnouncementKey] = true; ?>
            <?php endif; ?>
            <?php if (!empty($activeAnnouncements)):
                $catColors = ['General'=>'secondary','Update'=>'primary','Reminder'=>'info','Holiday'=>'success','Urgent'=>'danger','Maintenance'=>'warning'];
            ?>
            <div class="modal fade" id="announcementModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow">
                        <div class="modal-header" style="background:#f8f9fa;border-bottom:1px solid #eee;">
                            <h5 class="modal-title" style="font-weight:600;">Announcements</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="max-height:500px;overflow-y:auto;">
                                        <?php foreach ($activeAnnouncements as $idx => $ann): ?>
                                        <?php if ($idx > 0): ?><hr class="my-4"><?php endif; ?>
                                        <div id="announcement-<?php echo escape($ann['id'] ?? $idx); ?>" data-ann-id="<?php echo escape($ann['id'] ?? $idx); ?>">
                                            <span class="badge bg-<?php echo $catColors[$ann['category'] ?? 'General'] ?? 'secondary'; ?>" style="font-size:11px;letter-spacing:.4px;text-transform:uppercase;margin-bottom:8px;display:inline-block;"><?php echo escape($ann['category'] ?? 'General'); ?></span>
                                            <h1 style="font-size:1.6rem;font-weight:700;color:#111;line-height:1.2;margin-bottom:10px;"><?php echo escape($ann['title'] ?? 'Announcement'); ?></h1>
                                            <div style="word-wrap:break-word;overflow-wrap:break-word;font-size:13px;line-height:1.6;color:#333;"><?php echo $ann['message'] ?? ''; ?></div>
                                        </div>
                                        <?php endforeach; ?>
                        </div>
                        <div class="modal-footer py-2">
                            <button type="button" class="btn btn-dark btn-sm" data-bs-dismiss="modal">Got it</button>
                        </div>
                    </div>
                </div>
            </div>
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var annModal = document.getElementById('announcementModal');
                var annTrigger = document.getElementById('announcementTrigger');
                var shouldAutoShow = <?php echo $shouldAutoShowAnnouncement ? 'true' : 'false'; ?>;

                if (annModal && shouldAutoShow) {
                    bootstrap.Modal.getOrCreateInstance(annModal).show();
                }

                if (annTrigger && annModal) {
                    annTrigger.addEventListener('click', function() {
                        bootstrap.Modal.getOrCreateInstance(annModal).show();
                    });
                }
            });
            </script>
            <?php endif; ?>
