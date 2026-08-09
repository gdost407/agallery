@extends('app.layouts.layouts')

@section('content')

<div>
  <h3>My AGallery Trash</h3> 
</div>
<div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;"><input class="form-check-input" type="checkbox"></th>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Deleted</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-trash3-fill text-danger me-2"></i> Old_Draft_v1.docx</td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary">Word</span></td>
                <td>456 KB</td>
                <td>Jan 18, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-arrow-counterclockwise"></i></button>
                  <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-x-lg"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-trash3-fill text-danger me-2"></i> Temp_Data.xlsx</td>
                <td><span class="badge bg-success bg-opacity-10 text-success">Excel</span></td>
                <td>1.2 MB</td>
                <td>Jan 17, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-arrow-counterclockwise"></i></button>
                  <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-x-lg"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-trash3-fill text-danger me-2"></i> Backup_2025.zip</td>
                <td><span class="badge bg-secondary bg-opacity-10 text-secondary">ZIP</span></td>
                <td>156 MB</td>
                <td>Jan 15, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-arrow-counterclockwise"></i></button>
                  <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-x-lg"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-trash3-fill text-danger me-2"></i> Screenshot_2025_12_31.png</td>
                <td><span class="badge bg-info bg-opacity-10 text-info">PNG</span></td>
                <td>2.8 MB</td>
                <td>Jan 14, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-arrow-counterclockwise"></i></button>
                  <button class="btn btn-sm btn-outline-danger border-0"><i class="bi bi-x-lg"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
@endsection
