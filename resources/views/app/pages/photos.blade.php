@extends('app.layouts.layouts')

@section('content')

<!-- ========== TOPIC PILLS (Pinterest style) ========== -->
<div class="position-sticky top-0" style="z-index: 10;">
  <div class="d-flex flex-wrap gap-2 mb-4 overflow-auto pb-2" style="white-space: nowrap;">
    <span class="badge bg-dark rounded-pill px-4 py-2 fw-normal">For you</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Travel</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Art</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Nature</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Architecture</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Food</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Fashion</span>
    <span class="badge bg-light text-dark rounded-pill px-4 py-2 fw-normal border">Interior</span>
  </div>
</div>

<!-- ========== GALLERY GRID (Pinterest style – all images) ========== -->
<div class="">
  <div class="row g-4">

    <!-- Card 1 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p1/400/500" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.25; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Cozy mountain cabin</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@lisa</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 124</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 2 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p2/400/600" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.5; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Minimalist living room</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@julie</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 89</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 3 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p3/400/450" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.12; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Golden hour beach</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@mike</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 256</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 4 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p4/400/700" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.75; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Urban street art</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@alex</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 312</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 5 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p5/400/520" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.3; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Matcha latte art</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@emma</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 73</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 6 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p6/400/380" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/0.95; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Abstract liquid art</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@sofi</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 198</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 7 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p7/400/640" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.6; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Japanese garden</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@yuki</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 445</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 8 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p8/400/480" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.2; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Vintage camera collection</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@tom</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 67</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 9 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p9/400/560" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.4; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Night sky timelapse</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@nasa</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 892</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 10 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p10/400/420" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.05; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Tropical leaf pattern</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@nature</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 155</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 11 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p11/400/680" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.7; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Bauhaus poster design</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@design</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 210</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Card 12 -->
    <div class="col-6 col-sm-4 col-md-2">
      <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <img src="https://picsum.photos/seed/p12/400/500" class="card-img-top rounded-4" alt="Image" style="aspect-ratio: 1/1.25; object-fit: cover;">
        <div class="card-body px-2 pt-2 pb-1">
          <h6 class="card-title mb-0 small fw-semibold">Colorful macarons</h6>
          <div class="d-flex align-items-center gap-2 mt-1">
            <span class="badge bg-secondary bg-opacity-10 text-secondary">@foodie</span>
            <span class="ms-auto text-secondary-emphasis small"><i class="bi bi-heart"></i> 334</span>
          </div>
        </div>
      </div>
    </div>

  </div> <!-- /row -->
</div> <!-- /container -->

@endsection
