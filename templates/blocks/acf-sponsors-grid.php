<?php

/**
 * @var array $sponsors
 */

?>
<section class="sponsors wrapper">
    <h3><span>A Special Thanks To Our Sponsors</span></h3>

    <div>
		<?php foreach ( $sponsors as $sponsor ): ?>
            <article>
				<?php if ( empty( $sponsor["logo_url"] ) === false ): ?>
                    <figure>
                        <span style="background-image: url(<?php echo $sponsor["logo_url"] ?>)"></span>
                    </figure>
				<?php endif ?>
                <p><?php echo $sponsor["title"] ?></p>
				<?php if ( empty( $sponsor["website_url"] ) === false ): ?>
                    <p>
                        <a href="<?php echo $sponsor["website_url"] ?>" target="_blank">
							<?php echo $sponsor["subtitle"] ?>
                        </a>
                    </p>
				<?php endif ?>
                <ul class="social">
					<?php if ( empty( $sponsor["facebook_url"] ) === false ): ?>
                        <li class="facebook">
                            <a href="<?php echo $sponsor["facebook_url"] ?>" target="_blank"><i class="fab fa-facebook"></i></a>
                        </li>
					<?php endif ?>
	                <?php if ( empty( $sponsor["instagram_url"] ) === false ): ?>
                        <li class="instagram">
                            <a href="<?php echo $sponsor["instagram_url"] ?>" target="_blank"><i class="fab fa-instagram"></i></a>
                        </li>
	                <?php endif ?>
	                <?php if ( empty( $sponsor["twitter_url"] ) === false ): ?>
                        <li class="twitter">
                            <a href="<?php echo $sponsor["twitter_url"] ?>" target="_blank"><i class="fab fa-twitter-square"></i></a>
                        </li>
	                <?php endif ?>
	                <?php if ( empty( $sponsor["linkedin_url"] ) === false ): ?>
                        <li class="linkedin">
                            <a href="<?php echo $sponsor["linkedin_url"] ?>" target="_blank"><i class="fab fa-linkedin"></i></a>
                        </li>
	                <?php endif ?>
                </ul>
            </article>
		<?php endforeach ?>
    </div>
</section>