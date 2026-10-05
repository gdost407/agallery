<div class="offcanvas offcanvas-end upload-panel" tabindex="-1" id="offcanvasUploadDoc" aria-labelledby="offcanvasUploadDocLabel">
    <div class="offcanvas-header border-bottom"><h2 class="offcanvas-title fs-5" id="offcanvasUploadDocLabel">Add to your library</h2><button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button></div>
    <div class="offcanvas-body">
        <p class="text-secondary">Choose photos, videos or documents to preview your selection.</p>
        <label class="upload-dropzone" for="document"><i class="bi bi-cloud-arrow-up" aria-hidden="true"></i><strong>Choose files</strong><span>or drop them here</span><small>Photos, videos and documents</small></label>
        <input class="form-control mt-3" type="file" id="document" name="document" multiple aria-describedby="uploadNote">
        <div id="selectedFiles" class="selected-files mt-3" aria-live="polite"></div>
        <div class="upload-info mt-4" id="uploadNote"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>File saving is not connected yet. Selected files stay on your device.</div>
    </div>
</div>
<div class="modal fade" id="filePreview" tabindex="-1" aria-labelledby="filePreviewTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content preview-modal">
        <div class="modal-header"><h2 class="modal-title fs-5 text-break" id="filePreviewTitle">File preview</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close preview"></button></div>
        <div class="modal-body text-center" id="filePreviewBody"></div>
        <div class="modal-footer justify-content-between"><small class="text-secondary">Sample file preview</small><button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button></div>
    </div></div>
</div>
