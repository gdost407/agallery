@extends('app.layouts.layouts')

@section('content')

<div>
  <h3>My AGallery Videos</h3> 
</div>

<!-- ========== MAIN CONTENT ========== -->
<div class="mt-3">

  <!-- Categories / chips -->
  <div class="d-flex flex-wrap gap-2 mb-4 overflow-auto pb-2" style="white-space: nowrap;">
    <span class="badge bg-dark rounded-pill px-4 py-2 fw-normal">All</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Music</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Gaming</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">News</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Live</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Sports</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Education</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Comedy</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Travel</span>
  </div>

  <!-- Video Grid -->
  <div class="row g-4">

    <!-- Video 1 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v1/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">12:34</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">Amazing travel vlog | 4K</h6>
              <p class="card-text small text-secondary mb-0">TravelWithMe</p>
              <p class="card-text small text-secondary mb-0">1.2M views · 3 days ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video 2 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v2/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">8:21</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">React tutorial for beginners</h6>
              <p class="card-text small text-secondary mb-0">CodeMaster</p>
              <p class="card-text small text-secondary mb-0">456K views · 1 week ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video 3 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v3/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">45:12</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">Live concert: Rock night</h6>
              <p class="card-text small text-secondary mb-0">MusicLive</p>
              <p class="card-text small text-secondary mb-0">2.8M views · 2 months ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video 4 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v4/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">6:15</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">Daily morning routine</h6>
              <p class="card-text small text-secondary mb-0">LifeStyle</p>
              <p class="card-text small text-secondary mb-0">324K views · 5 days ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video 5 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v5/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">22:09</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">Documentary: Ocean deep</h6>
              <p class="card-text small text-secondary mb-0">NatGeo</p>
              <p class="card-text small text-secondary mb-0">1.7M views · 2 weeks ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video 6 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v6/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">3:45</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">Funny cat compilation</h6>
              <p class="card-text small text-secondary mb-0">CutePets</p>
              <p class="card-text small text-secondary mb-0">5.3M views · 1 month ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video 7 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v7/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">14:27</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">Fitness workout routine</h6>
              <p class="card-text small text-secondary mb-0">FitLife</p>
              <p class="card-text small text-secondary mb-0">678K views · 4 days ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Video 8 -->
    <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
      <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
        <div class="position-relative">
          <img src="https://picsum.photos/seed/v8/400/225" class="card-img-top" alt="Video thumbnail" style="aspect-ratio: 16/9; object-fit: cover;">
          <span class="position-absolute bottom-0 end-0 bg-black bg-opacity-75 text-white px-2 py-1 small m-2 rounded-1">9:58</span>
        </div>
        <div class="card-body px-3 py-2">
          <div class="d-flex gap-2">
            <div class="flex-shrink-0">
              <span class="d-inline-block bg-secondary bg-opacity-25 rounded-circle" style="width: 36px; height: 36px;"></span>
            </div>
            <div class="flex-grow-1">
              <h6 class="card-title mb-1 small fw-semibold">Tech review: New phone</h6>
              <p class="card-text small text-secondary mb-0">TechGuru</p>
              <p class="card-text small text-secondary mb-0">892K views · 2 days ago</p>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div> <!-- /video grid -->

  <!-- Load more button (YouTube style) -->
  <div class="row mt-4 mb-3">
    <div class="col-12 text-center">
      <button class="btn btn-light border rounded-pill px-5 py-2 fw-semibold">
        <i class="bi bi-arrow-down-circle me-2"></i>Load more
      </button>
    </div>
  </div>

</div> <!-- /container-fluid -->

@endsection
