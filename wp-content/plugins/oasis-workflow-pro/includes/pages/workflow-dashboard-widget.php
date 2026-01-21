<?php
/*
 * Workflow Dashboard Widget
 *
 * @copyright   Copyright (c) 2016, Nugget Solutions, Inc
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.1
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit();
}
?>
<div class="dashboard-content">
	<?php
	$ow_process_flow = new OW_Process_Flow();

	$user_id = get_current_user_id();

	// get date count of assigned tasks for selected user
	$tasks          = $inbox_items = $ow_process_flow->get_assigned_post( null, $user_id, "rows", null );
	$filtered_items = $ow_process_flow->filter_inbox_items( $tasks, "inbox-all" );

	$task_count           = $filtered_items["mineTaskCount"];
	$unclaimed_task_count = $filtered_items["unclaimedTaskCount"];

	// get priority count of assigned tasks for selected user
	$task_list_by_priority = $ow_process_flow->get_task_count_by_priority( $user_id );

	// get unclaimed task count
	$else_unclaimed_task_message = "";
	$unclaimed_task_message      = ".";
	if ( $unclaimed_task_count > 0 ) {
		$else_unclaimed_task_message = esc_html__( ' But there are currently ', 'oasisworkflow' ) .
		                               sprintf( '<a href="admin.php?page=oasiswf-inbox&action=inbox-unclaimed&user=%s">%s',
			                               $user_id, $unclaimed_task_count )
		                               . esc_html__( ' unclaimed task(s).', 'oasisworkflow' )
		                               . '</a>';
		$unclaimed_task_message      = esc_html__( ' and ', 'oasisworkflow' ) .
		                               sprintf( '<a href="admin.php?page=oasiswf-inbox&action=inbox-unclaimed&user=%s">%s',
			                               $user_id, $unclaimed_task_count )
		                               . esc_html__( ' unclaimed task(s).', 'oasisworkflow' )
		                               . '</a>';
	}
	?>

    <div class="main-content">
		<?php
		if ( $task_count > 0 ) {
			$task_count_message = esc_html__( 'There are currently ', 'oasisworkflow' )
			                      . sprintf( '<a href="admin.php?page=oasiswf-inbox">%s', $task_count )
			                      . esc_html__( ' assignment(s)', 'oasisworkflow' )
			                      . '</a>'
			                      . $unclaimed_task_message;
			echo "<span>";
			echo $task_count_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "</span>";
		} else {
			echo "<span>";
			echo esc_html__( 'Hurray! No assignments.', 'oasisworkflow' ) . $else_unclaimed_task_message; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo "</span>";
		}
		?>

		<?php
		$email_settings          = get_option( 'oasiswf_email_settings' );
		$reminder_email_settings = $email_settings['reminder_emails'];
		if ( ( get_option( 'oasiswf_default_due_days' ) != '' ) || ( $reminder_email_settings == 'yes' ) ) : ?>
            <div class="main-content1">
                <span><?php echo esc_html__( "Tasks by Due Date", "oasisworkflow" ); ?></span>
                <ul>
					<?php
					$today    = date( "Y-m-d" ); // phpcs:ignore
					$tomorrow = date( 'Y-m-d', strtotime( '+24 hours' ) ); // phpcs:ignore
					$week     = date( 'Y-m-d', strtotime( '+7 days' ) ); // phpcs:ignore

					//set due date types and if we don't have tasks for the given due dates , show 0 count
					$due_types = array(
						'overdue'           => array(
							'label' => esc_html__( 'Overdue', 'oasisworkflow' ),
							'count' => 0,
							'color' => ''
						),
						'due_today'         => array(
							'label' => esc_html__( 'Due Today', 'oasisworkflow' ),
							'count' => 0,
							'color' => ''
						),
						'due_tomorrow'      => array(
							'label' => esc_html__( 'Due Tomorrow', 'oasisworkflow' ),
							'count' => 0,
							'color' => ''
						),
						'due_in_seven_days' => array(
							'label' => esc_html__( 'Due in 7 days', 'oasisworkflow' ),
							'count' => 0,
							'color' => ''
						)
					);

					// compare dates to get counts for the given tasks by due dates
					foreach ( $tasks as $task ) {
						$date = $task->due_date;
						$key  = '';
						if ( $date < $today ) {
							$key                        = "overdue";
							$due_types[ $key ]["count"] += 1;
						}
						if ( $date == $today ) {
							$key                        = "due_today";
							$due_types[ $key ]["count"] += 1;
						}
						if ( $date == $tomorrow ) {
							$key                        = "due_tomorrow";
							$due_types[ $key ]["count"] += 1;
						}
						if ( $date <= $week ) {
							$key                        = "due_in_seven_days";
							$due_types[ $key ]["count"] += 1;
						}
					}

					foreach ( $due_types as $key => $value ) {

						$css_class = "owf-li-normal";

						// If task count is zero change the css class
						if ( $value['count'] !== 0 ) {
							$css_class = "owf-li-bold";
						}

						// If task is overdue than change text color to red
						if ( $key == "overdue" && $value['count'] !== 0 ) {
							$value['color'] = "red";
						}

						echo "<li class='" . esc_attr( $css_class ) . "' style='color:" . esc_attr( $value['color'] ) .
						     "'>" . esc_html( $value['count'] ) .
						     " " . esc_html( $value['label'] ) . "</li>";

					}
					?>
                </ul>
            </div>
		<?php endif; // check if default due date and reminder emails is enabled ?>

		<?php if ( get_option( 'oasiswf_priority_setting' ) == 'enable_priority' ) : ?>
            <div class="main-content2">
                <span><?php echo esc_html__( "Tasks by Priority", "oasisworkflow" ); ?></span>
                <ul>
					<?php

					$priority_array = array_reverse( OW_Utility::instance()->get_priorities() );

					foreach ( $priority_array as $priority_key => $priority_name ) {
						$has_tasks = false;
						// the CSS is defined without the number part, example, "2normal" is defined as "normal-task-priority"
						$css_class = substr( $priority_key, 1 );

						foreach ( $task_list_by_priority as $priority_task ) {
							if ( $priority_key ===
							     $priority_task->priority ) { //looks like we found count for the given priority
								echo "<li class=" . esc_attr( $css_class ) . "-task-priority>" .
								     esc_html( $priority_task->priority_count ) .
								     " " . esc_html( $priority_name ) . "</li>";
								$has_tasks = true;
								break;
							}
						}

						// since we do not have tasks for the given priority, show 0 count
						if ( ! $has_tasks ) {
							echo "<li>" . "0 " . esc_html( $priority_name ) . "</li>";
						}
					}
					?>
                </ul>
            </div>
		<?php endif; // enable_priority check  ?>
    </div> <!-- main-content end -->
</div> <!-- dashboard-content end -->