<!-- breadcrumb -->
<div class="content-wrapper deeplink_wrapper">
    <section class="wrapper bg-soft-grape">
        <div class="container py-3 py-md-5">
            <nav class="d-inline-block" aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 bg-transparent">
                    <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none"><?= !empty($this->lang->line('home')) ? str_replace('\\', '', $this->lang->line('home')) : 'Home' ?></a></li>
                    <li class="breadcrumb-item active" aria-current="page"><?= !empty($this->lang->line('seller')) ? str_replace('\\', '', $this->lang->line('seller')) : 'Seller' ?></li>
                    <?php if (isset($right_breadcrumb) && !empty($right_breadcrumb)) {
                        foreach ($right_breadcrumb as $row) {
                    ?>
                            <li class="breadcrumb-item"><?= $row ?></li>
                    <?php }
                    } ?>
                </ol>
            </nav>
            <!-- /nav -->
        </div>
        <!-- /.container -->
    </section>
</div>
<!-- end breadcrumb -->

<section class="container listing-page mb-15">
    <div class="product-listing card-solid py-4">
        <div class="row mx-0">
            <!-- Dektop Sidebar -->
            <!-- remved filters -->
                <div class="">
                    <h1><?= !empty($this->lang->line('sellers')) ? str_replace('\\', '', $this->lang->line('sellers')) : 'Sellers' ?></h4>
                </div>
            
            <!-- Location-Based Seller Discovery Section -->
            <div class="container-fluid location-discovery-section mb-3">
                <div class="card location-card">
                    <div class="card-body p-3">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="location-selector d-flex align-items-center">
                                    <i class="uil uil-map-marker fs-24 text-primary me-2"></i>
                                    <div class="location-info flex-grow-1">
                                        <small class="text-muted d-block"><?= !empty($this->lang->line('your_location')) ? str_replace('\\', '', $this->lang->line('your_location')) : 'Your Location' ?></small>
                                        <span id="current_location_text" class="fw-medium">
                                            <?php if (isset($location_filtering_active) && $location_filtering_active): ?>
                                                <?= !empty($this->lang->line('location_set')) ? str_replace('\\', '', $this->lang->line('location_set')) : 'Location set' ?>
                                            <?php else: ?>
                                                <?= !empty($this->lang->line('set_your_location')) ? str_replace('\\', '', $this->lang->line('set_your_location')) : 'Set your location to find nearby sellers' ?>
                                            <?php endif; ?>
                                        </span>
                                    </div>
                                    <button type="button" id="detect_location_btn" class="btn btn-sm btn-outline-primary ms-2" title="<?= !empty($this->lang->line('detect_location')) ? str_replace('\\', '', $this->lang->line('detect_location')) : 'Detect my location' ?>">
                                        <i class="uil uil-crosshair"></i>
                                        <span class="d-none d-md-inline ms-1"><?= !empty($this->lang->line('detect')) ? str_replace('\\', '', $this->lang->line('detect')) : 'Detect' ?></span>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-4 mt-2 mt-md-0">
                                <div class="radius-selector d-flex align-items-center">
                                    <label class="me-2 text-muted text-nowrap"><?= !empty($this->lang->line('within')) ? str_replace('\\', '', $this->lang->line('within')) : 'Within' ?>:</label>
                                    <select id="distance_radius" class="form-select form-select-sm">
                                        <option value=""><?= !empty($this->lang->line('any_distance')) ? str_replace('\\', '', $this->lang->line('any_distance')) : 'Any Distance' ?></option>
                                        <option value="5" <?= (isset($search_radius) && $search_radius == 5) ? 'selected' : '' ?>>5 km</option>
                                        <option value="10" <?= (isset($search_radius) && $search_radius == 10) ? 'selected' : '' ?>>10 km</option>
                                        <option value="25" <?= (isset($search_radius) && $search_radius == 25) ? 'selected' : '' ?>>25 km</option>
                                        <option value="50" <?= (isset($search_radius) && $search_radius == 50) ? 'selected' : '' ?>>50 km</option>
                                        <option value="100" <?= (isset($search_radius) && $search_radius == 100) ? 'selected' : '' ?>>100 km</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2 mt-2 mt-md-0 text-end">
                                <?php if (isset($location_filtering_active) && $location_filtering_active): ?>
                                    <button type="button" id="clear_location_btn" class="btn btn-sm btn-outline-secondary">
                                        <i class="uil uil-times"></i>
                                        <span class="d-none d-md-inline ms-1"><?= !empty($this->lang->line('clear')) ? str_replace('\\', '', $this->lang->line('clear')) : 'Clear' ?></span>
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <!-- Location Status Messages -->
                        <div id="location_status" class="mt-2 small" style="display: none;"></div>
                    </div>
                </div>
                <!-- Hidden inputs to store location data -->
                <input type="hidden" id="user_latitude" value="<?= isset($user_latitude) ? $user_latitude : '' ?>">
                <input type="hidden" id="user_longitude" value="<?= isset($user_longitude) ? $user_longitude : '' ?>">
            </div>
            <!-- End Location-Based Seller Discovery Section -->
            
            <div class="container-fluid filter-section pb-3">
                <div class="col-12 pl-0">
                    <div class="dropdown">
                        <div class="filter-bars">
                            <div class="menu js-menu">
                                <span class="menu__line"></span>
                                <span class="menu__line"></span>
                                <span class="menu__line"></span>

                            </div>
                        </div>
                        <div class="align-items-center d-flex flex-wrap justify-content-between gap-2">
                            <div class="col-md-5 pl-0">
                                <select id="product_sort_by" class="form-control">
                                    <option><?= !empty($this->lang->line('relevance')) ? str_replace('\\', '', $this->lang->line('relevance')) : 'Relevance' ?></option>
                                    <option value="top-rated" <?= ($this->input->get('sort') == "top-rated") ? 'selected' : '' ?>><?= !empty($this->lang->line('top_rated')) ? str_replace('\\', '', $this->lang->line('top_rated')) : 'Top Rated' ?></option>
                                    <option value="nearest" <?= ($this->input->get('sort') == "nearest") ? 'selected' : '' ?>><?= !empty($this->lang->line('nearest_first')) ? str_replace('\\', '', $this->lang->line('nearest_first')) : 'Nearest First' ?></option>
                                    <option value="date-desc" <?= ($this->input->get('sort') == "date-desc") ? 'selected' : '' ?>><?= !empty($this->lang->line('newest_first')) ? str_replace('\\', '', $this->lang->line('newest_first')) : 'Newest First' ?></option>
                                    <option value="date-asc" <?= ($this->input->get('sort') == "date-asc") ? 'selected' : '' ?>><?= !empty($this->lang->line('oldest_first')) ? str_replace('\\', '', $this->lang->line('oldest_first')) : 'Oldest First' ?></option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <input type="search" name="seller_search" class="form-control" id="seller_search" value="<?= (isset($seller_search) && !empty($seller_search)) ? $seller_search : "" ?>" placeholder="<?= !empty($this->lang->line('search_seller')) ? str_replace('\\', '', $this->lang->line('search_seller')) : 'Search Seller' ?>">
                            </div>
                            <div class="dropdown float-md-right form-select-wrapper">
                                <div class="align-items-baseline d-flex">
                                    <label class="mr-2 dropdown-label"> <?= !empty($this->lang->line('show')) ? str_replace('\\', '', $this->lang->line('show')) : 'Show' ?>:</label>
                                    <a class="dropdown-border form-select col-4 mr-2" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= ($this->input->get('per-page', true) ? $this->input->get('per-page', true) : '12') ?> <span class="caret ms-4"></span></a>
                                    <a href="#" id="product_grid_view_btn" class="grid-view text-dark text-decoration-none"><i class="fs-20 uil uil-th"></i></a>
                                    <a href="#" id="product_list_view_btn" class="grid-view ps-3 text-dark text-decoration-none"><i class="fs-20 uil uil-list-ul"></i></a>
                                    <div class="dropdown-menu custom-dropdown-menu" aria-labelledby="navbarDropdown" id="per_page_sellers">
                                        <a class="dropdown-item" href="#" data-value=12>12</a>
                                        <a class="dropdown-item" href="#" data-value=16>16</a>
                                        <a class="dropdown-item" href="#" data-value=20>20</a>
                                        <a class="dropdown-item" href="#" data-value=24>24</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (isset($sellers) && !empty($sellers)) { ?>

                    <?php if (isset($_GET['type']) && $_GET['type'] == "list") { ?>
                        <div class="col-md-12 col-sm-6">
                            <div class="row mt-4" id="sellers_list_container">
                                <div class="col-12">
                                    <h1 class="h4"><?= !empty($this->lang->line('sellers')) ? str_replace('\\', '', $this->lang->line('sellers')) : 'Sellers' ?></h4>
                                </div>
                                <?php foreach ($sellers as $row) {
                                ?>
                                    <div class="card mt-5 seller-card-item" title="<?= $row['seller_name'] ?>" data-seller-id="<?= $row['seller_id'] ?>">
                                        <div class="align-items-center d-flex flex-wrap gap-2">
                                            <div class="col-md-3">
                                                <div class="">
                                                    <div class="product-image">
                                                        <div class="product-image-container">
                                                            <a href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>">
                                                                <img class="pic-1 lazy product-list-image" src="<?= base_url('assets/front_end/modern/img/product-placeholder.jpg') ?>" data-src="<?= base_url('media/image?path=' . rawurlencode($row['seller_profile_path']) . '&width=800&quality=80') ?>">
                                                                <?php $row['seller_profile']; ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="product-content">
                                                    <h3 class="list-product-title title" title="<?= $row['seller_name'] ?>"><a href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>"><?= $row['seller_name'] ?></a></h3>
                                                    <div class="rating">
                                                        <input id="input" name="rating" class="rating rating-loading d-none" data-size="xs" value="<?= number_format($row['seller_rating'], 1) ?>" data-show-clear="false" data-show-caption="false" readonly>
                                                    </div>
                                                    <p class="text-muted list-product-desc m-0"><?= $row['store_description'] ?></p>
                                                    <div class="d-flex align-items-center gap-3 mb-2">
                                                        <p class="price mb-0 list-view-price">
                                                            <?= $row['store_name'] ?>
                                                        </p>
                                                        <?php if (isset($location_filtering_active) && $location_filtering_active): ?>
                                                            <?php if (isset($row['distance_text']) && !empty($row['distance_text'])): ?>
                                                                <span class="seller-distance-badge badge bg-light text-dark">
                                                                    <i class="uil uil-map-marker text-primary"></i>
                                                                    <span class="distance-value"><?= $row['distance_text'] ?></span>
                                                                    <span class="text-muted"><?= !empty($this->lang->line('away')) ? str_replace('\\', '', $this->lang->line('away')) : 'away' ?></span>
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="seller-distance-badge badge bg-light text-muted">
                                                                    <i class="uil uil-map-marker"></i>
                                                                    <span class="small"><?= !empty($this->lang->line('distance_not_available')) ? str_replace('\\', '', $this->lang->line('distance_not_available')) : 'N/A' ?></span>
                                                                </span>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <a href="<?= base_url('products?seller=' . $row['slug']) ?>" class="view-products  btn btn-sm btn-outline-primary rounded-pill mt-2"><?= !empty($this->lang->line('view_products')) ? str_replace('\\', '', $this->lang->line('view_products')) : 'View Products' ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>

                    <?php } else { ?>

                        <div class="row" id="sellers_grid_container">
                            <?php foreach ($sellers as $row) { ?>
                                <div class="col-md-4 col-6 mt-5 seller-card-item" title="<?= $row['seller_name'] ?>" data-seller-id="<?= $row['seller_id'] ?>">
                                    <div class="card text-center seller-card">
                                        <div class="seller-image-container">
                                            <a href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>">
                                                <img class="pic-1 lazy fig_seller_image" src="<?= base_url('assets/front_end/modern/img/product-placeholder.jpg') ?>" data-src="<?= base_url('media/image?path=' . rawurlencode($row['seller_profile_path']) . '&width=800&quality=80') ?>">
                                            </a>
                                        </div>
                                        <div class="rating">
                                            <input id="input" name="rating" class="rating rating-loading d-none" data-size="xs" value="<?= number_format($row['seller_rating'], 1) ?>" data-show-clear="false" data-show-caption="false" readonly>
                                        </div>
                                        <div class="product-content my-3">
                                            <h4 class="title m-0" title="<?= $row['seller_name'] ?>"><a class="text-decoration-none text-dark" href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>"><?= $row['seller_name'] ?></a></h4>
                                            <p class="price fs-14">
                                                <?= $row['store_name'] ?>
                                            </p>
                                            <?php if (isset($location_filtering_active) && $location_filtering_active): ?>
                                                <?php if (isset($row['distance_text']) && !empty($row['distance_text'])): ?>
                                                    <p class="seller-distance mb-2">
                                                        <i class="uil uil-map-marker text-primary"></i>
                                                        <span class="distance-value"><?= $row['distance_text'] ?></span>
                                                        <span class="text-muted small"><?= !empty($this->lang->line('away')) ? str_replace('\\', '', $this->lang->line('away')) : 'away' ?></span>
                                                    </p>
                                                <?php else: ?>
                                                    <p class="seller-distance mb-2 text-muted">
                                                        <i class="uil uil-map-marker"></i>
                                                        <span class="small"><?= !empty($this->lang->line('distance_not_available')) ? str_replace('\\', '', $this->lang->line('distance_not_available')) : 'Distance N/A' ?></span>
                                                    </p>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                            <a href="<?= base_url('products?seller=' . $row['slug']) ?>" class="view-products btn btn-xs btn-outline-primary rounded-pill"><?= !empty($this->lang->line('view_products')) ? str_replace('\\', '', $this->lang->line('view_products')) : 'View Products' ?></a>

                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                <?php } ?>

                <?php if (!isset($sellers) || empty($sellers)) { ?>
                    <div class="col-12 text-center mt-5">
                        <h1 class="h2"><?= !empty($this->lang->line('no_sellers_found')) ? str_replace('\\', '', $this->lang->line('no_sellers_found')) : 'No Sellers Found.' ?></h1>
                        <a href="<?= base_url('products') ?>" class="btn rounded-pill btn-warning btn-sm"><?= !empty($this->lang->line('go_to_shop')) ? str_replace('\\', '', $this->lang->line('go_to_shop')) : 'Go to Shop' ?></a>
                    </div>
                <?php } ?>
                <nav class="text-center mt-4">
                    <?= (isset($links)) ? $links : '' ?>
                </nav>
            </div>
        </div>
    </div>
</section>

<!-- Location Detection Script -->
<script>
console.log('📍 Location Script Loaded (Modern)');

document.addEventListener('DOMContentLoaded', function() {
    console.log('📍 DOM Ready');
    
    var detectBtn = document.getElementById('detect_location_btn');
    var statusDiv = document.getElementById('location_status');
    var latInput = document.getElementById('user_latitude');
    var lngInput = document.getElementById('user_longitude');
    var radiusSelect = document.getElementById('distance_radius');
    
    console.log('📍 Detect Button:', detectBtn ? 'Found' : 'NOT FOUND');
    console.log('📍 Status Div:', statusDiv ? 'Found' : 'NOT FOUND');
    
    // Check URL params
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('latitude') && urlParams.has('longitude')) {
        console.log('📍 Location from URL - Lat:', urlParams.get('latitude'), 'Lng:', urlParams.get('longitude'));
    }
    
    if (detectBtn) {
        detectBtn.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('📍 Detect Button Clicked');
            
            // Show loading status
            if (statusDiv) {
                statusDiv.style.display = 'block';
                statusDiv.className = 'alert alert-info mt-2';
                statusDiv.innerHTML = '<i class="uil uil-spinner"></i> Detecting your location...';
            }
            
            // Disable button and show loading
            detectBtn.disabled = true;
            detectBtn.innerHTML = '<i class="uil uil-spinner"></i> Detecting...';
            
            if (!navigator.geolocation) {
                console.error('📍 Geolocation NOT supported');
                if (statusDiv) {
                    statusDiv.className = 'alert alert-danger mt-2';
                    statusDiv.innerHTML = '<i class="uil uil-times-circle"></i> Geolocation is not supported by your browser.';
                }
                detectBtn.disabled = false;
                detectBtn.innerHTML = '<i class="uil uil-crosshair"></i> Detect';
                return;
            }
            
            console.log('📍 Requesting location...');
            
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    var lat = position.coords.latitude;
                    var lng = position.coords.longitude;
                    
                    console.log('📍 SUCCESS! Latitude:', lat, 'Longitude:', lng);
                    
                    if (statusDiv) {
                        statusDiv.className = 'alert alert-success mt-2';
                        statusDiv.innerHTML = '<i class="uil uil-check-circle"></i> Location: ' + lat.toFixed(4) + ', ' + lng.toFixed(4) + ' - Redirecting...';
                    }
                    
                    if (latInput) latInput.value = lat;
                    if (lngInput) lngInput.value = lng;
                    
                    // Build redirect URL
                    var url = new URL(window.location.href);
                    url.searchParams.set('latitude', lat);
                    url.searchParams.set('longitude', lng);
                    url.searchParams.set('sort', 'nearest');
                    
                    // Add radius if selected
                    if (radiusSelect && radiusSelect.value) {
                        url.searchParams.set('radius', radiusSelect.value);
                    }
                    
                    console.log('📍 Redirecting to:', url.toString());
                    window.location.href = url.toString();
                },
                function(error) {
                    console.error('📍 ERROR:', error.code, error.message);
                    var msg = '';
                    switch(error.code) {
                        case 1: msg = 'Permission denied. Please allow location access.'; break;
                        case 2: msg = 'Position unavailable.'; break;
                        case 3: msg = 'Request timed out.'; break;
                        default: msg = 'Unknown error: ' + error.message;
                    }
                    if (statusDiv) {
                        statusDiv.className = 'alert alert-danger mt-2';
                        statusDiv.innerHTML = '<i class="uil uil-times-circle"></i> ' + msg;
                    }
                    detectBtn.disabled = false;
                    detectBtn.innerHTML = '<i class="uil uil-crosshair"></i> Detect';
                },
                {
                    enableHighAccuracy: true,
                    timeout: 15000,
                    maximumAge: 0
                }
            );
        });
        
        console.log('📍 Click handler attached');
    }
    
    // Radius change handler
    if (radiusSelect) {
        radiusSelect.addEventListener('change', function() {
            console.log('📍 Radius changed to:', this.value);
            
            // Only reload if we have location
            var urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('latitude') && urlParams.has('longitude')) {
                var url = new URL(window.location.href);
                if (this.value) {
                    url.searchParams.set('radius', this.value);
                } else {
                    url.searchParams.delete('radius');
                }
                console.log('📍 Reloading with radius:', url.toString());
                window.location.href = url.toString();
            }
        });
    }
});
</script>