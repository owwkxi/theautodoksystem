<?php

define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireAnyRole(['admin', 'cashier', 'service_adviser', 'stockman']);

$pageTitle = 'Notes';
$userId = (int)($_SESSION['user_id'] ?? 0);
$ownedNotes = getOwnedUserNotes($userId);
$notes = getPrivateUserNotes($userId);
$noteAccessUsers = getUsersWithNoteAccess();
$shareUsers = getUsersWithNoteAccess($userId);
$shareUserNames = [];
foreach ($noteAccessUsers as $shareUser) {
    $shareUserNames[(int)$shareUser['id']] = $shareUser['name'];
}
$currentUserName = $shareUserNames[$userId] ?? ($_SESSION['full_name'] ?? $_SESSION['username'] ?? 'You');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        validateCSRF();
        $action = $_POST['action'] ?? '';

        if ($action === 'save_note') {
            $noteId = trim((string)($_POST['note_id'] ?? ''));
            $title = trim((string)($_POST['note_title'] ?? ''));
            $content = trim((string)($_POST['note_content'] ?? ''));
            $shareMode = ($_POST['share_mode'] ?? 'private') === 'shared' ? 'shared' : 'private';
            $removeImageUrls = array_values(array_unique(array_filter(
                array_map('strval', (array)($_POST['remove_image_urls'] ?? []))
            )));
            $sharedUserIds = array_values(array_unique(array_filter(
                array_map('intval', (array)($_POST['shared_user_ids'] ?? [])),
                static function ($id) use ($userId) {
                    return $id > 0 && $id !== $userId;
                }
            )));
            $uploadedImageUrls = [];

            if ($shareMode === 'shared') {
                $allowedShareUserIds = array_map('intval', array_column($shareUsers, 'id'));
                if (empty($sharedUserIds) || count(array_diff($sharedUserIds, $allowedShareUserIds)) > 0) {
                    throw new Exception('Please select at least one valid user for sharing.');
                }
            } else {
                $sharedUserIds = [];
            }

            $noteOwnerId = $noteId !== '' ? getPrivateUserNoteOwnerId($noteId, $userId) : $userId;
            if ($noteId !== '' && $noteOwnerId <= 0) {
                throw new Exception('Note not found or you do not have access to edit it.');
            }
            if ($noteId !== '' && $noteOwnerId !== $userId) {
                throw new Exception('Shared notes are view-only. Only the note owner can edit this note.');
            }
            $updatedNotes = getOwnedUserNotes($noteOwnerId);

            if ($title === '' && $content !== '') {
                $title = 'Untitled Note';
            }

            if ($content === '') {
                throw new Exception('Note content is required.');
            }

            $imageUploads = $_FILES['note_images'] ?? null;
            $uploadCount = is_array($imageUploads['name'] ?? null) ? count($imageUploads['name']) : 0;
            for ($imageIndex = 0; $imageIndex < $uploadCount; $imageIndex++) {
                $imageUpload = [
                    'name' => $imageUploads['name'][$imageIndex] ?? '',
                    'tmp_name' => $imageUploads['tmp_name'][$imageIndex] ?? '',
                    'size' => $imageUploads['size'][$imageIndex] ?? 0,
                    'error' => $imageUploads['error'][$imageIndex] ?? UPLOAD_ERR_NO_FILE,
                ];
                if (($imageUpload['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                    continue;
                }
                if (($imageUpload['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
                    throw new Exception('One of the images could not be uploaded.');
                }
                if ((int)$imageUpload['size'] > 5 * 1024 * 1024) {
                    throw new Exception('Each image must be 5 MB or smaller.');
                }

                $imageInfo = @getimagesize($imageUpload['tmp_name']);
                $allowedMimeTypes = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/gif' => 'gif',
                    'image/webp' => 'webp',
                ];
                $imageMimeType = is_array($imageInfo) ? (string)($imageInfo['mime'] ?? '') : '';
                if (!isset($allowedMimeTypes[$imageMimeType])) {
                    throw new Exception('Please upload a JPG, PNG, GIF, or WEBP image.');
                }
                if (!ensureUploadDirectoryWritable()) {
                    throw new Exception('The upload folder is not writable.');
                }

                $imageDirectory = rtrim(UPLOAD_PATH, '/') . '/note_images';
                if (!is_dir($imageDirectory) && !@mkdir($imageDirectory, 0755, true) && !is_dir($imageDirectory)) {
                    throw new Exception('Unable to create the image upload folder.');
                }

                $imageFilename = 'note_' . $userId . '_' . bin2hex(random_bytes(8)) . '.' . $allowedMimeTypes[$imageMimeType];
                $imagePath = $imageDirectory . '/' . $imageFilename;
                if (!@move_uploaded_file($imageUpload['tmp_name'], $imagePath)) {
                    throw new Exception('Unable to save one of the uploaded images.');
                }
                $uploadedImageUrls[] = rtrim(APP_URL, '/') . '/uploads/note_images/' . rawurlencode($imageFilename);
            }

            $now = date('Y-m-d H:i:s');
            $removedImagePaths = [];
            if ($noteId !== '') {
                foreach ($updatedNotes as &$entry) {
                    if (($entry['id'] ?? '') === $noteId) {
                        $existingImageUrls = is_array($entry['image_urls'] ?? null) ? $entry['image_urls'] : [];
                        $keptImageUrls = array_values(array_diff($existingImageUrls, $removeImageUrls));
                        foreach (array_intersect($existingImageUrls, $removeImageUrls) as $removedImageUrl) {
                            $imageName = basename((string)parse_url($removedImageUrl, PHP_URL_PATH));
                            if (preg_match('/^note_[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|gif|webp)$/i', $imageName)) {
                                $removedImagePaths[] = rtrim(UPLOAD_PATH, '/') . '/note_images/' . $imageName;
                            }
                        }
                        $entry['title'] = $title !== '' ? $title : 'Untitled Note';
                        $entry['content'] = $content;
                        $entry['shared_with'] = $sharedUserIds;
                        $entry['image_urls'] = $keptImageUrls;
                        if ($uploadedImageUrls) {
                            $entry['image_urls'] = array_merge($entry['image_urls'] ?? [], $uploadedImageUrls);
                        }
                        $entry['updated_at'] = $now;
                        break;
                    }
                }
                unset($entry);
            } else {
                $updatedNotes[] = [
                    'id' => uniqid('note_', true),
                    'title' => $title !== '' ? $title : 'Untitled Note',
                    'content' => $content,
                    'image_urls' => $uploadedImageUrls,
                    'owner_id' => $noteOwnerId,
                    'shared_with' => $sharedUserIds,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            if (!savePrivateUserNotes($updatedNotes, $noteOwnerId)) {
                throw new Exception('Unable to save note.');
            }
            foreach (array_unique($removedImagePaths) as $removedImagePath) {
                if (is_file($removedImagePath)) {
                    @unlink($removedImagePath);
                }
            }

            $notes = getPrivateUserNotes($userId);
            setMessage($noteId !== '' ? 'Note updated successfully.' : 'Note added successfully.', 'success');
            redirect(routeUrl('settings_notes'));
        }

        if ($action === 'delete_note') {
            $noteId = trim((string)($_POST['note_id'] ?? ''));
            if ($noteId === '') {
                throw new Exception('The note to delete was not selected.');
            }
            $noteOwnerId = getPrivateUserNoteOwnerId($noteId, $userId);
            if ($noteOwnerId !== $userId) {
                throw new Exception('Only the note owner can delete this note.');
            }
            // Reload the owner file so deletion is not based on an older page snapshot.
            $currentOwnedNotes = getOwnedUserNotes($noteOwnerId);
            $updatedNotes = [];
            $deletedNote = null;
            foreach ($currentOwnedNotes as $note) {
                if ((string)($note['id'] ?? '') === $noteId) {
                    $deletedNote = $note;
                    continue;
                }
                $updatedNotes[] = $note;
            }
            if ($deletedNote === null) {
                throw new Exception('Note not found. Please refresh the page and try again.');
            }

            if (!savePrivateUserNotes($updatedNotes, $noteOwnerId)) {
                throw new Exception('Unable to delete note.');
            }
            foreach ((array)($deletedNote['image_urls'] ?? []) as $imageUrl) {
                $imageName = basename((string)parse_url($imageUrl, PHP_URL_PATH));
                if (preg_match('/^note_[A-Za-z0-9_-]+\.(?:jpg|jpeg|png|gif|webp)$/i', $imageName)) {
                    $imagePath = rtrim(UPLOAD_PATH, '/') . '/note_images/' . $imageName;
                    if (is_file($imagePath)) {
                        @unlink($imagePath);
                    }
                }
            }

            $ownedNotes = $updatedNotes;
            $notes = getPrivateUserNotes($userId);
            setMessage('Note deleted successfully.', 'success');
            redirect(routeUrl('settings_notes'));
        }
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }
}

$editingNote = null;
$viewingNote = null;
if (isset($_GET['edit']) && $_GET['edit'] !== '') {
    foreach ($notes as $note) {
        if (($note['id'] ?? '') === $_GET['edit']) {
            $editingNote = $note;
            break;
        }
    }
}
if (isset($_GET['view']) && $_GET['view'] !== '') {
    foreach ($notes as $note) {
        if (($note['id'] ?? '') === $_GET['view']) {
            $viewingNote = $note;
            break;
        }
    }
}

include __DIR__ . '/../partials/header.php';
?>

<style>
    .notes-page {
        min-height: calc(100vh - 160px);
        height: auto;
        overflow: visible;
        padding-bottom: 48px;
    }
    .notes-dashboard {
        display: grid;
        grid-template-columns: 320px minmax(0, 1fr);
        gap: 18px;
        align-items: start;
        height: auto;
        padding-bottom: 24px;
    }
    .note-form-card,
    .notes-list-panel,
    .note-card,
    .note-empty {
        border-radius: 16px !important;
        border: 1px solid #e5e7eb;
        background: #fff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    }
    .note-form-card {
        padding: 16px;
        min-height: 0;
        height: fit-content;
        align-self: start;
        overflow: hidden;
    }
    .note-form-card.is-editing {
        overflow-y: auto;
    }
    .note-form-card.is-editing::-webkit-scrollbar {
        width: 8px;
    }
    .note-form-card.is-editing::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 8px;
    }
    .note-form-card.is-editing::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }
    .note-form-image,
    .note-image {
        display: block;
        width: 100%;
        height: auto;
        max-height: 220px;
        object-fit: contain;
        border: 0;
        margin: 0;
    }
    .note-images {
        column-count: 2;
        column-gap: 12px;
        padding: 0;
        margin: 0 0 8px;
    }
    .note-images > * {
        display: block;
        break-inside: avoid;
        width: 100%;
        margin: 0;
    }
    .note-images .note-image {
        margin: 0;
    }
    .note-card > .note-images {
        text-align: center;
    }
    .note-card > .note-images.single-image-gallery {
        column-count: 1;
        display: flex;
        justify-content: center;
    }
    .note-card > .note-images.dashboard-gallery {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        column-count: unset;
    }
    .note-card > .note-images .note-image-item {
        display: flex;
        justify-content: center;
        text-align: center;
    }
    .note-card > .note-images.single-image-gallery .note-image-item {
        width: 100%;
    }
    .note-card > .note-images.single-image-gallery .note-image-item,
    .modal .note-images.single-image-gallery .note-image-item {
        display: flex;
        justify-content: center;
        text-align: center;
    }
    .note-card > .note-images.single-image-gallery .note-image {
        width: 100%;
    }
    .note-card > .note-images.dashboard-gallery.single-image-gallery {
        display: flex;
        width: 100%;
    }
    .note-card > .note-images.dashboard-gallery.single-image-gallery .note-image-item {
        flex: 1 1 100%;
        width: 100%;
    }
    .note-card > .note-images .note-image {
        display: block;
        width: auto;
        max-width: 100%;
        margin: 0;
    }
    .note-card > .note-images.dashboard-gallery.single-image-gallery .note-image {
        width: 100%;
        max-width: 100%;
    }
    .modal .note-images.view-gallery {
        column-count: 2;
        column-gap: 12px;
        padding: 12px;
        margin: 0 0 8px;
        box-sizing: border-box;
    }
    .modal .note-images.view-gallery.single-image-gallery {
        column-count: 1;
    }
    .modal .note-images.view-gallery .note-image-item {
        display: block;
        width: 100%;
        break-inside: avoid;
        margin: 0 0 12px;
    }
    .modal .note-images.view-gallery .note-image {
        width: 100%;
        max-width: 100%;
    }
    .note-image-item {
        display: block;
    }
    .note-image-remove {
        position: relative;
        display: block;
        margin: 0;
        cursor: pointer;
    }
    .note-image-remove input {
        position: absolute;
        left: 8px;
        bottom: 8px;
        z-index: 2;
        accent-color: #6b7280;
    }
    .note-image-remove span {
        position: absolute;
        left: 28px;
        bottom: 6px;
        padding: 2px 6px;
        border-radius: 4px;
        background: rgba(31, 41, 55, 0.82);
        color: #fff;
        font-size: 11px;
        line-height: 1.3;
    }
    .note-form-image { max-height: 160px; }
    .modal .note-image {
        height: auto;
        width: 100%;
        max-width: 100%;
        max-height: 70vh;
        object-fit: contain;
    }
    .notes-list-panel {
        min-height: 0;
        height: auto;
        padding: 16px;
        overflow: hidden;
    }
    .notes-scroll {
        max-height: calc(100vh - 250px);
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0 4px 48px 0;
        box-sizing: border-box;
    }
    .notes-scroll::-webkit-scrollbar { width: 8px; }
    .notes-scroll::-webkit-scrollbar-track { background: #f3f4f6; border-radius: 8px; }
    .notes-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 8px; }
    .notes-grid {
        column-count: 3;
        column-gap: 14px;
    }
    .note-card {
        padding: 14px 14px 12px;
        display: block;
        break-inside: avoid;
        margin: 0 0 14px;
    }
    .note-card h6 {
        font-size: 0.98rem;
        margin-bottom: 8px;
        line-height: 1.3;
        min-width: 0;
        overflow-wrap: anywhere;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 2;
        overflow: hidden;
    }
    .note-card > .d-flex { min-width: 0; }
    .note-body {
        min-width: 0;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .note-card .note-body {
        color: #374151;
        line-height: 1.5;
        white-space: pre-wrap;
        overflow: hidden;
        font-size: 0.9rem;
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: 4;
    }
    .note-card .note-meta {
        color: #6b7280;
        font-size: 11px;
        margin-top: 12px;
    }
    .note-actions {
        display: flex;
        gap: 4px;
        flex-shrink: 0;
    }
    .note-actions .dropdown-toggle::after { display: none; }
    .note-actions .dropdown-menu { min-width: 120px; }
    .note-actions .btn {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px !important;
    }
    .note-actions .btn i {
        font-size: 14px;
        line-height: 1;
    }
    #sharedUserField .form-check-input {
        border-color: #9ca3af;
        background-color: #fff;
    }
    #sharedUserField .form-check-input:checked {
        border-color: #6b7280;
        background-color: #6b7280;
    }
    .note-empty {
        padding: 22px 18px;
        text-align: center;
        color: #64748b;
        background: #f8fafc;
        border-style: dashed;
    }
    @media (max-width: 991px) {
        .notes-page { height: auto; overflow: visible; }
        .notes-dashboard { grid-template-columns: 1fr; height: auto; }
        .note-form-card,
        .notes-list-panel {
            height: auto;
        }
        .notes-list-panel {
            overflow: visible;
        }
        .notes-scroll { max-height: none; overflow: visible; }
        .notes-grid { column-count: 2; column-gap: 12px; }
        .note-images { column-count: 2; column-gap: 10px; }
    }
    @media (max-width: 575px) {
        .notes-grid { column-count: 1; }
        .note-images { column-count: 1; }
    }
</style>

<div class="container-fluid notes-page">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">Notes</h4>
            <p class="text-muted mb-0 small">Keep notes private or share them with any active user who has Notes access.</p>
        </div>
    </div>

    <div class="notes-dashboard">
        <div class="note-form-card<?php echo $editingNote ? ' is-editing' : ''; ?>">
            <div>
                <h6 class="fw-bold mb-3">
                    <i class="bi bi-<?php echo $viewingNote ? 'eye' : 'pencil-square'; ?> me-2"></i>
                    <?php echo $viewingNote ? 'View Note' : ($editingNote ? 'Edit Note' : 'Add New Note'); ?>
                </h6>

                <?php if ($viewingNote): ?>
                    <h5 class="mb-3"><?php echo escape($viewingNote['title'] ?? 'Untitled Note'); ?></h5>
                    <?php if (!empty($viewingNote['image_urls'])): ?>
                        <div class="note-images view-gallery mb-3">
                            <?php foreach ($viewingNote['image_urls'] as $imageUrl): ?>
                                <div class="note-image-item">
                                    <img src="<?php echo escape($imageUrl); ?>" alt="Note image" class="note-image">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="note-body mb-3"><?php echo nl2br(escape($viewingNote['content'] ?? '')); ?></div>
                    <div class="note-meta mb-3">
                        Updated <?php echo formatDateTime($viewingNote['updated_at'] ?? $viewingNote['created_at'] ?? date('Y-m-d H:i:s')); ?>
                    </div>
                    <a href="<?php echo routeUrl('settings_notes'); ?>" class="btn btn-outline-secondary btn-sm">Close</a>
                <?php else: ?>
                <form method="POST" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="save_note">
                    <input type="hidden" name="note_id" value="<?php echo escape($editingNote['id'] ?? ''); ?>">

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Title</label>
                        <input type="text" class="form-control" name="note_title" value="<?php echo escape($editingNote['title'] ?? ''); ?>" placeholder="Add a title (optional)">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Note</label>
                        <textarea class="form-control" name="note_content" rows="4" placeholder="Write your private note here..." style="resize: vertical; min-height: 90px;"><?php echo escape($editingNote['content'] ?? ''); ?></textarea>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Sharing</label>
                        <select class="form-select form-select-sm" name="share_mode" id="noteShareMode">
                            <option value="private" <?php echo empty($editingNote['shared_with']) ? 'selected' : ''; ?>>Private - only me</option>
                            <option value="shared" <?php echo !empty($editingNote['shared_with']) ? 'selected' : ''; ?>>Shared with another user</option>
                        </select>
                    </div>

                    <div class="mb-2 <?php echo empty($editingNote['shared_with']) ? 'd-none' : ''; ?>" id="sharedUserField">
                        <label class="form-label small fw-semibold">Share with</label>
                        <div class="border rounded p-2 bg-light" role="group" aria-label="Users to share this note with">
                        <?php foreach ($shareUsers as $shareUser): ?>
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    name="shared_user_ids[]"
                                    value="<?php echo (int)$shareUser['id']; ?>"
                                    id="sharedUser-<?php echo (int)$shareUser['id']; ?>"
                                    <?php echo !empty($editingNote['shared_with']) && in_array((int)$shareUser['id'], array_map('intval', $editingNote['shared_with']), true) ? 'checked' : ''; ?>
                                >
                                <label class="form-check-label" for="sharedUser-<?php echo (int)$shareUser['id']; ?>">
                                    <?php echo escape($shareUser['name']); ?>
                                    <span class="text-muted small">(<?php echo escape($shareUser['role']); ?>)</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        </div>
                        <div class="form-text">Check one or more users.</div>
                        <?php if (empty($shareUsers)): ?>
                            <div class="form-text">No other active users currently have Notes access.</div>
                        <?php endif; ?>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold">Image</label>
                        <input type="file" class="form-control form-control-sm" name="note_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple>
                        <div class="form-text">Optional. Add multiple images, up to 5 MB each.</div>
                        <?php if (!empty($editingNote['image_urls'])): ?>
                            <div class="note-images mt-2">
                                <?php foreach ($editingNote['image_urls'] as $imageUrl): ?>
                                    <label class="note-image-remove">
                                        <img src="<?php echo escape($imageUrl); ?>" alt="Note image" class="note-image">
                                        <input type="checkbox" name="remove_image_urls[]" value="<?php echo escape($imageUrl); ?>">
                                        <span>Remove</span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <div class="form-text">Check Remove under any picture you want to delete.</div>
                        <?php endif; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-dark">
                            <i class="bi bi-check2-circle"></i> <?php echo $editingNote ? 'Update Note' : 'Save Note'; ?>
                        </button>
                        <?php if ($editingNote): ?>
                            <a href="<?php echo routeUrl('settings_notes'); ?>" class="btn btn-outline-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <div class="notes-list-panel">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="mb-0 fw-bold"><i class="bi bi-journal-text me-2"></i>Notes</h6>
                <span class="badge bg-dark rounded-pill"><?php echo count($notes); ?></span>
            </div>
            <?php if (empty($notes)): ?>
                <div class="note-empty">
                    <i class="bi bi-journal-text fs-2 d-block mb-2"></i>
                    No notes yet. Add your first note on the left.
                </div>
            <?php else: ?>
                <div class="notes-scroll">
                    <div class="notes-grid">
                        <?php foreach ($notes as $note): ?>
                            <div class="note-card">
                                <?php
                                    $noteOwnerId = (int)($note['owner_id'] ?? $userId);
                                    $sharedNames = [];
                                    foreach ((array)($note['shared_with'] ?? []) as $sharedId) {
                                        $sharedNames[] = $shareUserNames[(int)$sharedId] ?? 'another user';
                                    }
                                ?>
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                    <div class="min-width-0">
                                        <h6 class="mb-1"><?php echo escape($note['title'] ?? 'Untitled Note'); ?></h6>
                                        <div class="small text-muted">
                                            <i class="bi bi-people"></i>
                                            <?php if ($sharedNames): ?>
                                                <?php if ($noteOwnerId === $userId): ?>
                                                    Shared with <?php echo escape(implode(', ', $sharedNames)); ?>
                                                <?php else: ?>
                                                    Shared with you
                                                <?php endif; ?>
                                            <?php else: ?>
                                                Private
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="note-actions">
                                        <div class="dropdown action-dropdown note-actions">
                                            <button type="button" class="btn action-menu-btn dropdown-toggle" title="Note actions" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Note actions">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#noteViewModal-<?php echo md5((string)$note['id']); ?>">
                                                        <i class="bi bi-eye me-2"></i>View
                                                    </button>
                                                </li>
                                                <?php if ((int)($note['owner_id'] ?? $userId) === $userId): ?>
                                                    <li><a href="<?php echo routeUrl('settings_notes', ['edit' => $note['id']]); ?>" class="dropdown-item"><i class="bi bi-pencil me-2"></i>Edit</a></li>
                                                    <li>
                                                        <button type="button" class="dropdown-item text-danger" data-bs-toggle="modal" data-bs-target="#deleteNoteModal" data-note-id="<?php echo escape($note['id']); ?>" data-note-title="<?php echo escape($note['title'] ?? 'Untitled Note'); ?>">
                                                            <i class="bi bi-trash me-2"></i>Delete
                                                        </button>
                                                    </li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!empty($note['image_urls'])): ?>
                                    <?php $dashboardImages = array_slice($note['image_urls'], 0, 2); ?>
                                    <div class="note-images dashboard-gallery<?php echo count($dashboardImages) === 1 ? ' single-image-gallery' : ''; ?>">
                                        <?php foreach ($dashboardImages as $imageUrl): ?>
                                            <div class="note-image-item">
                                                <img src="<?php echo escape($imageUrl); ?>" alt="Note image" class="note-image dashboard-note-image">
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <div class="note-body"><?php echo nl2br(escape($note['content'] ?? '')); ?></div>
                                <div class="note-meta">
                                    Updated <?php echo formatDateTime($note['updated_at'] ?? $note['created_at'] ?? date('Y-m-d H:i:s')); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteNoteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle text-danger me-2"></i>Delete Note
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Are you sure you want to delete this note?</p>
                <div class="small text-muted">
                    Note: <span id="deleteNoteTitle">-</span>
                </div>
                <form id="deleteNoteForm" method="POST" class="d-none">
                    <?php echo csrfField(); ?>
                    <input type="hidden" name="action" value="delete_note">
                    <input type="hidden" name="note_id" id="deleteNoteId" value="">
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="deleteNoteForm" class="btn btn-danger">
                    <i class="bi bi-trash3"></i> Delete Note
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const shareMode = document.getElementById('noteShareMode');
    const sharedUserField = document.getElementById('sharedUserField');
    if (shareMode && sharedUserField) {
        shareMode.addEventListener('change', function () {
            sharedUserField.classList.toggle('d-none', this.value !== 'shared');
        });
    }

    const actionMenus = new WeakMap();
    document.addEventListener('show.bs.dropdown', function (event) {
        const button = event.target;
        const container = button.closest('.note-actions');
        const menu = container ? container.querySelector('.dropdown-menu') : null;
        if (!container || !menu) return;

        const placeholder = document.createComment('note-actions-menu');
        menu.parentNode.insertBefore(placeholder, menu);
        document.body.appendChild(menu);
        actionMenus.set(button, { menu, placeholder });
    });

    document.addEventListener('shown.bs.dropdown', function (event) {
        const state = actionMenus.get(event.target);
        if (!state) return;

        const menu = state.menu;
        const buttonRect = event.target.getBoundingClientRect();
        menu.style.position = 'fixed';
        menu.style.visibility = 'hidden';
        menu.style.display = 'block';
        const menuHeight = menu.scrollHeight;
        const menuWidth = menu.offsetWidth;
        const gap = 6;
        const top = buttonRect.bottom + menuHeight + gap <= window.innerHeight
            ? buttonRect.bottom + gap
            : Math.max(gap, buttonRect.top - menuHeight - gap);
        const left = Math.min(
            Math.max(gap, buttonRect.right - menuWidth),
            window.innerWidth - menuWidth - gap
        );
        menu.style.top = top + 'px';
        menu.style.left = left + 'px';
        menu.style.right = 'auto';
        menu.style.transform = 'none';
        menu.style.visibility = 'visible';
    });

    document.addEventListener('hidden.bs.dropdown', function (event) {
        const state = actionMenus.get(event.target);
        if (!state) return;

        state.placeholder.parentNode.insertBefore(state.menu, state.placeholder);
        state.placeholder.remove();
        state.menu.style.position = '';
        state.menu.style.top = '';
        state.menu.style.left = '';
        state.menu.style.right = '';
        state.menu.style.transform = '';
        state.menu.style.display = '';
        state.menu.style.visibility = '';
        actionMenus.delete(event.target);
    });

    const modalEl = document.getElementById('deleteNoteModal');
    if (!modalEl) return;

    modalEl.addEventListener('show.bs.modal', function (event) {
        const trigger = event.relatedTarget;
        if (!trigger) return;

        const idInput = document.getElementById('deleteNoteId');
        const titleEl = document.getElementById('deleteNoteTitle');
        if (idInput) idInput.value = trigger.getAttribute('data-note-id') || '';
        if (titleEl) titleEl.textContent = trigger.getAttribute('data-note-title') || 'Untitled Note';
    });
})();
</script>

