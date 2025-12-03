<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h4>Terms & Conditions</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= base_url('shipping-company/home') ?>">Home</a></li>
                        <li class="breadcrumb-item active">Terms & Conditions</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-info p-5">
                        <?php if (!empty($terms_n_condition)) : ?>
                            <div class="terms-content">
                                <?= $terms_n_condition ?>
                            </div>
                        <?php else : ?>
                            <p class="text-muted">No terms and conditions have been set.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

