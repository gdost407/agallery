@extends('app.layouts.layouts')

@section('content')

<div>
  <h3>My AGallery Shared</h3> 
</div>
<div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;"><input class="form-check-input" type="checkbox"></th>
                <th>Name</th>
                <th>Shared with</th>
                <th>Type</th>
                <th>Size</th>
                <th>Modified</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-share-fill text-success me-2"></i> Team_Photo_2026.jpg</td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary">alice@email.com</span></td>
                <td><span class="badge bg-info bg-opacity-10 text-info">JPG</span></td>
                <td>3.6 MB</td>
                <td>Jan 14, 2026</td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-share-fill text-success me-2"></i> Meeting_Minutes.docx</td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary">bob@email.com</span></td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary">Word</span></td>
                <td>128 KB</td>
                <td>Jan 13, 2026</td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-share-fill text-success me-2"></i> Inventory_Tracker.xlsx</td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary">team@company.com</span></td>
                <td><span class="badge bg-success bg-opacity-10 text-success">Excel</span></td>
                <td>3.2 MB</td>
                <td>Jan 11, 2026</td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-share-fill text-success me-2"></i> Training_Session.pptx</td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary">training@org.com</span></td>
                <td><span class="badge bg-warning bg-opacity-10 text-warning">PPT</span></td>
                <td>6.8 MB</td>
                <td>Jan 9, 2026</td>
              </tr>
            </tbody>
          </table>
        </div>
@endsection
