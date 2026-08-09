@include('app.components.upload-doc')
<aside class="left-sidebar">
    <!-- Sidebar scroll-->
    <div>
        <div class="brand-logo d-flex align-items-center justify-content-between">
            <a href="./index.html" class="text-nowrap logo-img">
                <img src="{{ asset('assets/AGallery-Logo-Golden.png') }}" alt="" style="width: 100px;" />
            </a>
            <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
                <i class="ti ti-x fs-8"></i>
            </div>
        </div>
        <!-- Sidebar navigation-->
        <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
            <ul id="sidebarnav">

                <!-- New -->
                <li class="sidebar-item mb-3">
                    <a class="btn btn-outline-light w-100 py-2" href="javascript:void(0)" data-bs-toggle="offcanvas" data-bs-target="#offcanvasUploadDoc" aria-controls="offcanvasUploadDoc">
                        <i class="bi bi-plus-lg me-2"></i>
                        New File
                    </a>
                </li>

                <!-- Files -->
                <li class="nav-small-cap">
                    <span class="hide-menu">FILES</span>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.dashboard') }}">
                        <span><i class="bi bi-folder2-open"></i></span>
                        <span class="hide-menu">All Files</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.photos') }}">
                        <span><i class="bi bi-image"></i></span>
                        <span class="hide-menu">Photos</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.videos') }}">
                        <span><i class="bi bi-camera-video"></i></span>
                        <span class="hide-menu">Videos</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.documents') }}">
                        <span><i class="bi bi-file-earmark-text"></i></span>
                        <span class="hide-menu">Documents</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.starred') }}">
                        <span><i class="bi bi-star"></i></span>
                        <span class="hide-menu">Starred</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.recent') }}">
                        <span><i class="bi bi-clock-history"></i></span>
                        <span class="hide-menu">Recent</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.shared') }}">
                        <span><i class="bi bi-share"></i></span>
                        <span class="hide-menu">Shared</span>
                    </a>
                </li>

                <li class="sidebar-item">
                    <a class="sidebar-link" href="{{ route('app.trash') }}">
                        <span><i class="bi bi-trash3"></i></span>
                        <span class="hide-menu">Trash</span>
                    </a>
                </li>

                <!-- Storage -->
                <li class="nav-small-cap mt-4">
                    <span class="hide-menu">STORAGE</span>
                </li>

                <li class="px-3 mt-3">

                    <div class="d-flex justify-content-between mb-1">
                        <small>75% Used</small>
                        <small>3.75 GB / 5 GB</small>
                    </div>

                    <div class="progress mb-3" style="height:8px;">
                        <div class="progress-bar bg-primary"
                            role="progressbar"
                            style="width:75%;">
                        </div>
                    </div>

                    <a href="#" class="btn btn-primary w-100">
                        <i class="bi bi-cloud-arrow-up me-2"></i>
                        Get Storage
                    </a>

                </li>

            </ul>
        </nav>
        <!-- End Sidebar navigation -->
    </div>
    <!-- End Sidebar scroll-->
</aside>