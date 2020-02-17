
<p><i><a href="<?php echo admin_url('admin.php?page=wp2static-zip'); ?>">Refresh page</a> to see latest status</i><p>

<table class="widefat striped">
    <thead>
        <tr>
            <th>Zip Size</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <?php if ( $view['zip_path'] ) : ?>
                    <?php echo $view['zip_size']; ?>
                <?php else: ?>
                    No ZIP found.
                <?php endif; ?>
            </td>
            <td>
                <?php if ( $view['zip_path'] ) : ?>
                    <?php echo $view['zip_created']; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if ( $view['zip_path'] ) : ?>
                    <a href="<?php echo $view['zip_url']; ?>"><button class="button btn-danger">Download ZIP</button></a>
                    <a href="#"><button class="button btn-danger">Delete ZIP</button></a>
                <?php endif; ?>

            </td>
        </tr>
    </tbody>
</table>

