<?php
/**
 * The template for displaying the search form.
 *
 * @package ASPA
 */
?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<input type="search" class="search-field" name="s" value="<?php echo get_search_query(); ?>" placeholder="<?php esc_attr_e( 'Search...', 'aspa' ); ?>" aria-label="<?php esc_attr_e( 'Search for:', 'aspa' ); ?>" required />
	<button type="submit" class="search-submit" aria-label="<?php esc_attr_e( 'Search', 'aspa' ); ?>">
		<?php
		if ( function_exists( 'aspa_get_lucide_icon' ) ) {
			echo aspa_get_lucide_icon( 'search' );
		}
		?>
	</button>
</form>
