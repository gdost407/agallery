@extends('app.layouts.layouts')

@section('content')

<div>
  <h3>My AGallery Starred</h3> 
</div>
<div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;"><input class="form-check-input" type="checkbox"></th>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Modified</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-star-fill text-warning me-2"></i> Annual_Report_2025.pdf</td>
                <td><span class="badge bg-danger bg-opacity-10 text-danger">PDF</span></td>
                <td>2.4 MB</td>
                <td>Jan 15, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-trash3"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-star-fill text-warning me-2"></i> Project_Proposal.docx</td>
                <td><span class="badge bg-primary bg-opacity-10 text-primary">Word</span></td>
                <td>856 KB</td>
                <td>Jan 12, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-trash3"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-star-fill text-warning me-2"></i> Sales_Data_2026.xlsx</td>
                <td><span class="badge bg-success bg-opacity-10 text-success">Excel</span></td>
                <td>1.8 MB</td>
                <td>Jan 10, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-trash3"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-star-fill text-warning me-2"></i> Presentation_Q4.pptx</td>
                <td><span class="badge bg-warning bg-opacity-10 text-warning">PPT</span></td>
                <td>4.2 MB</td>
                <td>Jan 8, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-trash3"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
@endsection
