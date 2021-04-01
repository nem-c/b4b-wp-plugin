<?php

/**
 * @var $b4b_sponsor_social_facebook_url string
 * @var $b4b_sponsor_social_instagram_url string
 * @var $b4b_sponsor_social_twitter_url string
 * @var $b4b_sponsor_social_linkedin_url string
 */

?>
<table class="form-table">
    <tr>
        <th>
            <label for="b4b_sponsor_social_facebook_url" class="b4b_sponsor_social_facebook_url_label"><?php echo __( 'Facebook URL', B4B_TEXT_DOMAIN ) ?></label>
        </th>
        <td>
            <input type="text" id="b4b_sponsor_social_facebook_url" name="b4b_sponsor_social_facebook_url" class="b4b_sponsor_social_facebook_url_field regular-text"
                   placeholder="<?php echo esc_attr__( '', B4B_TEXT_DOMAIN ) ?>"
                   value="<?php echo esc_attr__( $b4b_sponsor_social_facebook_url ) ?>">
        </td>
    </tr>

    <tr>
        <th>
            <label for="b4b_sponsor_social_instagram_url" class="b4b_sponsor_social_instagram_url_label"><?php echo __( 'Instagram URL', B4B_TEXT_DOMAIN ) ?></label>
        </th>
        <td>
            <input type="text" id="b4b_sponsor_social_instagram_url" name="b4b_sponsor_social_instagram_url" class="b4b_sponsor_social_instagram_url_field regular-text"
                   placeholder="<?php echo esc_attr__( '', B4B_TEXT_DOMAIN ) ?>"
                   value="<?php echo esc_attr__( $b4b_sponsor_social_instagram_url ) ?>">
        </td>
    </tr>

    <tr>
        <th>
            <label for="b4b_sponsor_social_twitter_url" class="b4b_sponsor_social_twitter_url_label"><?php echo __( 'Twitter URL', B4B_TEXT_DOMAIN ) ?></label>
        </th>
        <td>
            <input type="text" id="b4b_sponsor_social_twitter_url" name="b4b_sponsor_social_twitter_url" class="b4b_sponsor_social_twitter_url_field regular-text"
                   placeholder="<?php echo esc_attr__( '', B4B_TEXT_DOMAIN ) ?>"
                   value="<?php echo esc_attr__( $b4b_sponsor_social_twitter_url ) ?>">
        </td>
    </tr>

    <tr>
        <th>
            <label for="b4b_sponsor_social_linkedin_url" class="b4b_sponsor_social_linkedin_url_label"><?php echo __( 'LinkedIN URL', B4B_TEXT_DOMAIN ) ?></label>
        </th>
        <td>
            <input type="text" id="b4b_sponsor_social_linkedin_url" name="b4b_sponsor_social_linkedin_url" class="b4b_sponsor_social_linkedin_url_field regular-text"
                   placeholder="<?php echo esc_attr__( '', B4B_TEXT_DOMAIN ) ?>"
                   value="<?php echo esc_attr__( $b4b_sponsor_social_linkedin_url ) ?>">
        </td>
    </tr>

</table>