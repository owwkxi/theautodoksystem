<?php
define('APP_ACCESS', true);
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/Database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/session.php';
require_once __DIR__ . '/../../includes/security.php';

requireLogin();
requireAnyRole(['admin', 'cashier']);

$pageTitle = 'Announcement Settings';
$categories = ['General', 'Update', 'Reminder', 'Holiday', 'Urgent', 'Maintenance'];
$shopOptions = function_exists('getShopOptions') ? getShopOptions() : [];
$activeShop = function_exists('getActiveShopOption') ? getActiveShopOption() : ['key' => 'default', 'name' => APP_NAME];
$currentAnnouncementShopKey = normalizeReportShopKey($activeShop['key'] ?? 'default');
$currentAnnouncementShopName = trim((string)($activeShop['name'] ?? APP_NAME));
$audiences = ['everyone' => 'Everyone', 'branch' => $currentAnnouncementShopName];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save_announcement') {
    try {
        validateCSRF();
        $all    = getAnnouncements();
        $editId = trim((string)($_POST['edit_id'] ?? ''));
        $announcementAudience = normalizeAnnouncementAudience($_POST['announcement_audience'] ?? 'everyone');

        $annData = [
            'id'         => $editId ?: uniqid('ann_', true),
            'category'   => trim((string)($_POST['announcement_category'] ?? 'General')),
            'title'      => trim((string)($_POST['announcement_title'] ?? '')),
            'message'    => $_POST['announcement_message'] ?? '',
            'enabled'    => !empty($_POST['announcement_enabled']),
            'audience'   => $announcementAudience,
            'shop_key'   => $announcementAudience === 'branch' ? $currentAnnouncementShopKey : '',
            'updated_at' => date('Y-m-d H:i:s'),
            'updated_by' => $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'Admin',
        ];
        if ($editId) {
            foreach ($all as &$a) {
                if (($a['id'] ?? '') === $editId) { $a = $annData; break; }
            }
            unset($a);
        } else {
            $all[] = $annData;
        }
        if (!saveAnnouncements($all)) throw new Exception('Failed to save announcement.');
        setMessage('Announcement saved successfully.', 'success');
        redirect(routeUrl('settings_announcement'));
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete_announcement') {
    try {
        validateCSRF();
        $deleteId = trim((string)($_POST['delete_id'] ?? ''));
        $all      = getAnnouncements();
        $deletedAnnouncement = null;
        foreach ($all as $entry) {
            if ((string)($entry['id'] ?? '') === $deleteId) {
                $deletedAnnouncement = $entry;
                break;
            }
        }
        if (!$deletedAnnouncement) {
            throw new Exception('Announcement not found.');
        }
        $archivedAnnouncementId = archiveDeletedRecord('announcement', $deletedAnnouncement, ['source' => 'json']);
        if (!$archivedAnnouncementId) {
            throw new Exception('Failed to archive announcement before deletion.');
        }
        $all      = array_values(array_filter($all, function ($a) use ($deleteId) {
            return ($a['id'] ?? '') !== $deleteId;
        }));
        if (!saveAnnouncements($all)) {
            deleteArchivedRecordById($archivedAnnouncementId);
            throw new Exception('Failed to delete announcement.');
        }
        setMessage('Announcement deleted.', 'success');
        redirect(routeUrl('settings_announcement'));
    } catch (Exception $e) {
        setMessage('Error: ' . $e->getMessage(), 'error');
    }
}

$announcements = array_values(array_filter(getAnnouncements(), function ($ann) use ($currentAnnouncementShopKey) {
    return isAnnouncementVisibleToShop($ann, $currentAnnouncementShopKey);
}));
$catColors     = ['General'=>'secondary','Update'=>'primary','Reminder'=>'info','Holiday'=>'success','Urgent'=>'danger','Maintenance'=>'warning'];
$shopNameMap = [];
foreach ($shopOptions as $shopKey => $shopOption) {
    $shopNameMap[normalizeReportShopKey((string)$shopKey)] = (string)($shopOption['name'] ?? $shopKey);
}

include __DIR__ . '/../partials/header.php';
?>