<script>
(function () {
    document.querySelectorAll('.dashboard-gallery, .view-gallery').forEach(function (gallery) {
        const items = Array.from(gallery.querySelectorAll('.note-image-item'));
        const classifyAndArrange = function () {
            items.forEach(function (item) {
                const image = item.querySelector('.note-image');
                if (!image || !image.naturalWidth || !image.naturalHeight) return;
                const ratio = image.naturalWidth / image.naturalHeight;
                item.classList.toggle('is-landscape', ratio > 1.2);
                item.classList.toggle('is-portrait', ratio < 0.8);
            });

            items.sort(function (a, b) {
                const shapeWeight = function (item) {
                    if (item.classList.contains('is-landscape')) return 0;
                    if (item.classList.contains('is-portrait')) return 2;
                    return 1;
                };
                return shapeWeight(a) - shapeWeight(b);
            }).forEach(function (item) {
                gallery.appendChild(item);
            });
        };

        items.forEach(function (item) {
            const image = item.querySelector('.note-image');
            if (image && !image.complete) {
                image.addEventListener('load', classifyAndArrange, { once: true });
            }
        });
        classifyAndArrange();
    });
})();
</script>

<?php foreach ($notes as $note): ?>
    <div class="modal fade" id="noteViewModal-<?php echo md5((string)$note['id']); ?>" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo escape($note['title'] ?? 'Untitled Note'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if (!empty($note['image_urls'])): ?>
                    <div class="note-images view-gallery<?php echo count($note['image_urls']) === 1 ? ' single-image-gallery' : ''; ?> mb-3">
                            <?php foreach ($note['image_urls'] as $imageUrl): ?>
                                <div class="note-image-item">
                                    <img src="<?php echo escape($imageUrl); ?>" alt="Note image" class="note-image">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="note-body"><?php echo nl2br(escape($note['content'] ?? '')); ?></div>
                    <div class="note-meta mt-3">
                        Updated <?php echo formatDateTime($note['updated_at'] ?? $note['created_at'] ?? date('Y-m-d H:i:s')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endforeach; ?>

<?php include __DIR__ . '/../partials/footer.php'; ?>
