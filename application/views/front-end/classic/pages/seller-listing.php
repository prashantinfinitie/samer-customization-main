<!-- breadcrumb -->
<section class="breadcrumb-title-bar colored-breadcrumb deeplink_wrapper">
    <div class="main-content responsive-breadcrumb">
        <h2><?= isset($page_main_bread_crumb) ? $page_main_bread_crumb : 'Product Listing' ?></h2>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= base_url() ?>"><?= !empty($this->lang->line('home')) ? str_replace('\\', '', $this->lang->line('home')) : 'Home' ?></a></li>
                <?php if (isset($right_breadcrumb) && !empty($right_breadcrumb)) {
                    foreach ($right_breadcrumb as $row) {
                ?>
                        <li class="breadcrumb-item"><?= $row ?></li>
                <?php }
                } ?>
                <li class="breadcrumb-item active" aria-current="page"><?= !empty($this->lang->line('sellers')) ? str_replace('\\', '', $this->lang->line('sellers')) : 'Sellers' ?></li>
            </ol>
        </nav>
    </div>

</section>
<!-- end breadcrumb -->
<section class="listing-page content main-content">
    <div class="product-listing card-solid py-4">
        <div class="row mx-0">
            <!-- Dektop Sidebar -->
            <!-- remved filters -->
            <div class="col-md-12 order-md-2">
                
                <!-- Location-Based Seller Discovery Section -->
                <div class="container-fluid location-discovery-section mb-3">
                    <div class="card location-card">
                        <div class="card-body p-3">
                            <div class="row align-items-center">
                                <div class="col-md-6">
                                    <div class="location-selector d-flex align-items-center">
                                        <i class="fa fa-map-marker-alt fa-lg text-primary mr-2"></i>
                                        <div class="location-info flex-grow-1">
                                            <small class="text-muted d-block"><?= !empty($this->lang->line('your_location')) ? str_replace('\\', '', $this->lang->line('your_location')) : 'Your Location' ?></small>
                                            <span id="current_location_text" class="font-weight-bold">
                                                <?php if (isset($location_filtering_active) && $location_filtering_active): ?>
                                                    <?= !empty($this->lang->line('location_set')) ? str_replace('\\', '', $this->lang->line('location_set')) : 'Location set' ?>
                                                <?php else: ?>
                                                    <?= !empty($this->lang->line('set_your_location')) ? str_replace('\\', '', $this->lang->line('set_your_location')) : 'Set your location to find nearby sellers' ?>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <button type="button" id="detect_location_btn" class="btn btn-sm btn-outline-primary ml-2" title="<?= !empty($this->lang->line('detect_location')) ? str_replace('\\', '', $this->lang->line('detect_location')) : 'Detect my location' ?>">
                                            <i class="fa fa-crosshairs"></i>
                                            <span class="d-none d-md-inline ml-1"><?= !empty($this->lang->line('detect')) ? str_replace('\\', '', $this->lang->line('detect')) : 'Detect' ?></span>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-2 mt-md-0">
                                    <div class="radius-selector d-flex align-items-center">
                                        <label class="mr-2 text-muted text-nowrap mb-0"><?= !empty($this->lang->line('within')) ? str_replace('\\', '', $this->lang->line('within')) : 'Within' ?>:</label>
                                        <select id="distance_radius" class="form-control form-control-sm">
                                            <option value=""><?= !empty($this->lang->line('any_distance')) ? str_replace('\\', '', $this->lang->line('any_distance')) : 'Any Distance' ?></option>
                                            <option value="5" <?= (isset($search_radius) && $search_radius == 5) ? 'selected' : '' ?>>5 km</option>
                                            <option value="10" <?= (isset($search_radius) && $search_radius == 10) ? 'selected' : '' ?>>10 km</option>
                                            <option value="25" <?= (isset($search_radius) && $search_radius == 25) ? 'selected' : '' ?>>25 km</option>
                                            <option value="50" <?= (isset($search_radius) && $search_radius == 50) ? 'selected' : '' ?>>50 km</option>
                                            <option value="100" <?= (isset($search_radius) && $search_radius == 100) ? 'selected' : '' ?>>100 km</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 mt-2 mt-md-0 text-right">
                                    <?php if (isset($location_filtering_active) && $location_filtering_active): ?>
                                        <button type="button" id="clear_location_btn" class="btn btn-sm btn-outline-secondary">
                                            <i class="fa fa-times"></i>
                                            <span class="d-none d-md-inline ml-1"><?= !empty($this->lang->line('clear')) ? str_replace('\\', '', $this->lang->line('clear')) : 'Clear' ?></span>
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
                
                <div class="container-fluid filter-section pt-3 pb-3">
                    <div class="col-12 pl-0">
                        <div class="dropdown">
                            <div class="filter-bars d-none">
                                <div class="menu js-menu">
                                    <span class="menu__line"></span>
                                    <span class="menu__line"></span>
                                    <span class="menu__line"></span>

                                </div>
                            </div>
                            <?php if (isset($sellers) && !empty($sellers)) { ?>
                                <div class="dropdown float-md-right d-flex mb-4">
                                    <label class="mr-2 dropdown-label"> <?= !empty($this->lang->line('show')) ? str_replace('\\', '', $this->lang->line('show')) : 'Show' ?>:</label>
                                    <a class="btn dropdown-border btn-lg dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><?= ($this->input->get('per-page', true) ? $this->input->get('per-page', true) : '12') ?> <span class="caret"></span></a>
                                    <a href="#" id="product_grid_view_btn" class="grid-view"><i class="fas fa-th"></i></a>
                                    <a href="#" id="product_list_view_btn" class="grid-view"><i class="fas fa-th-list"></i></a>
                                    <div class="dropdown-menu custom-dropdown-menu" aria-labelledby="navbarDropdown" id="per_page_sellers">
                                        <a class="dropdown-item" href="#" data-value=12>12</a>
                                        <a class="dropdown-item" href="#" data-value=16>16</a>
                                        <a class="dropdown-item" href="#" data-value=20>20</a>
                                        <a class="dropdown-item" href="#" data-value=24>24</a>
                                    </div>
                                </div>
                                <div class="ele-wrapper d-flex ">
                                    <div class="form-group col-md-4 d-flex pl-0">
                                        <label for="product_sort_by"></label>
                                        <select id="product_sort_by" class="form-control">
                                            <option><?= !empty($this->lang->line('relevance')) ? str_replace('\\', '', $this->lang->line('relevance')) : 'Relevance' ?></option>
                                            <option value="top-rated" <?= ($this->input->get('sort') == "top-rated") ? 'selected' : '' ?>><?= !empty($this->lang->line('top_rated')) ? str_replace('\\', '', $this->lang->line('top_rated')) : 'Top Rated' ?></option>
                                            <option value="nearest" <?= ($this->input->get('sort') == "nearest") ? 'selected' : '' ?>><?= !empty($this->lang->line('nearest_first')) ? str_replace('\\', '', $this->lang->line('nearest_first')) : 'Nearest First' ?></option>
                                            <option value="date-desc" <?= ($this->input->get('sort') == "date-desc") ? 'selected' : '' ?>><?= !empty($this->lang->line('newest_first')) ? str_replace('\\', '', $this->lang->line('newest_first')) : 'Newest First' ?></option>
                                            <option value="date-asc" <?= ($this->input->get('sort') == "date-asc") ? 'selected' : '' ?>><?= !empty($this->lang->line('oldest_first')) ? str_replace('\\', '', $this->lang->line('oldest_first')) : 'Oldest First' ?></option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-5 d-flex">
                                        <label for="seller_search"></label>
                                        <input type="search" name="seller_search" class="form-control" id="seller_search" value="<?= (isset($seller_search) && !empty($seller_search)) ? $seller_search : "" ?>" placeholder="<?= !empty($this->lang->line('search_seller')) ? str_replace('\\', '', $this->lang->line('search_seller')) : 'Search Seller' ?>">
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php if (isset($sellers) && !empty($sellers)) { ?>

                        <?php if (isset($_GET['type']) && $_GET['type'] == "list") { ?>
                            <div class="col-md-12 col-sm-6">
                                <div class="col-12">
                                    <h1 class="h4"><?= !empty($this->lang->line('sellers')) ? str_replace('\\', '', $this->lang->line('sellers')) : 'Sellers' ?></h4>
                                </div>
                                <div class="d-flex flex-column mt-4">
                                    <?php foreach ($sellers as $row) { ?>
                                        <div class="d-flex mb-2">
                                            <div class="col-md-3">
                                                <div class="product-grid padding-zero">
                                                    <div class="product-image">
                                                        <div class="product-image-container">
                                                            <a href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>">
                                                                <img class="pic-1 lazy" data-src="<?= base_url('media/image?path=' . rawurlencode($row['seller_profile_path']) . '&width=320&quality=80') ?>">
                                                                <?php $row['seller_profile']; ?>
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="product-content">
                                                    <h3 class="list-product-title title"><a href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>"><?= $row['seller_name'] ?></a></h3>
                                                    <div class="rating">
                                                        <input type="text" class="kv-fa rating-loading" value="<?= number_format($row['seller_rating'], 1) ?>" data-size="sm" title="" readonly>
                                                    </div>
                                                    <p class="text-muted list-product-desc"><?= $row['store_description'] ?></p>
                                                    <div class="price mb-2 list-view-price">
                                                        <?= $row['store_name'] ?>
                                                    </div>
                                                    <div class="button button-sm m-0 p-0">
                                                        <a class="add-to-cart view-products" href="<?= base_url('products?seller=' . $row['slug']) ?>"><?= !empty($this->lang->line('view_products')) ? str_replace('\\', '', $this->lang->line('view_products')) : 'View Products' ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                        <?php } else { ?>
                            <div class="">
                                <h1 class="h4"><?= !empty($this->lang->line('sellers')) ? str_replace('\\', '', $this->lang->line('sellers')) : 'Sellers' ?></h4>
                            </div>
                            <div class="d-flex flex-wrap" id="sellers_grid_container">
                                <?php foreach ($sellers as $row) {
                                ?>
                                    <div class="col-md-3 col-sm-6 mb-4 seller-card-item" data-seller-id="<?= $row['seller_id'] ?>">
                                        <div class="product-grid seller-card">
                                            <div class="product-image">
                                                <div class="product-image-container">
                                                    <a href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>">
                                                        <img class="pic-1 lazy" data-src="<?= base_url('media/image?path=' . rawurlencode($row['seller_profile_path']) . '&width=320&quality=80') ?>">
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="rating">
                                                <input type="text" class="kv-fa rating-loading" value="<?= number_format($row['seller_rating'], 1) ?>" data-size="sm" title="" readonly>
                                            </div>
                                            <div class="product-content">
                                                <h3 class="title"><a href="<?= base_url('sellers/seller_details/' . $row['slug']) ?>"><?= $row['seller_name'] ?></a></h3>
                                                <div class="price mb-2">
                                                    <?= $row['store_name'] ?>
                                                </div>
                                                <?php if (isset($location_filtering_active) && $location_filtering_active): ?>
                                                    <?php if (isset($row['distance_text']) && !empty($row['distance_text'])): ?>
                                                        <p class="seller-distance mb-2">
                                                            <i class="fa fa-map-marker-alt text-primary"></i>
                                                            <span class="distance-value"><?= $row['distance_text'] ?></span>
                                                            <span class="text-muted small"><?= !empty($this->lang->line('away')) ? str_replace('\\', '', $this->lang->line('away')) : 'away' ?></span>
                                                        </p>
                                                    <?php else: ?>
                                                        <p class="seller-distance mb-2 text-muted">
                                                            <i class="fa fa-map-marker-alt"></i>
                                                            <span class="small"><?= !empty($this->lang->line('distance_not_available')) ? str_replace('\\', '', $this->lang->line('distance_not_available')) : 'Distance not available' ?></span>
                                                        </p>
                                                    <?php endif; ?>
                                                <?php endif; ?>
                                                <a class="add-to-cart view-products" href="<?= base_url('products?seller=' . $row['slug']) ?>"><?= !empty($this->lang->line('view_products')) ? str_replace('\\', '', $this->lang->line('view_products')) : 'View Products' ?></a>
                                            </div>
                                        </div>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } ?>

                    <?php if (!isset($sellers) || empty($sellers)) { ?>
                        <div class="col-12 text-center">
                            <h1 class="h2"><?= !empty($this->lang->line('no_sellers_found')) ? str_replace('\\', '', $this->lang->line('no_sellers_found')) : 'No Sellers Found.' ?></h1>
                            <a href="<?= base_url('products') ?>" class="button button-rounded button-warning"><?= !empty($this->lang->line('go_to_shop')) ? str_replace('\\', '', $this->lang->line('go_to_shop')) : 'Go to Shop' ?></a>
                        </div>
                    <?php } ?>
                    <nav class="text-center mt-4">
                        <?= (isset($links)) ? $links : '' ?>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Location Detection Script -->
<script>
console.log('📍 Location Script Loaded');

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
                statusDiv.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Detecting your location...';
            }
            
            // Disable button and show loading
            detectBtn.disabled = true;
            detectBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Detecting...';
            
            if (!navigator.geolocation) {
                console.error('📍 Geolocation NOT supported');
                if (statusDiv) {
                    statusDiv.className = 'alert alert-danger mt-2';
                    statusDiv.innerHTML = '<i class="fa fa-times-circle"></i> Geolocation is not supported by your browser.';
                }
                detectBtn.disabled = false;
                detectBtn.innerHTML = '<i class="fa fa-crosshairs"></i> Detect';
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
                        statusDiv.innerHTML = '<i class="fa fa-check-circle"></i> Location: ' + lat.toFixed(4) + ', ' + lng.toFixed(4) + ' - Redirecting...';
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
                        statusDiv.innerHTML = '<i class="fa fa-times-circle"></i> ' + msg;
                    }
                    detectBtn.disabled = false;
                    detectBtn.innerHTML = '<i class="fa fa-crosshairs"></i> Detect';
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