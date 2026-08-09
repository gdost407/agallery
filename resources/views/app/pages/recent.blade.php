@extends('app.layouts.layouts')

@section('content')

<div>
  <h3>My AGallery Recent</h3> 
</div>
<div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
              <tr>
                <th style="width: 40px;"><input class="form-check-input" type="checkbox"></th>
                <th>Name</th>
                <th>Type</th>
                <th>Size</th>
                <th>Last opened</th>
                <th class="text-center">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-clock-history text-info me-2"></i> app_controller.js</td>
                <td><span class="badge bg-warning bg-opacity-10 text-warning">JS</span></td>
                <td>24 KB</td>
                <td>Today, 2:30 PM</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-clock-history text-info me-2"></i> data_processor.py</td>
                <td><span class="badge bg-info bg-opacity-10 text-info">Python</span></td>
                <td>45 KB</td>
                <td>Today, 11:15 AM</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-clock-history text-info me-2"></i> User_Manual_v3.pdf</td>
                <td><span class="badge bg-danger bg-opacity-10 text-danger">PDF</span></td>
                <td>5.7 MB</td>
                <td>Yesterday, 4:45 PM</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-clock-history text-info me-2"></i> Team_Photo_2026.jpg</td>
                <td><span class="badge bg-info bg-opacity-10 text-info">JPG</span></td>
                <td>3.6 MB</td>
                <td>Yesterday, 10:00 AM</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
              <tr>
                <td><input class="form-check-input" type="checkbox"></td>
                <td><i class="bi bi-clock-history text-info me-2"></i> Annual_Report_2025.pdf</td>
                <td><span class="badge bg-danger bg-opacity-10 text-danger">PDF</span></td>
                <td>2.4 MB</td>
                <td>Jan 19, 2026</td>
                <td class="text-center">
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-star"></i></button>
                  <button class="btn btn-sm btn-outline-secondary border-0"><i class="bi bi-share"></i></button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

@endsection
