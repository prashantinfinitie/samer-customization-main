<div class="card">
    <div class="card-header">
        <h5 class="card-title">Assigned Zipcodes</h5>
    </div>
    <div class="card-body">
        <?php if(isset($assigned_zipcodes) && !empty($assigned_zipcodes)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Zipcode</th>
                            <th>City</th>
                            <th>Assigned Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($assigned_zipcodes as $zipcode): ?>
                            <tr>
                                <td>
                                    <strong><?php echo isset($zipcode['zipcode']) ? $zipcode['zipcode'] : 'N/A'; ?></strong>
                                </td>
                                <td><?php echo isset($zipcode['city']) ? $zipcode['city'] : 'N/A'; ?></td>
                                <td><?php echo isset($zipcode['created_at']) ? date('d-m-Y', strtotime($zipcode['created_at'])) : 'N/A'; ?></td>
                                <td>
                                    <span class="badge bg-success">Active</span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                No zipcodes assigned yet. Contact admin to assign service areas.
            </div>
        <?php endif; ?>
    </div>
</div>