<?php
/**
 * Settings Feed page html
 *
 * @package PureClarity for WooCommerce
 * @since 3.0.0
 */

/**
 * Dashboard class containing functions used by this view.
 *
 * @var Pureclarity_Dashboard_Page $this
 */

?>
<div id="pureclarity-account-info" class="pureclarity-info-box">
	<div class="pureclarity-info-title">
		<h2>Account Status</h2>
	</div>
	<div class="pureclarity-info-content">
		<?php if ( $dashboard['Account']['IsSignedUp'] === 0 ) : ?>
		<p>You have <?php echo $dashboard['Account']['DaysLeft']; ?> days left of your Free Trial</p>
		<p>Ending on: <?php echo $dashboard['Account']['FreeTrialEndDate'] ?></p>
		<p><a href="">Sign up now</a></p>
		<?php endif; ?>
	</div>
</div>
<style>

</style>