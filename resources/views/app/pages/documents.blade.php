@extends('app.layouts.layouts')

@section('content')

<div>
  <h3>My AGallery Documents</h3> 
</div>

<!-- ========== MAIN CONTENT ========== -->
<div class="mt-3">

  <!-- Categories / chips -->
  <div class="d-flex flex-wrap gap-2 mb-4 overflow-auto pb-2" style="white-space: nowrap;">
    <span class="badge bg-dark rounded-pill px-4 py-2 fw-normal">All</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">PDF</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Word</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Excel</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">PowerPoint</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Images</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Archives</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Code</span>
  </div>

  <!-- Document Grid -->
  <div class="row g-4">

    <!-- Document 1 - PDF -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-pdf text-danger fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Annual_Report_2025.pdf</h6>
              <p class="card-text small text-secondary mb-1">PDF document</p>
              <p class="card-text small text-secondary mb-0">2.4 MB · 3 days ago</p>
              <div class="mt-2">
                <span class="badge bg-danger bg-opacity-10 text-danger">PDF</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Financial</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 2 - Word -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-docx text-primary fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Project_Proposal.docx</h6>
              <p class="card-text small text-secondary mb-1">Word document</p>
              <p class="card-text small text-secondary mb-0">856 KB · 1 week ago</p>
              <div class="mt-2">
                <span class="badge bg-primary bg-opacity-10 text-primary">Word</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Draft</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 3 - Excel -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-xlsx text-success fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Sales_Data_2026.xlsx</h6>
              <p class="card-text small text-secondary mb-1">Excel spreadsheet</p>
              <p class="card-text small text-secondary mb-0">1.8 MB · 2 days ago</p>
              <div class="mt-2">
                <span class="badge bg-success bg-opacity-10 text-success">Excel</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Data</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 4 - PowerPoint -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-pptx text-warning fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Presentation_Q4.pptx</h6>
              <p class="card-text small text-secondary mb-1">PowerPoint</p>
              <p class="card-text small text-secondary mb-0">4.2 MB · 5 days ago</p>
              <div class="mt-2">
                <span class="badge bg-warning bg-opacity-10 text-warning">PPT</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Slides</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 5 - Image -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-jpg text-info fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Team_Photo_2026.jpg</h6>
              <p class="card-text small text-secondary mb-1">Image</p>
              <p class="card-text small text-secondary mb-0">3.6 MB · 1 month ago</p>
              <div class="mt-2">
                <span class="badge bg-info bg-opacity-10 text-info">JPG</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Photo</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 6 - Archive -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-zip text-secondary fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Backup_Files_2026.zip</h6>
              <p class="card-text small text-secondary mb-1">Archive</p>
              <p class="card-text small text-secondary mb-0">156 MB · 2 weeks ago</p>
              <div class="mt-2">
                <span class="badge bg-secondary bg-opacity-10 text-secondary">ZIP</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Backup</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 7 - Code -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-js text-warning fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">app_controller.js</h6>
              <p class="card-text small text-secondary mb-1">JavaScript</p>
              <p class="card-text small text-secondary mb-0">24 KB · 1 day ago</p>
              <div class="mt-2">
                <span class="badge bg-warning bg-opacity-10 text-warning">JS</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Code</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 8 - PDF -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-pdf text-danger fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">User_Manual_v3.pdf</h6>
              <p class="card-text small text-secondary mb-1">PDF document</p>
              <p class="card-text small text-secondary mb-0">5.7 MB · 1 month ago</p>
              <div class="mt-2">
                <span class="badge bg-danger bg-opacity-10 text-danger">PDF</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Guide</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 9 - Word -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-docx text-primary fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Meeting_Minutes.docx</h6>
              <p class="card-text small text-secondary mb-1">Word document</p>
              <p class="card-text small text-secondary mb-0">128 KB · 3 hours ago</p>
              <div class="mt-2">
                <span class="badge bg-primary bg-opacity-10 text-primary">Word</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Meeting</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 10 - Excel -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-xlsx text-success fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Inventory_Tracker.xlsx</h6>
              <p class="card-text small text-secondary mb-1">Excel spreadsheet</p>
              <p class="card-text small text-secondary mb-0">3.2 MB · 2 weeks ago</p>
              <div class="mt-2">
                <span class="badge bg-success bg-opacity-10 text-success">Excel</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Inventory</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 11 - PowerPoint -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-pptx text-warning fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">Training_Session.pptx</h6>
              <p class="card-text small text-secondary mb-1">PowerPoint</p>
              <p class="card-text small text-secondary mb-0">6.8 MB · 6 days ago</p>
              <div class="mt-2">
                <span class="badge bg-warning bg-opacity-10 text-warning">PPT</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Training</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Document 12 - Code -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="card-body p-3">
          <div class="d-flex align-items-start gap-3">
            <div class="flex-shrink-0">
              <i class="bi bi-filetype-py text-info fs-1"></i>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 fw-semibold">data_processor.py</h6>
              <p class="card-text small text-secondary mb-1">Python</p>
              <p class="card-text small text-secondary mb-0">45 KB · 4 days ago</p>
              <div class="mt-2">
                <span class="badge bg-info bg-opacity-10 text-info">Python</span>
                <span class="badge bg-secondary bg-opacity-10 text-secondary ms-1">Script</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div> <!-- /document grid -->

  <!-- Load more button -->
  <div class="row mt-4 mb-3">
    <div class="col-12 text-center">
      <button class="btn btn-light border rounded-pill px-5 py-2 fw-semibold">
        <i class="bi bi-arrow-down-circle me-2"></i>Load more
      </button>
    </div>
  </div>

</div> <!-- /container-fluid -->

@endsection
