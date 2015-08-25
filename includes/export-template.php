<?php
/**
 * WordPress Export+ Administration Screen
 *
 * @package WordPress
 * @subpackage Administration
 */

if ( !current_user_can('export') ) {
	wp_die(__('You do not have sufficient permissions to export the content of this site.'));
}

/**
 * Create the date options fields for exporting a given post type.
 *
 * @global wpdb      $wpdb      WordPress database object.
 * @global WP_Locale $wp_locale Date and Time Locale object.
 *
 * @since 3.1.0
 *
 * @param string $post_type The post type. Default 'post'.
 */
function export_plus_date_options( $post_type = 'post' ) {
	global $wpdb, $wp_locale;

	$start = $wpdb->get_results( $wpdb->prepare( "
		SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month, DAY( post_date ) AS day
		FROM $wpdb->posts
		WHERE post_type = %s AND post_status != 'auto-draft'
		ORDER BY post_date ASC
		LIMIT 1
	", $post_type ) );

	if ( !$start )
		return;

	$start_date = array(
		'start_year' => $start[0]->year,
		'start_month' => zeroise( $start[0]->month, 2 ),
		'start_day' => zeroise( $start[0]->day, 2 )
	);

	return $start_date;
}

$start_date = export_plus_date_options();

var_dump( $start_date );

?>

<script type="text/javascript">
	var start_year = <?php echo $start_date['start_year']; ?>;
	var start_month = <?php echo $start_date['start_month'] - 1; ?>;
	var start_day = <?php echo $start_date['start_day']; ?>;

	jQuery(document).ready(function($){
			$('#post-start-date-picker').datepicker({
			dateFormat: 'yy-mm-dd',
			minDate: new Date(start_year, start_month, start_day),
			maxDate: 0,
      		hideIfNoPrevNext: true
		});
		$('#post-end-date-picker').datepicker({
			dateFormat: 'yy-mm-dd',
			minDate: new Date(start_year, start_month, start_day),
			maxDate: 0,
			hideIfNoPrevNext: true
		});
	});
</script>

<div class="wrap">
<h2><?php _e( 'Export Plus', 'export-plus' ); ?></h2>

<p><?php _e('When you click the button below WordPress will create an XML file for you to save to your computer.'); ?></p>
<p><?php _e('This format, which we call WordPress eXtended RSS or WXR, will contain your posts, pages, comments, custom fields, categories, and tags.'); ?></p>
<p><?php _e('Once you&#8217;ve saved the download file, you can use the Import function in another WordPress installation to import the content from this site.'); ?></p>

<h3><?php _e( 'Choose what to export' ); ?></h3>
<form action="" method="get" id="export-filters">
<input type="hidden" name="export_plus_download" value="true" />
<input type="hidden" name="page" value="export_plus" />
<p><label><input type="checkbox" class="selectall" value="all" checked="checked" /> <?php _e( 'Select all', 'export-plus' ); ?></label></p>

<hr/>

<p><label><input type="checkbox" name="content[]" value="menus" checked="checked" /> <?php _e( 'Menus', 'export-plus' ); ?></label></p>

<p><label><input type="checkbox" name="content[]" value="posts" checked="checked" /> <?php _e( 'Posts', 'export-plus' ); ?></label></p>
<ul id="post-filters" class="export-filters">
	<li>
		<label><?php _e( 'Categories:', 'export-plus' ); ?></label>
		<?php wp_dropdown_categories( array( 'show_option_all' => __( 'All', 'export-plus' ) ) ); ?>
	</li>
</ul>

<p><label><input type="checkbox" name="content[]" value="pages" checked="checked" /> <?php _e( 'Pages', 'export-plus' ); ?></label></p>

<?php foreach ( get_post_types( array( '_builtin' => false, 'can_export' => true ), 'objects' ) as $post_type ) : ?>
<p><label><input type="checkbox" name="content[]" value="<?php echo esc_attr( $post_type->name ); ?>" checked="checked" /> <?php echo esc_html( $post_type->label ); ?></label></p>
<?php endforeach; ?>

<h3><?php _e( 'Filter exported content', 'export-plus' ); ?></h3>
<ul class="export-filter">
	<li>
		<label><?php _e( 'Authors:', 'export-plus' ); ?></label>
<?php
		global $wpdb;
		$authors = $wpdb->get_col( "SELECT DISTINCT post_author FROM {$wpdb->posts}" );
		wp_dropdown_users( array( 'include' => $authors, 'name' => 'post_author', 'multi' => true, 'show_option_all' => __( 'All', 'export-plus' ) ) );
?>
	</li>
	<li>
		<?php export_plus_date_options(); ?>

		<label for="post-start-date"><?php _e( 'Start date:', 'export-plus' ); ?></label>
		<input type="text" id="post-start-date-picker" name="post_start_date" />

		<label for="post-end-date"><?php _e( 'End date:', 'export-plus' ); ?></label>
		<input type="text" id="post-end-date-picker" name="post_end_date" />

	</li>
	<li>
		<label><?php _e( 'Status:', 'export-plus' ); ?></label>
		<select name="post_status">
			<option value="0"><?php _e( 'All', 'export-plus' ); ?></option>
			<?php $post_stati = get_post_stati( array( 'internal' => false ), 'objects' );
			foreach ( $post_stati as $status ) : ?>
			<option value="<?php echo esc_attr( $status->name ); ?>"><?php echo esc_html( $status->label ); ?></option>
			<?php endforeach; ?>
		</select>
	</li>
</ul>

<?php
/**
 * Fires after the export filters form.
 *
 * @since 3.5.0
 */
do_action( 'export_filters' );
?>

<?php submit_button( __('Download Export File') ); ?>
</form>
</div>
