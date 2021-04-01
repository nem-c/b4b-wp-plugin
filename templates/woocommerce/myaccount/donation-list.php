<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * @var $min_year integer
 * @var $max_year integer
 * @var $selected_year string
 * @var $donations_list array
 * @var $donations_goal float
 * @var $donations_collected float
 * @var $donation_link string
 */

$user = wp_get_current_user();
$role = current($user->roles);

?>

<?php if (in_array($role, ["administrator", "b4b_event_manager"])) : ?>

    <p class="redirect-msg-user">
        You will be redirected to administration panel in next 3 seconds <img src="<?php echo get_stylesheet_directory_uri() ?>/assets/img/loading.gif">
    </p>
    <script type="text/javascript">
        setTimeout(function() {
            window.location = "/wp-admin";
        }, 3000);
    </script>

    <?php return;
endif ?>

<p class="filter-label">Filter donations by year:</p>

<div class="filters-wrapper">
    <ul class="filter-years">
        <li class="<?php echo (sanitize_text_field($_REQUEST["show"])) === "all" ? "active" : "" ?>">
                <a href=" ?show=all">All</a>
        </li>
        <?php for ($year = $max_year; $year >= $min_year; $year--) : ?>
            <li class="<?php echo ($selected_year === $year) ? "active" : "" ?>">
                <a href="?filter=<?php echo $year ?>"><?php echo $year ?></a>
            </li>
        <?php endfor ?>
    </ul>    
</div>


<?php if ($selected_year > 0) : ?>
    <div class="goal-wrapper">
        <h2>Goal in <?php echo $selected_year ?></h2>

        <?php if (empty($donation_link) === false) : ?>
            <div class="promotion-link-holder">
                <input id="promotion-link" type="text" value="<?php echo $donation_link ?>">
                <button onclick="copy_link()">Copy Link</button>
                <script type="text/javascript">
                    function copy_link() {
                        var copyText = document.getElementById("promotion-link");
                        copyText.select();
                        document.execCommand("copy");
                    }
                </script>
            </div>
        <?php endif ?>
    </div>

    <table class="wp-list-table widefat striped goal-table responsive">
        <thead>
            <tr>
                <th>Donations</th>
                <th>Difference</th>
                <th>Goal</th>
            </tr>
        </thead>
        <tbody>
            <tr class="wp-list-table__row is-ext-header">
                <td class="wp-list-table__ext-actions">
                    <?php echo wc_price($donations_collected) ?>
                </td>
                <td class="wp-list-table__ext-actions <?php echo (($donations_collected - $donations_goal) < 0) ? "negative" : "" ?>">
                    <?php echo wc_price($donations_collected - $donations_goal) ?>
                </td>
                <td class="wp-list-table__ext-details">
                    <?php echo wc_price($donations_goal) ?>
                </td>
            </tr>
        </tbody>
    </table>
<?php endif ?>

<h2>
    All donations <?php echo ($selected_year > 0) ? "in $selected_year" : "from $min_year to $max_year" ?>
</h2>
<?php if (count($donations_list) === 0) : ?>
    <p>No donations in <?php echo $selected_year ?></p>
<?php else : ?>
    <table class="wp-list-table widefat striped list-donation-table responsive">
        <thead>
            <tr>
                <th>Date</th>
                <th>Donor Name</th>
                <th>Donor Message</th>
                <th>Donation Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($donations_list as $donation) : ?>
                <tr class="wp-list-table__row is-ext-header">
                    <td class="wp-list-table__ext-details">
                        <?php echo date_i18n(get_option("date_format"), strtotime($donation["date"])) ?>
                    </td>
                    <td class="wp-list-table__ext-actions">
                        <?php echo $donation["donor_name"] ?>
                    </td>
                    <td class="wp-list-table__ext-actions">
                        <div class="cut-block">
                            <?php echo $donation["message"] ?>
                        </div>
                    </td>
                    <td class="wp-list-table__ext-actions">
                        <?php echo wc_price($donation["amount"]) ?>
                    </td>
                </tr>
            <?php endforeach ?>
        </tbody>
    </table>
<?php endif ?>