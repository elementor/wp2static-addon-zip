<?php
// phpcs:disable Generic.Files.LineLength.MaxExceeded                              
// phpcs:disable Generic.Files.LineLength.TooLong                                  

/**
 * @var mixed[] $view
 */
?>

<p><i><a href="<?php echo admin_url( 'admin.php?page=wp2static-zip' ); ?>">Refresh page</a> to see latest status</i><p>

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
                <?php else : ?>
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
                    <a style="float:left;margin-right:10px;" href="<?php echo $view['zip_url']; ?>"><button class="button btn-danger">Download ZIP</button></a>

<form
    name="wp2static-zip-delete"
    method="POST"
    action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

                    <?php wp_nonce_field( $view['nonce_action'] ); ?>
    <input name="action" type="hidden" value="wp2static_zip_delete" />

    <button class="button btn-danger">Delete ZIP</button>
</form>
                <?php endif; ?>

            </td>
        </tr>
    </tbody>
</table>