<style>
.ann-toolbar{display:flex;flex-wrap:wrap;align-items:center;gap:3px;padding:6px 8px;background:#f8f9fa;border:1px solid #dee2e6;border-bottom:none;border-radius:6px 6px 0 0;user-select:none;}
.ann-toolbar button{display:inline-flex;align-items:center;justify-content:center;min-width:28px;height:28px;padding:0 7px;background:#fff;border:1px solid #dee2e6;border-radius:4px;font-size:12px;cursor:pointer;line-height:1;transition:background .12s;white-space:nowrap;}
.ann-toolbar button:hover{background:#e9ecef;border-color:#adb5bd;}
.ann-toolbar button.active{background:#d0d7de;border-color:#adb5bd;}
.ann-toolbar select{height:28px;border:1px solid #dee2e6;border-radius:4px;padding:0 6px;font-size:12px;cursor:pointer;background:#fff;}
.ann-toolbar .tb-sep{width:1px;height:22px;background:#dee2e6;margin:0 3px;flex-shrink:0;}
.ann-toolbar .color-btn{width:28px;height:28px;padding:2px;border:1px solid #dee2e6;border-radius:4px;cursor:pointer;background:#fff;}
#annEditor{min-height:220px;border:1px solid #dee2e6;border-radius:0 0 6px 6px;padding:12px 14px;font-size:13px;line-height:1.65;outline:none;background:#fff;overflow-y:auto;word-break:break-word;}
#annEditor:focus{border-color:#86b7fe;box-shadow:0 0 0 .2rem rgba(13,110,253,.1);}
#annEditor h1{font-size:1.75rem;font-weight:700;margin-bottom:.4rem;}
#annEditor h2{font-size:1.35rem;font-weight:600;margin-bottom:.35rem;}
#annEditor h3{font-size:1.1rem;font-weight:600;margin-bottom:.3rem;}
#annEditor table{border-collapse:collapse;width:100%;margin:8px 0;}
#annEditor table td,#annEditor table th{border:1px solid #ccc;padding:5px 8px;min-width:50px;}
#annEditor table th{background:#f0f0f0;font-weight:600;}
#annEditor a{color:#0d6efd;}
#annEditor img{max-width:100%;height:auto;border-radius:3px;}
.ann-title-preview{font-size:1rem;font-weight:400;color:#212529;line-height:1.3;}
/* Gray/dark toggle switch */
#annEnabled { background-color: #6c757d !important; border-color: #6c757d !important; }
#annEnabled:checked { background-color: #212529 !important; border-color: #212529 !important; }
#annEnabled:focus { box-shadow: 0 0 0 0.2rem rgba(108,117,125,0.25) !important; }
/* Gray checkbox for table header */
#tableHasHeader:checked { background-color: #6c757d !important; border-color: #6c757d !important; }
#tableHasHeader:focus { box-shadow: 0 0 0 0.2rem rgba(108,117,125,0.25) !important; }
</style>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">Announcement Settings</h4>
            <p class="text-muted mb-0 small">Manage announcements shown to users after login.</p>
        </div>
        <a href="<?php echo routeUrl('settings'); ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back to Settings
        </a>
    </div>

    <div class="card mb-4 shadow-sm">
        <div class="card-body">
            <h6 class="mb-3" id="annFormTitle"><i class="bi bi-megaphone me-1"></i>Add Announcement</h6>
            <form method="POST" id="annForm" onsubmit="return syncEditor()">
                <?php echo csrfField(); ?>
                <input type="hidden" name="action" value="save_announcement">
                <input type="hidden" name="edit_id" id="annEditId" value="">
                <input type="hidden" name="announcement_message" id="annMessage" value="">
                <div class="row g-3">
                    <div class="col-md-2">
                        <label class="form-label fw-semibold small">Category</label>
                        <select class="form-select form-select-sm" name="announcement_category" id="annCategory">
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo escape($cat); ?>"><?php echo escape($cat); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Title</label>
                        <input type="text" class="form-control form-control-sm" name="announcement_title" id="annTitle" placeholder="e.g. System Update Notice" required>
                        <div id="annTitlePreviewWrap" style="display:none;margin-top:6px;padding:6px 10px;background:#f8f9fa;border-radius:5px;">
                            <div class="ann-title-preview mt-1" id="annTitlePreview"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Audience</label>
                        <select class="form-select form-select-sm" name="announcement_audience" id="annAudience">
                            <?php foreach ($audiences as $audienceValue => $audienceLabel): ?>
                            <option value="<?php echo escape($audienceValue); ?>" <?php echo $audienceValue === 'everyone' ? 'selected' : ''; ?>>
                                <?php echo escape($audienceLabel); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-semibold small">Status</label>
                        <div class="form-check form-switch mt-1">
                            <input class="form-check-input" type="checkbox" name="announcement_enabled" id="annEnabled" value="1" checked>
                            <label class="form-check-label small" for="annEnabled">Enabled</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-semibold small">Message</label>
                        <div class="ann-toolbar">
                            <select id="tbBlockFmt" title="Block format" onchange="execFmtBlock(this.value);this.value='';">
                                <option value="">Paragraph</option>
                                <option value="h1">H1</option>
                                <option value="h2">H2</option>
                                <option value="h3">H3</option>
                                <option value="p">Normal</option>
                            </select>
                            <div class="tb-sep"></div>
                            <button type="button" id="tbBold"      onclick="execCmd('bold')"          title="Bold"><b>B</b></button>
                            <button type="button" id="tbItalic"    onclick="execCmd('italic')"        title="Italic"><i>I</i></button>
                            <button type="button" id="tbUnderline" onclick="execCmd('underline')"     title="Underline"><u>U</u></button>
                            <button type="button" id="tbStrike"    onclick="execCmd('strikeThrough')" title="Strikethrough"><s>S</s></button>
                            <div class="tb-sep"></div>
                            <label title="Text color" style="cursor:pointer;display:flex;align-items:center;gap:3px;font-size:12px;">
                                <input type="color" class="color-btn" id="tbForeColor" value="#000000" onchange="execCmd('foreColor',this.value)" title="Text color">
                                <span style="font-size:11px;">A</span>
                            </label>
                            <div class="tb-sep"></div>
                            <button type="button" onclick="execCmd('insertUnorderedList')" title="Bullet list"><i class="bi bi-list-ul"></i></button>
                            <button type="button" onclick="execCmd('insertOrderedList')"   title="Numbered list"><i class="bi bi-list-ol"></i></button>
                            <div class="tb-sep"></div>
                            <button type="button" onclick="execCmd('justifyLeft')"   title="Align left"><i class="bi bi-text-left"></i></button>
                            <button type="button" onclick="execCmd('justifyCenter')" title="Align center"><i class="bi bi-text-center"></i></button>
                            <button type="button" onclick="execCmd('justifyRight')"  title="Align right"><i class="bi bi-text-right"></i></button>
                            <div class="tb-sep"></div>
                            <button type="button" onclick="openTableModal()" title="Insert table"><i class="bi bi-table"></i> Table</button>
                            <button type="button" onclick="openLinkModal()"  title="Insert link"><i class="bi bi-link-45deg"></i> Link</button>
                            <button type="button" onclick="openImageModal()" title="Insert image"><i class="bi bi-image"></i> Image</button>
                            <div class="tb-sep"></div>
                            <button type="button" onclick="clearFormatting()" title="Clear formatting"><i class="bi bi-eraser"></i> Clear</button>
                        </div>
                        <div id="annEditor" contenteditable="true" spellcheck="true"></div>
                    </div>
                </div>
                <div class="mt-3 d-flex gap-2 flex-wrap">
                    <button type="submit" class="btn btn-dark btn-sm"><i class="bi bi-save"></i> Save Announcement</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="annCancelEdit" style="display:none;" onclick="resetAnnForm()"><i class="bi bi-x-circle"></i> Cancel Edit</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (!empty($announcements)): ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <h6 class="mb-3"><i class="bi bi-list-check me-1"></i>All Announcements <span class="badge bg-secondary ms-1"><?php echo count($announcements); ?></span></h6>
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size:13px;">
                    <thead class="table-light">
                        <tr><th>Category</th><th>Title</th><th>Scope</th><th>Preview</th><th class="text-center">Status</th><th>Updated</th><th class="text-end">Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($announcements as $ann): ?>
                        <tr>
                            <td><span class="badge bg-<?php echo $catColors[$ann['category'] ?? 'General'] ?? 'secondary'; ?>"><?php echo escape($ann['category'] ?? 'General'); ?></span></td>
                            <td><span class="ann-title-preview"><?php echo escape($ann['title'] ?? ''); ?></span></td>
                            <td>
                                <?php
                                    $annAudience = normalizeAnnouncementAudience($ann['audience'] ?? 'everyone');
                                    $annShopKey = normalizeReportShopKey($ann['shop_key'] ?? '');
                                ?>
                                <?php if ($annAudience === 'everyone'): ?>
                                    <span class="badge bg-dark-subtle text-dark border">Everyone</span>
                                <?php else: ?>
                                    <span class="badge bg-info-subtle text-dark border">
                                        <?php echo escape($shopNameMap[$annShopKey] ?? ucfirst(str_replace('_', ' ', $annShopKey))); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td><span class="text-muted" style="font-size:11px;"><?php echo mb_strimwidth(strip_tags($ann['message'] ?? ''), 0, 70, '…'); ?></span></td>
                            <td class="text-center"><span class="badge bg-<?php echo !empty($ann['enabled']) ? 'success' : 'secondary'; ?>"><?php echo !empty($ann['enabled']) ? 'Active' : 'Disabled'; ?></span></td>
                            <td class="text-muted" style="font-size:11px;"><?php echo !empty($ann['updated_at']) ? date('M d, g:i A', strtotime($ann['updated_at'])) : '—'; ?></td>
                            <td class="text-end" style="white-space:nowrap;">
                                <button class="btn btn-sm btn-outline-dark" onclick="editAnn(<?php echo escape(json_encode($ann)); ?>)" title="Edit"><i class="bi bi-pencil"></i></button>
                                <form method="POST" class="d-inline" onsubmit="return confirmAnnDelete(this);">
                                    <?php echo csrfField(); ?>
                                    <input type="hidden" name="action" value="delete_announcement">
                                    <input type="hidden" name="delete_id" value="<?php echo escape($ann['id'] ?? ''); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- TABLE MODAL -->
<div class="modal fade" id="annTableModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 bg-light"><h6 class="modal-title mb-0"><i class="bi bi-table me-1"></i>Insert Table</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label form-label-sm fw-semibold">Rows</label><input type="number" class="form-control form-control-sm" id="tableRows" value="3" min="1" max="20"></div>
                <div class="mb-3"><label class="form-label form-label-sm fw-semibold">Columns</label><input type="number" class="form-control form-control-sm" id="tableCols" value="3" min="1" max="10"></div>
                <div class="form-check mb-0"><input class="form-check-input" type="checkbox" id="tableHasHeader" checked><label class="form-check-label small" for="tableHasHeader">Include header row</label></div>
            </div>
            <div class="modal-footer py-2"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-dark btn-sm" onclick="doInsertTable()"><i class="bi bi-table me-1"></i>Insert</button></div>
        </div>
    </div>
</div>

<!-- LINK MODAL -->
<div class="modal fade" id="annLinkModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 bg-light"><h6 class="modal-title mb-0"><i class="bi bi-link-45deg me-1"></i>Insert Link</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label form-label-sm fw-semibold">URL <span class="text-danger">*</span></label><input type="url" class="form-control form-control-sm" id="linkUrl" placeholder="https://"></div>
                <div class="mb-0"><label class="form-label form-label-sm fw-semibold">Display text <span class="text-muted fw-normal">(optional)</span></label><input type="text" class="form-control form-control-sm" id="linkText" placeholder="Leave blank to use the URL"></div>
            </div>
            <div class="modal-footer py-2"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-dark btn-sm" onclick="doInsertLink()"><i class="bi bi-link-45deg me-1"></i>Insert</button></div>
        </div>
    </div>
</div>

<!-- IMAGE MODAL -->
<div class="modal fade" id="annImageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow">
            <div class="modal-header py-2 bg-light"><h6 class="modal-title mb-0"><i class="bi bi-image me-1"></i>Insert Image</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="mb-3"><label class="form-label form-label-sm fw-semibold">Image URL <span class="text-danger">*</span></label><input type="url" class="form-control form-control-sm" id="imageUrl" placeholder="https://…"></div>
                <div class="mb-3"><label class="form-label form-label-sm fw-semibold">Alt text <span class="text-muted fw-normal">(optional)</span></label><input type="text" class="form-control form-control-sm" id="imageAlt" placeholder="Describe the image"></div>
                <div id="imgPreviewWrap" style="display:none;"><img id="imgPreview" src="" alt="" style="max-width:100%;max-height:120px;object-fit:contain;border:1px solid #dee2e6;border-radius:4px;display:block;"></div>
            </div>
            <div class="modal-footer py-2"><button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button><button type="button" class="btn btn-dark btn-sm" onclick="doInsertImage()"><i class="bi bi-image me-1"></i>Insert</button></div>
        </div>
    </div>
</div>

<script>
const editor = document.getElementById('annEditor');

function execCmd(cmd, val) { editor.focus(); document.execCommand(cmd, false, val !== undefined ? val : null); updateToolbarState(); }
function execFmtBlock(tag) { if (!tag) return; editor.focus(); document.execCommand('formatBlock', false, '<' + tag + '>'); updateToolbarState(); }
function syncEditor() { document.getElementById('annMessage').value = editor.innerHTML; return true; }
function clearFormatting() { editor.focus(); document.execCommand('selectAll', false, null); document.execCommand('removeFormat', false, null); document.execCommand('formatBlock', false, '<p>'); updateToolbarState(); }

let _savedRange = null;
function saveEditorSelection() { const s = window.getSelection(); if (s && s.rangeCount > 0) _savedRange = s.getRangeAt(0).cloneRange(); }
function restoreEditorSelection() { editor.focus(); if (_savedRange) { const s = window.getSelection(); s.removeAllRanges(); s.addRange(_savedRange); } }

function updateToolbarState() {
    const m = { tbBold:'bold', tbItalic:'italic', tbUnderline:'underline', tbStrike:'strikeThrough' };
    for (const [id, cmd] of Object.entries(m)) { const b = document.getElementById(id); if (b) b.classList.toggle('active', document.queryCommandState(cmd)); }
}
editor.addEventListener('keyup', updateToolbarState);
editor.addEventListener('mouseup', updateToolbarState);

// TABLE
function openTableModal() {
    saveEditorSelection();
    document.getElementById('tableRows').value = '3';
    document.getElementById('tableCols').value = '3';
    document.getElementById('tableHasHeader').checked = true;
    bootstrap.Modal.getOrCreateInstance(document.getElementById('annTableModal')).show();
}
function doInsertTable() {
    const rows = Math.max(1, parseInt(document.getElementById('tableRows').value) || 3);
    const cols = Math.max(1, parseInt(document.getElementById('tableCols').value) || 3);
    const hdr  = document.getElementById('tableHasHeader').checked;
    let html = '<table><tbody>';
    for (let r = 0; r < rows; r++) {
        html += '<tr>';
        for (let c = 0; c < cols; c++) html += r === 0 && hdr ? '<th>Header '+(c+1)+'</th>' : '<td>&nbsp;</td>';
        html += '</tr>';
    }
    html += '</tbody></table><p><br></p>';
    bootstrap.Modal.getInstance(document.getElementById('annTableModal')).hide();
    setTimeout(() => { restoreEditorSelection(); document.execCommand('insertHTML', false, html); }, 120);
}

// LINK
function openLinkModal() {
    saveEditorSelection();
    const sel = window.getSelection();
    document.getElementById('linkUrl').value  = 'https://';
    document.getElementById('linkText').value = sel && sel.rangeCount > 0 ? sel.toString().trim() : '';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('annLinkModal')).show();
    document.getElementById('annLinkModal').addEventListener('shown.bs.modal', function f() { document.getElementById('linkUrl').focus(); document.getElementById('annLinkModal').removeEventListener('shown.bs.modal', f); });
}
function doInsertLink() {
    const url = document.getElementById('linkUrl').value.trim();
    const txt = document.getElementById('linkText').value.trim();
    if (!url || url === 'https://') { document.getElementById('linkUrl').focus(); return; }
    bootstrap.Modal.getInstance(document.getElementById('annLinkModal')).hide();
    setTimeout(() => { restoreEditorSelection(); document.execCommand('insertHTML', false, '<a href="'+escH(url)+'" target="_blank" rel="noopener noreferrer">'+(txt ? escH(txt) : escH(url))+'</a>'); }, 120);
}

// IMAGE
function openImageModal() {
    saveEditorSelection();
    document.getElementById('imageUrl').value = '';
    document.getElementById('imageAlt').value = '';
    document.getElementById('imgPreviewWrap').style.display = 'none';
    bootstrap.Modal.getOrCreateInstance(document.getElementById('annImageModal')).show();
    document.getElementById('annImageModal').addEventListener('shown.bs.modal', function f() { document.getElementById('imageUrl').focus(); document.getElementById('annImageModal').removeEventListener('shown.bs.modal', f); });
}
document.getElementById('imageUrl').addEventListener('input', function() {
    const u = this.value.trim(), w = document.getElementById('imgPreviewWrap'), i = document.getElementById('imgPreview');
    if (u && u !== 'https://') { i.src = u; w.style.display = ''; } else w.style.display = 'none';
});
function doInsertImage() {
    const url = document.getElementById('imageUrl').value.trim();
    const alt = document.getElementById('imageAlt').value.trim();
    if (!url) { document.getElementById('imageUrl').focus(); return; }
    bootstrap.Modal.getInstance(document.getElementById('annImageModal')).hide();
    setTimeout(() => { restoreEditorSelection(); document.execCommand('insertHTML', false, '<img src="'+escH(url)+'" alt="'+escH(alt)+'" style="max-width:100%;height:auto;">'); }, 120);
}

function escH(s) { return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }

function editAnn(ann) {
    document.getElementById('annEditId').value = ann.id || '';
    document.getElementById('annCategory').value = ann.category || 'General';
    document.getElementById('annTitle').value = ann.title || '';
    document.getElementById('annAudience').value = ann.audience === 'branch' ? 'branch' : 'everyone';
    editor.innerHTML = ann.message || '';
    document.getElementById('annEnabled').checked = !!ann.enabled;
    document.getElementById('annFormTitle').innerHTML = '<i class="bi bi-pencil-square me-1"></i>Edit Announcement';
    document.getElementById('annCancelEdit').style.display = '';
    updateTitlePreview(ann.title || '');
    window.scrollTo({top: 0, behavior: 'smooth'});
}

function resetAnnForm() {
    document.getElementById('annEditId').value = '';
    document.getElementById('annCategory').value = 'General';
    document.getElementById('annTitle').value = '';
    document.getElementById('annAudience').value = 'everyone';
    editor.innerHTML = '';
    document.getElementById('annEnabled').checked = true;
    document.getElementById('annFormTitle').innerHTML = '<i class="bi bi-megaphone me-1"></i>Add Announcement';
    document.getElementById('annCancelEdit').style.display = 'none';
    updateTitlePreview('');
}

function confirmAnnDelete(form) {
    if (typeof appConfirm === 'function') {
        const title = form?.closest('tr')?.querySelector('td:first-child')?.textContent?.trim() || 'Unknown';
        const id = form?.querySelector('[name="delete_id"]')?.value || '—';
        appConfirm(`Delete this announcement?\n\nTitle: ${title}\nAnnouncement ID: ${id}`, {title:'Delete Announcement',confirmText:'Delete',cancelText:'Cancel',variant:'danger'}).then(c => { if (c && form) form.submit(); });
        return false;
    }
    return confirm('Delete this announcement?');
}

function updateTitlePreview(val) {
    const wrap = document.getElementById('annTitlePreviewWrap'), prev = document.getElementById('annTitlePreview');
    if (val.trim()) { prev.textContent = val; wrap.style.display = ''; } else { wrap.style.display = 'none'; prev.textContent = ''; }
}
document.getElementById('annTitle').addEventListener('input', function() { updateTitlePreview(this.value); });
// Defensive: remove any helper text like "(shown large & bold in popup)" if present in the settings UI
(function(){
    const helperText = '(shown large & bold in popup)';
    function stripHelper(el) {
        if (!el) return;
        // check next sibling text nodes/elements
        let n = el.nextSibling;
        while (n) {
            if (n.nodeType === Node.TEXT_NODE && n.textContent && n.textContent.includes(helperText)) {
                n.textContent = n.textContent.replace(helperText, '').trim();
                return;
            }
            if (n.nodeType === Node.ELEMENT_NODE && n.textContent && n.textContent.includes(helperText)) {
                n.textContent = n.textContent.replace(helperText, '').trim();
                return;
            }
            n = n.nextSibling;
        }
        // also search within the label itself
        if (el.textContent && el.textContent.includes(helperText)) {
            el.textContent = el.textContent.replace(helperText, '').trim();
        }
    }
    // try label for annTitle
    const lbl = document.querySelector('label[for="annTitle"]');
    stripHelper(lbl);
    // try preview wrapper label or small help text near preview
    const previewWrap = document.getElementById('annTitlePreviewWrap');
    if (previewWrap) {
        // remove helper inside preview wrap if any
        const elems = previewWrap.querySelectorAll('*');
        for (const e of elems) {
            if (e.textContent && e.textContent.includes(helperText)) e.textContent = e.textContent.replace(helperText, '').trim();
        }
    }
})();
</script>

<?php include __DIR__ . '/../partials/footer.php'; ?>
