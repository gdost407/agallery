@extends('app.layouts.layouts')

@section('content')

  <!-- ========== STATS CARDS ========== -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
      <div class="bg-white p-3 rounded-3 shadow-sm h-100 d-flex align-items-center gap-3">
        <div class="bg-primary bg-opacity-10 rounded-3 p-3">
          <i class="bi bi-folder2-open text-primary fs-3"></i>
        </div>
        <div>
          <div class="text-secondary small">Total Folders</div>
          <div class="fw-bold fs-4">24</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="bg-white p-3 rounded-3 shadow-sm h-100 d-flex align-items-center gap-3">
        <div class="bg-success bg-opacity-10 rounded-3 p-3">
          <i class="bi bi-file-earmark text-success fs-3"></i>
        </div>
        <div>
          <div class="text-secondary small">Total Files</div>
          <div class="fw-bold fs-4">2,847</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="bg-white p-3 rounded-3 shadow-sm h-100 d-flex align-items-center gap-3">
        <div class="bg-warning bg-opacity-10 rounded-3 p-3">
          <i class="bi bi-star-fill text-warning fs-3"></i>
        </div>
        <div>
          <div class="text-secondary small">Starred</div>
          <div class="fw-bold fs-4">18</div>
        </div>
      </div>
    </div>
    <div class="col-6 col-md-3">
      <div class="bg-white p-3 rounded-3 shadow-sm h-100 d-flex align-items-center gap-3">
        <div class="bg-danger bg-opacity-10 rounded-3 p-3">
          <i class="bi bi-trash3-fill text-danger fs-3"></i>
        </div>
        <div>
          <div class="text-secondary small">Trash</div>
          <div class="fw-bold fs-4">7</div>
        </div>
      </div>
    </div>
  </div>

  <!-- ========== STORAGE & QUICK ACCESS ========== -->
  <div class="row g-3 mb-4">
    <!-- Storage Card -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="bg-white p-3 rounded-3 shadow-sm h-100">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="fw-semibold"><i class="bi bi-hdd-stack text-primary me-1"></i> Storage</span>
          <span class="badge bg-primary bg-opacity-10 text-primary">160GB of 200GB</span>
        </div>
        <canvas id="storageChart" width="200" height="200"></canvas>
        <!-- <div class="d-flex justify-content-between small text-secondary">
          <span>Used: 160 GB</span>
          <span>Free: 40 GB</span>
        </div>
        <hr class="my-2">
        <div class="d-flex justify-content-between">
          <span class="small"><span class="badge bg-primary bg-opacity-10 text-primary me-1">●</span> Documents</span>
          <span class="small">24.5 GB</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="small"><span class="badge bg-warning bg-opacity-10 text-warning me-1">●</span> Images</span>
          <span class="small">32.5 GB</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="small"><span class="badge bg-success bg-opacity-10 text-success me-1">●</span> Audio</span>
          <span class="small">46.5 GB</span>
        </div>
        <div class="d-flex justify-content-between">
          <span class="small"><span class="badge bg-danger bg-opacity-10 text-danger me-1">●</span> Video</span>
          <span class="small">56.5 GB</span>
        </div>
        <div class="mt-2">
          <button class="btn btn-outline-primary btn-sm rounded-pill w-100">
            <i class="bi bi-arrow-up-circle me-1"></i> Upgrade Storage
          </button>
        </div> -->
      </div>
    </div>

    <!-- Quick Access -->
    <div class="col-12 col-md-6 col-lg-4">
      <div class="bg-white p-3 rounded-3 shadow-sm h-100">
        <h6 class="fw-semibold mb-3"><i class="bi bi-grid-3x3-gap-fill me-1"></i> Quick Access</h6>
        <div class="d-grid gap-2">
          <a href="#" class="btn btn-light border rounded-3 text-start d-flex align-items-center gap-3">
            <i class="bi bi-star-fill text-warning fs-5"></i>
            <div><div class="fw-semibold">Starred</div><div class="small text-secondary">18 items</div></div>
            <span class="ms-auto text-secondary"><i class="bi bi-chevron-right"></i></span>
          </a>
          <a href="#" class="btn btn-light border rounded-3 text-start d-flex align-items-center gap-3">
            <i class="bi bi-share-fill text-success fs-5"></i>
            <div><div class="fw-semibold">Shared</div><div class="small text-secondary">12 items</div></div>
            <span class="ms-auto text-secondary"><i class="bi bi-chevron-right"></i></span>
          </a>
          <a href="#" class="btn btn-light border rounded-3 text-start d-flex align-items-center gap-3">
            <i class="bi bi-clock-history text-info fs-5"></i>
            <div><div class="fw-semibold">Recent</div><div class="small text-secondary">Last 7 days</div></div>
            <span class="ms-auto text-secondary"><i class="bi bi-chevron-right"></i></span>
          </a>
          <a href="#" class="btn btn-light border rounded-3 text-start d-flex align-items-center gap-3">
            <i class="bi bi-trash3-fill text-danger fs-5"></i>
            <div><div class="fw-semibold">Trash</div><div class="small text-secondary">7 items</div></div>
            <span class="ms-auto text-secondary"><i class="bi bi-chevron-right"></i></span>
          </a>
        </div>
      </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-12 col-lg-4">
      <div class="bg-white p-3 rounded-3 shadow-sm h-100">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-semibold mb-0"><i class="bi bi-activity me-1"></i> Recent Activity</h6>
          <span class="badge bg-light text-secondary">Today</span>
        </div>
        <div class="d-flex align-items-start gap-3 mb-3">
          <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-circle"><i class="bi bi-upload"></i></span>
          <div>
            <div class="fw-semibold small">Uploaded Annual_Report_2025.pdf</div>
            <div class="small text-secondary">2 minutes ago</div>
          </div>
        </div>
        <div class="d-flex align-items-start gap-3 mb-3">
          <span class="badge bg-warning bg-opacity-10 text-warning p-2 rounded-circle"><i class="bi bi-star"></i></span>
          <div>
            <div class="fw-semibold small">Starred Project_Proposal.docx</div>
            <div class="small text-secondary">15 minutes ago</div>
          </div>
        </div>
        <div class="d-flex align-items-start gap-3 mb-3">
          <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle"><i class="bi bi-share"></i></span>
          <div>
            <div class="fw-semibold small">Shared Team_Photo_2026.jpg with Alice</div>
            <div class="small text-secondary">1 hour ago</div>
          </div>
        </div>
        <div class="d-flex align-items-start gap-3">
          <span class="badge bg-danger bg-opacity-10 text-danger p-2 rounded-circle"><i class="bi bi-trash3"></i></span>
          <div>
            <div class="fw-semibold small">Moved Old_Draft_v1.docx to trash</div>
            <div class="small text-secondary">3 hours ago</div>
          </div>
        </div>
        <hr class="my-2">
        <a href="#" class="text-decoration-none small text-primary">View all activity →</a>
      </div>
    </div>
  </div>

  <!-- ========== RECENT FILES TABLE ========== -->
  <div class="row g-3 mb-4">
    <div class="col-12">
      <div class="bg-white rounded-3 shadow-sm p-3 p-md-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
          <h6 class="fw-semibold mb-0"><i class="bi bi-file-earmark me-1"></i> Recent Files</h6>
          <a href="#" class="text-decoration-none small text-primary">View all →</a>
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Modified</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><i class="bi bi-filetype-pdf text-danger me-2"></i> Annual_Report_2025.pdf</td>
                <td><span class="badge bg-danger bg-opacity-10 text-danger">PDF</span></td>
                <td>2.4 MB</td>
                <td>Jan 20, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
              <tr>
                <td><i class="bi bi-filetype-docx text-primary me-2"></i> Project_Proposal.docx</td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary">Word</span></td>
                <td>856 KB</td>
                <td>Jan 19, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
              <tr>
                <td><i class="bi bi-filetype-xlsx text-success me-2"></i> Sales_Data_2026.xlsx</td>
                <td><span class="badge bg-success bg-opacity-10 text-success">Excel</span></td>
                <td>1.8 MB</td>
                <td>Jan 18, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
              <tr>
                <td><i class="bi bi-filetype-pptx text-warning me-2"></i> Presentation_Q4.pptx</td>
                <td><span class="badge bg-warning bg-opacity-10 text-warning">PPT</span></td>
                <td>4.2 MB</td>
                <td>Jan 17, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

