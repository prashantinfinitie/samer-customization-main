<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Profile</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('shipping-company/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Profile</li>
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
                        <div class="d-flex form-group justify-content-center profile_image mt-3">
                            <?php if (!empty($users->image)) { ?>
                                <img class="avatar" src="<?= base_url(rawurlencode($users->image)) ?>" alt="Profile Image" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                            <?php } else { ?>
                                <img class="avatar" src="<?= base_url() . NO_USER_IMAGE ?>" alt="Profile Image" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover;">
                            <?php } ?>
                        </div>
                        <!-- form start -->
                        <form class="form-submit-event" action="<?= base_url('shipping-company/login/update_user') ?>" method="POST">
                            <input type="hidden" name="old_profile_image" value="<?= $users->image ?>">

                            <div class="card-body">
                                <div class="form-group row">
                                    <label for="image" class="col-sm-2 col-form-label">Profile Image</label>
                                    <div class="col-sm-10">
                                        <input type="file" class="form-control" name="image" id="image" accept="image/*" />
                                    </div>
                                </div>

                                <div class="form-group row">
                                    <label for="username" class="col-sm-2 col-form-label">Company Name <span class='text-danger text-xs'>*</span></label>
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" id="username" placeholder="Enter company name" name="username" value="<?= $users->username ?>">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <?php if ($identity_column == 'email') { ?>
                                        <label for="email" class="col-sm-2 col-form-label">Email <span class='text-danger text-xs'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="email" placeholder="Enter email" name="email" value="<?= $users->email ?>">
                                        </div>
                                    <?php } else { ?>
                                        <label for="mobile" class="col-sm-2 col-form-label">Mobile <span class='text-danger text-xs'>*</span></label>
                                        <div class="col-sm-10">
                                            <input type="text" maxlength="16" oninput="validateNumberInput(this)" class="form-control" id="mobile" placeholder="Enter mobile number" name="mobile" value="<?= $users->mobile ?>">
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="form-group row">
                                    <label for="address" class="col-sm-2 col-form-label">Address</label>
                                    <div class="col-sm-10">
                                        <textarea class="form-control" id="address" name="address" rows="2" placeholder="Enter address"><?= isset($users->address) ? $users->address : '' ?></textarea>
                                    </div>
                                </div>

                                <hr>
                                <h5 class="mb-3">Change Password</h5>

                                <div class="form-group row">
                                    <label for="old" class="col-sm-2 col-form-label">Old Password</label>
                                    <div class="input-group col-sm-10">
                                        <input type="password" class="form-control form-input passwordToggle" name="old" id="old" placeholder="Enter old password" value="">
                                        <span class="input-group-text togglePassword" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="new" class="col-sm-2 col-form-label">New Password</label>
                                    <div class="input-group col-sm-10">
                                        <input type="password" class="form-control form-input passwordToggle" name="new" id="new" placeholder="Enter new password" value="">
                                        <span class="input-group-text togglePassword" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="new_confirm" class="col-sm-2 col-form-label">Confirm New Password</label>
                                    <div class="input-group col-sm-10">
                                        <input type="password" class="form-control form-input passwordToggle" name="new_confirm" id="new_confirm" placeholder="Confirm new password" value="">
                                        <span class="input-group-text togglePassword" style="cursor: pointer;">
                                            <i class="fa fa-eye"></i>
                                        </span>
                                    </div>
                                </div>
                                <p class="text-muted small">Password must be at least 8 characters with uppercase, lowercase, number and special character.</p>

                                <div class="form-group mt-4">
                                    <button type="reset" class="btn btn-warning">Reset</button>
                                    <button type="submit" class="btn btn-success" id="submit_btn">Update Profile</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

