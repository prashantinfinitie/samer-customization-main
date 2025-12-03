<div class="card">
    <div class="card-header">
        <h5 class="card-title">My Profile</h5>
    </div>
    <div class="card-body">
        <form id="profile_form" method="POST">
            <div class="form-group">
                <label for="username">Company Name</label>
                <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($user->username) ? $user->username : ''; ?>">
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($user->email) ? $user->email : ''; ?>">
            </div>

            <div class="form-group">
                <label for="mobile">Mobile</label>
                <input type="text" class="form-control" id="mobile" name="mobile" value="<?php echo isset($user->mobile) ? $user->mobile : ''; ?>" readonly>
            </div>

            <div class="form-group">
                <label for="address">Address</label>
                <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($user->address) ? $user->address : ''; ?></textarea>
            </div>

            <hr>

            <h6 class="mt-4 mb-3">Change Password</h6>

            <div class="form-group">
                <label for="old_password">Current Password</label>
                <input type="password" class="form-control" id="old_password" name="old" placeholder="Enter current password">
            </div>

            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" class="form-control" id="new_password" name="new" placeholder="Enter new password">
                <small class="form-text text-muted">At least 8 characters with uppercase, lowercase, number, and special character</small>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="new_confirm" placeholder="Confirm new password">
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Profile</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#profile_form').on('submit', function(e) {
        e.preventDefault();
        
        $.ajax({
            url: '<?php echo base_url("shipping_company/login/update_user"); ?>',
            type: 'POST',
            data: $(this).serialize(),
            dataType: 'json',
            success: function(response) {
                if (response.error) {
                    alert('Error: ' + response.message);
                } else {
                    alert('Profile updated successfully!');
                    location.reload();
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
});
</script>