<!-- <div class="row">
  <div class="col-lg-9">
    <h3>My AGallery</h3>
    <div class="row">
      <div class="col-sm-4">
        <div class="card border">
          <div class="card-body p-3">
            <div class="d-flex flex-column">
              <div><i class="bi bi-folder-fill text-warning fs-5"></i></div>
              <div class="small my-2">Documents</div>
              <div class="h3">640 Files</div>
              <div class="progress" role="progressbar" aria-label="Basic example" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar w-75"></div>
              </div>
              <div class="small mt-2">24.5 GB <span class="float-end">50GB</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="card border">
          <div class="card-body p-3">
            <div class="d-flex flex-column">
              <div><i class="bi bi-folder-fill text-warning fs-5"></i></div>
              <div class="small my-2">Images</div>
              <div class="h3">640 Files</div>
              <div class="progress" role="progressbar" aria-label="Basic example" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar w-75"></div>
              </div>
              <div class="small mt-2">24.5 GB <span class="float-end">50GB</span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-4">
        <div class="card border">
          <div class="card-body p-3">
            <div class="d-flex flex-column">
              <div><i class="bi bi-folder-fill text-warning fs-5"></i></div>
              <div class="small my-2">Videos</div>
              <div class="h3">640 Files</div>
              <div class="progress" role="progressbar" aria-label="Basic example" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100">
                <div class="progress-bar w-75"></div>
              </div>
              <div class="small mt-2">24.5 GB <span class="float-end">50GB</span></div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <h3 class="mt-4">Folders</h3>
    <div class="row">
      <div class="col-sm-3">
        <div class="card border">
          <div class="card-body p-3">
            <div class="d-flex flex-column">
              <div><i class="bi bi-folder-fill text-warning fs-5"></i></div>
              <div class="small my-2">Mobile App</div>
              <div class="h3">640 Files</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="card border">
          <div class="card-body p-3">
            <div class="d-flex flex-column">
              <div><i class="bi bi-folder-fill text-warning fs-5"></i></div>
              <div class="small my-2">illustrations</div>
              <div class="h3">640 Files</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="card border">
          <div class="card-body p-3">
            <div class="d-flex flex-column">
              <div><i class="bi bi-folder-fill text-warning fs-5"></i></div>
              <div class="small my-2">Web designs</div>
              <div class="h3">640 Files</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-sm-3">
        <div class="card border">
          <div class="card-body p-3">
            <div class="d-flex flex-column">
              <div><i class="bi bi-folder-fill text-warning fs-5"></i></div>
              <div class="small my-2">Games</div>
              <div class="h3">640 Files</div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <h3 class="mt-4">Files</h3>
    <table class="table">
      <thead>
        <tr>
          <th scope="col">#</th>
          <th scope="col">File Name</th>
          <th scope="col">Size</th>
          <th scope="col">Folder</th>
          <th scope="col">Date</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <th scope="row"><i class="bi bi-folder-fill text-primary"></i></th>
          <td>Resume</td>
          <td>1mb</td>
          <td>CV</td>
          <td>Jan 23, 2022</td>
        </tr>
        <tr>
          <th scope="row"><i class="bi bi-file-earmark-text text-success"></i></th>
          <td>Book</td>
          <td>10mb</td>
          <td>Temp</td>
          <td>Jan 23, 2022</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="col-lg-3">
    <h3>Storage Details</h3>
    <div class="card border">
      <div class="card-body p-3">
        <div class="mt-3">
          <div class="d-flex align-items-center mb-2">
            <span class="bg-primary rounded-circle me-2" style="width: 15px; height: 15px;"></span>
            <span>Documents</span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <span class="bg-success rounded-circle me-2" style="width: 15px; height: 15px;"></span>
            <span>Images</span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <span class="bg-warning rounded-circle me-2" style="width: 15px; height: 15px;"></span>
            <span>Videos</span>
          </div>
          <div class="d-flex align-items-center mb-2">
            <span class="bg-danger rounded-circle me-2" style="width: 15px; height: 15px;"></span>
            <span>Audio</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div> -->
 
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
  const ctx = document.getElementById('storageChart');
  const data = {
    labels: [
      'Red',
      'Blue',
      'Yellow'
    ],
    datasets: [{
      label: 'My First Dataset',
      data: [300, 50, 100],
      backgroundColor: [
        'rgb(255, 99, 132)',
        'rgb(54, 162, 235)',
        'rgb(255, 205, 86)'
      ],
      hoverOffset: 4
    }]
  };
  const myChart = new Chart(ctx, {
    type: 'doughnut',
    data: data
  });
</script>

@endsection
