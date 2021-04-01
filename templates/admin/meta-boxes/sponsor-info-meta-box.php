<?php

/**
 * @var $b4b_sponsor_subtitle string
 * @var $b4b_sponsor_url string
 */

?>
<table class="form-table">
    <tr>
        <th>
            <label for="b4b_sponsor_subtitle" class="b4b_sponsor_subtitle_label"><?php echo __( 'Subtitle', B4B_TEXT_DOMAIN ) ?></label>
        </th>
        <td>
            <input type="text" id="b4b_sponsor_subtitle" name="b4b_sponsor_subtitle" class="b4b_sponsor_subtitle_field regular-text"
                   placeholder="<?php echo esc_attr__( '', B4B_TEXT_DOMAIN ) ?>"
                   value="<?php echo esc_attr__( $b4b_sponsor_subtitle ) ?>">
        </td>
    </tr>

    <tr>
        <th>
            <label for="b4b_sponsor_url" class="b4b_sponsor_url_label"><?php echo __( 'URL', B4B_TEXT_DOMAIN ) ?></label>
        </th>
        <td>
            <input type="text" id="b4b_sponsor_url" name="b4b_sponsor_url" class="b4b_sponsor_url_field regular-text"
                   placeholder="<?php echo esc_attr__( '', B4B_TEXT_DOMAIN ) ?>"
                   value="<?php echo esc_attr__( $b4b_sponsor_url ) ?>">
        </td>
    </tr>

</table>