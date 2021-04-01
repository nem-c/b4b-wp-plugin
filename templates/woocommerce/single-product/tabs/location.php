<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * @var $maps_api_key string
 * @var $maps_address string
 */

global $product;

?>

<div class="g-map-frame">
    <iframe
        width="600"
        height="450"
        frameborder="0" style="border:0"
        src="https://www.google.com/maps/embed/v1/place?key=<?php echo $maps_api_key ?>&q=<?php echo $maps_address ?>" allowfullscreen>
    </iframe>
</div>
