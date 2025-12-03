<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4><?= isset($store_details['id']) ? 'Update' : 'Add' ?> Store</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/home') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= base_url('seller/store') ?>">Stores</a></li>
                        <li class="breadcrumb-item active"><?= isset($store_details['id']) ? 'Update' : 'Add' ?> Store</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info">
                        <form class="form-horizontal" action="<?= base_url('seller/store/add_store'); ?>" method="POST" enctype="multipart/form-data" id="save-store">
                            <input type="hidden" name="vendor_id" value="<?= $vendor_id ?>">
                            <?php if (isset($store_details['id'])) { ?>
                                <input type="hidden" name="store_id" value="<?= $store_details['id'] ?>">
                            <?php } ?>

                            <div class="card-body">
                                <h5>Store Information</h5>
                                <hr>

                                <div class="form-group row">
                                    <label for="store_name" class="col-sm-2 col-form-label">Store Name <span class='text-danger text-sm'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="store_name" placeholder="Store Name" name="store_name" value="<?= isset($store_details['store_name']) ? $store_details['store_name'] : '' ?>" required>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="store_logo" class="col-sm-2 col-form-label">Store Logo</label>
                                    <div class="col-sm-10">
                                        <?php if (isset($store_details['logo']) && !empty($store_details['logo'])) { ?>
                                            <div class="mb-2">
                                                <img src="<?= base_url($store_details['logo']) ?>" alt="Store Logo" style="max-width: 150px; max-height: 150px;">
                                            </div>
                                        <?php } ?>
                                        <input type="file" class="form-control" name="store_logo" id="store_logo" accept="image/*" />
                                        <small class="text-muted">Recommended size: 300x300px. Max size: 5MB</small>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="store_description" class="col-sm-2 col-form-label">Store Description</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="store_description" placeholder="Store Description" name="store_description" rows="4"><?= isset($store_details['store_description']) ? $store_details['store_description'] : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="store_url" class="col-sm-2 col-form-label">Store URL</label>
                                    <div class="col-sm-10">
                                        <input type="url" class="form-control" id="store_url" placeholder="https://example.com" name="store_url" value="<?= isset($store_details['store_url']) ? $store_details['store_url'] : '' ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="categories" class="col-sm-2 col-form-label">Categories</label>
                                    <div class="col-sm-10">
                                        <?php
                                        $selected_categories = [];
                                        if (isset($store_details['category_ids']) && !empty($store_details['category_ids'])) {
                                            $selected_categories = explode(',', $store_details['category_ids']);
                                        }
                                        ?>
                                        <select multiple="multiple" name="categories[]" id="store-category-field" class="form-control">
                                            <?php
                                            if (isset($categories) && !empty($categories)) {
                                                foreach ($categories as $category) {
                                                    $selected = in_array($category['id'], $selected_categories) ? 'selected' : '';
                                                    $prefix = ($category['parent_id'] == "0") ? '' : "-- ";
                                            ?>
                                                    <option value="<?= $category["id"] ?>" <?= $selected ?>>
                                                        <?= $prefix . $category["name"] ?>
                                                    </option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                </div>

                                <h5 class="mt-4">Service Area</h5>
                                <hr>

                                <div class="form-group row">
                                    <label for="serviceable_zipcodes" class="col-sm-2 col-form-label">Serviceable Zipcodes</label>
                                    <div class="col-sm-10">
                                        <?php
                                        $selected_zipcodes = [];
                                        if (isset($store_details['serviceable_zipcodes']) && !empty($store_details['serviceable_zipcodes'])) {
                                            $selected_zipcodes = explode(',', $store_details['serviceable_zipcodes']);
                                        }
                                        ?>
                                        <select name="serviceable_zipcodes[]" class="search_zipcode form-control w-100" multiple id="serviceable_zipcodes">
                                            <?php
                                            if (!empty($selected_zipcodes)) {
                                                $zipcodes_name = fetch_details('zipcodes', "", 'zipcode,id', "", "", "", "", "id", $selected_zipcodes);
                                                foreach ($zipcodes_name as $row) {
                                            ?>
                                                    <option value="<?= $row['id'] ?>" selected><?= $row['zipcode'] ?></option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="serviceable_cities" class="col-sm-2 col-form-label">Serviceable Cities</label>
                                    <div class="col-sm-10">
                                        <?php
                                        $selected_city_ids = [];
                                        if (isset($store_details['serviceable_cities']) && !empty($store_details['serviceable_cities'])) {
                                            $selected_city_ids = explode(',', $store_details['serviceable_cities']);
                                        }
                                        ?>
                                        <select class="form-control city_list w-100" name="serviceable_cities[]" id="serviceable_cities" multiple>
                                            <?php
                                            if (isset($cities) && !empty($cities)) {
                                                foreach ($cities as $row) {
                                                    $selected = in_array($row['id'], $selected_city_ids) ? 'selected' : '';
                                            ?>
                                                    <option value="<?= $row['id'] ?>" <?= $selected ?>><?= $row['name'] ?></option>
                                            <?php }
                                            } ?>
                                        </select>
                                    </div>
                                </div>

                                <h5 class="mt-4">SEO Settings</h5>
                                <hr>

                                <div class="form-group row">
                                    <label for="seo_page_title" class="col-sm-2 col-form-label">SEO Page Title</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="seo_page_title" placeholder="SEO Page Title" name="seo_page_title" value="<?= isset($store_details['seo_page_title']) ? $store_details['seo_page_title'] : '' ?>">
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="seo_meta_description" class="col-sm-2 col-form-label">SEO Meta Description</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="seo_meta_description" placeholder="SEO Meta Description" name="seo_meta_description" rows="3"><?= isset($store_details['seo_meta_description']) ? $store_details['seo_meta_description'] : '' ?></textarea>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="seo_meta_keywords" class="col-sm-2 col-form-label">SEO Meta Keywords</label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="seo_meta_keywords" placeholder="keyword1, keyword2, keyword3" name="seo_meta_keywords" value="<?= isset($store_details['seo_meta_keywords']) ? $store_details['seo_meta_keywords'] : '' ?>">
                                        <small class="text-muted">Separate keywords with commas</small>
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <div class="col-sm-10 offset-sm-2">
                                        <button type="submit" class="btn btn-primary"><?= isset($store_details['id']) ? 'Update' : 'Add' ?> Store</button>
                                        <a href="<?= base_url('seller/store') ?>" class="btn btn-default">Cancel</a>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(document).ready(function() {
        // Initialize select2 for categories
        $('#store-category-field').select2({
            placeholder: 'Select categories',
            allowClear: true
        });

        // Initialize select2 for zipcodes
        $('.search_zipcode').select2({
            placeholder: 'Select zipcodes',
            allowClear: true,
            ajax: {
                url: '<?= base_url('seller/area/get_zipcodes') ?>',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        search: params.term,
                        page: params.page || 1
                    };
                },
                processResults: function(data) {
                    return {
                        results: data.data
                    };
                },
                cache: true
            }
        });

        // Initialize select2 for cities
        $('.city_list').select2({
            placeholder: 'Select cities',
            allowClear: true
        });

        // Form submission
        $('#save-store').on('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            
            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(result) {
                    if (result.error == false) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: result.message,
                            showConfirmButton: false,
                            timer: 2000
                        });
                        setTimeout(function() {
                            window.location.href = '<?= base_url('seller/store') ?>';
                        }, 2000);
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: result.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'An error occurred. Please try again.'
                    });
                }
            });
        });
    });
</script>

