/**
 * NS Cloner - main JS library.
 *
 * @package NS_Cloner
 */

jQuery(
	function ( $ ) {

		/**
		 * Sections
		 */

		// Default pro promotion sections to closed.
		$( '.ns-cloner-section[id$=promo]' ).addClass( 'closed' );

		// Close report when close button is clicked.
		$( 'input.ns-cloner-close-report' ).click(
			function () {
				$( '.ns-cloner-report' ).fadeOut();
			}
		);

		// Set up action when clone mode select is changed.
		$( '.ns-cloner-select-mode' ).change(
			function () {
				$( '.ns-cloner-main-form' ).trigger( 'ns_cloner_form_refresh' );
			}
		);

		// Set up action when source site is changed.
		$( '.ns-cloner-site-select' ).change(
			function ( e ) {
				$( '.ns-cloner-main-form' ).trigger( 'ns_cloner_source_refresh', [ $( this ).val() ] );
			}
		);

		// Make section slide up / down when section toggle is clicked.
		$( '.ns-cloner-section-header' ).click(
			function () {
				$( this ).parents( '.ns-cloner-section' ).not( '#ns-cloner-section-modes' ).toggleClass( 'closed' );
			}
		);

		// Make all sections slide up / down when all toggles is clicked.
		$( '.ns-cloner-collapse-all' ).click(
			function () {
				var $sections = $( '.ns-cloner-section' ).not( '#ns-cloner-section-modes' );
				$sections.addClass( 'closed' );
			}
		);
		$( '.ns-cloner-expand-all' ).click(
			function () {
				var $sections = $( '.ns-cloner-section' ).not( '#ns-cloner-section-modes' );
				$sections.removeClass( 'closed' );
			}
		);

		/**
		 * Section Fields
		 */

		// When modifying a text field that should show immediate validation, trigger it.
		$( '.ns-cloner-section' ).on(
			'input',
			'input.ns-cloner-quick-validate',
			function () {
				var input   = $( this );
				var section = input.parents( '.ns-cloner-section' );
				// Show spinner next to field to indicate that validation is in progress.
				var input_group = input.parents( '.ns-cloner-input-group' );
				var input_ref   = input_group.length ? input_group.children( ':last:not(span)' ) : $( this );
				if ( input_ref.nextAll( '.ns-cloner-validating-spinner' ).length === 0 ) {
					input_ref.after( '<span class="ns-cloner-validating-spinner"></span>' );
				}
				// Remove any error messages.
				section.find( '.ns-cloner-error-message' ).remove();
				// Cancel any already scheduled validation for this field.
				if ( input.data( 'validation_timeout' ) ) {
					window.clearTimeout( input.data( 'validation_timeout' ) );
				}
				// Schedule validation in 1 second so that validation will only take place once a second.
				var timeout = window.setTimeout(
					function () {
						ns_cloner_form.validate_section( section.attr( 'id' ) );
						input.data( 'validation_timeout', false );
					},
					1000
				);
				// Save the timeout so it can be deferred (cleared and reset) if another change is made before the timeout ends.
				input.data( 'validation_timeout', timeout );
			}
		);

		// Add autocomplete for search box.
		$( '.ns-cloner-section-content select' ).not( '.no-chosen' ).each(
			function() {
				$( this ).chosen(
					{
						width: '100%',
						max_selected_options: $( this ).attr( 'data-max' )
					}
				);
			}
		);

		// Turn on repeaters.
		$( '.ns-repeater' ).nsRepeater();

		/**
		 * Modals
		 */

		// Show copy logs box before going to support, then close copy logs box once continue button is clicked.
		$( '[data-cloner-modal]' ).click(
			function ( e ) {
				var id    = $( this ).attr( 'data-cloner-modal' );
				var modal = $( '#' + id );
				if (  modal.length ) {
					modal.fadeIn();
					e.preventDefault();
				}
			}
		);
		$( '.ns-cloner-extra-modal .close' ).click(
			function ( e ) {
				$( this ).parents( '.ns-cloner-extra-modal' ).fadeOut();
				e.preventDefault();
			}
		);
		$( '.ns-cloner-extra-modal' ).click(
			function ( e ) {
				if ( e.target === this ) {
					$( this ).fadeOut();
				}
			}
		);

		// Force page to reload so if a migration is in progress it will be opened again.
		$( document ).on(
			'click',
			'.ns-modal-refresh, .ns-modal-close',
			function () {
				window.location.reload();
			}
		);

		// Enable cancelling a clone process.
		$( document ).on(
			'click',
			'.ns-modal-cancel',
			function () {
				if ( window.confirm( "Are you sure you want to cancel this cloning process?" ) ) {
					ns_cloner_form.ajax(
						{ action: 'ns_cloner_process_exit' },
						function () {
							$( '.ns-cloner-processes-working' ).next( 'h2' ).text( 'Cleaning up...' );
							// Delay a few seconds to give active processes the chance to recognize the exit flag,
							// so that the progress modal won't pop up when
							window.setTimeout( function(){
								window.location.reload();
							}, 10000 );
						}
					);
					$( '.ns-cloner-processes-working' ).slideUp().after(
						'<h2 style="text-align:center">Canceling...</h2>' +
						'<img src="' + ns_cloner.loading_img + '" class="ns-cloner-loading-center" alt="loading..." />'
					);
				}
			}
		);

		// Automatically open progress modal and start checking progress,
		// if an operation is already in progress when page is loaded.
		if ( ns_cloner.in_progress ) {
			$( '.ns-cloner-processes-modal' ).show( 500 );
			$( window ).on('load',
				function () {
					ns_cloner_form.get_progress();
				}
			);
		}

		// Automatically open modal and show report if the report from the last clone operation was not seen.
		if ( $( '.ns-cloner-report-content' ).children().length ) {
			$( '.ns-cloner-processes-working' ).hide();
			$( '.ns-cloner-processes-done' ).show();
			$( '.ns-cloner-processes-modal' ).show( 500 );
		}

		/**
		 * Bottom Bar and Button
		 */

		// Update the steps in the bottom bar when scrolling.
		$( document ).on(
			'scroll',
			function () {
				// Highlight the current step in the bottom bar - current determined by the latest section fully visible in the viewport.
				var sections = $( '.ns-cloner-section:visible' ).toArray().reverse();
				// Pass back to jQuery since it's now an array, not a jQuery object.
				$( sections ).each(
					function () {
						// calculate scroll offset required to be above bottom of viewport, including the bottom button bar.
						var bottom_of_viewport = $( window ).scrollTop() + $( window ).height() - 90;
						var bottom_of_element  = $( this ).offset().top + $( this ).height();
						if ( bottom_of_element < bottom_of_viewport ) {
							// Highlight the current step in the bottom bar.
							var step_label = $( '[data-section="' + $( this ).attr( 'id' ) + '"]' );
							step_label.addClass( 'seen' ).prevAll().addClass( 'seen' );
							return false;
						}
					}
				);
				// Adjust width of scroll progress bar
				// (for more accurate correlation of progress bar to steps in bottom bar, calculate relative to content, not whole page).
				var content_offset = $( '.ns-cloner-wrapper' ).offset().top;
				var scroll_max     = $( document ).height() - $( window ).height() - content_offset;
				var scroll_value   = $( window ).scrollTop() - content_offset;
				var scroll_percent = Math.round( 100 * (scroll_value / scroll_max) );
				$( '.ns-cloner-scroll-progress' ).css( 'width', scroll_percent + '%' );
			}
		);

		// Scroll to a section when clicking on its step in the bottom bar.
		$( '.ns-cloner-button-steps' ).on(
			'click',
			'span',
			function () {
				var section = $( '#' + $( this ).attr( 'data-section' ) );
				$( 'html,body' ).animate( { scrollTop: section.offset().top - 50 } );
			}
		);

		/**
		 * Refresh UI based on mode selection
		 */

		// Update ui when refresh is triggered by changed setting, etc.
		$( '.ns-cloner-main-form' ).on(
			'ns_cloner_form_refresh',
			function () {
				var $mode_selector   = $( '.ns-cloner-select-mode' );
				var $selected_option = $mode_selector.children( 'option[value=' + $mode_selector.val() + ']' );
				// Show correct metaboxes.
				var mode_slug        = $mode_selector.val();
				var mode_title       = $selected_option.text().trim();
				var mode_description = $selected_option.attr( 'data-description' );
				var mode_button_text = $selected_option.attr( 'data-button-text' );
				$( '.ns-cloner-section' ).filter( ':not([data-modes~=' + mode_slug + '],[data-modes=all])' ).not( '#ns-cloner-section-modes' ).slideUp().promise().done(
					function () {
						$( '.ns-cloner-section' ).filter( '[data-modes~=' + mode_slug + ']' ).slideDown();
					}
				);
				// Show correct description for current mode.
				$( '.ns-cloner-mode-description' ).fadeOut(
					function () {
						$( this ).html( mode_description ).fadeIn();
					}
				);
				// Set modal title based on current clone mode title (lowercased to look better).
				$( '.ns-modal-title' ).text( mode_title.charAt( 0 ).toUpperCase() + mode_title.slice( 1 ).toLowerCase() );
				// Fade out submit button - make updates to it later at same time as button steps,
				// so that animation timing won't mess up width calculations.
				$( '.ns-cloner-button' ).animate( { opacity: 0 } );
				// Fade out the steps in bottom bar, update active steps, then fade back in.
				$( '.ns-cloner-button-steps' ).animate(
					{ opacity: 0 },
					function () {
						// Remove current steps list.
						$( this ).html( '' );
						// Add new step for each visible section that has a non-empty data-button-step attribute.
						$( '.ns-cloner-section[data-modes~=' + mode_slug + ']:not([data-button-step=""])' ).each(
							function () {
								var section_id = $( this ).attr( 'id' );
								var label_text = $( this ).attr( 'data-button-step' );
								$( '<span/>', { 'data-section': section_id } ).text( label_text ).appendTo( '.ns-cloner-button-steps' );
							}
						);
						// Update submit button label.
						$( '.ns-cloner-button' ).val( mode_button_text );
						// Adjust font size so all steps fit on one line.
						var bar_width           = $( this ).width() - 20;
						var item_spacing        = $( this ).children().length * 55;
						var width_per_character = Math.floor( (bar_width - item_spacing) / $( this ).text().length );
						if ( width_per_character < 11 ) {
							var reduction_needed = 11 - width_per_character;
							$( this ).css( 'font-size', 18 - reduction_needed + 'px' );
						} else {
							$( this ).css( 'font-size', '18px' );
						}
						// Fade all steps back in.
						$( this ).animate( { opacity: 1 } );
						$( '.ns-cloner-button' ).animate( { opacity: 1 } );
					}
				);
				// Enable classes to provide hook for different behavior for different modes (eg teleport).
				$( '.ns-cloner-button' ).attr( 'data-mode', mode_slug );
			}
		);

		// Trigger initial setup when page loads.
		$( window ).on('load',
			function () {
				$( '.ns-cloner-main-form' )
					.trigger( 'ns_cloner_form_refresh' )
					.trigger( 'ns_cloner_source_refresh', $( '.ns-cloner-site-select').val() );
			}
		);

		// Force re-dispatch mode.
		$( '.ns-cloner-ajax-force-trigger' ).click(
			function(e){
				e.preventDefault();
				$( this ).parent().remove();
				ns_cloner_form.ajax_force = true;
			}
		);

		// Move license nag down to alerts section.
		$( '.notice-error:contains("NS Cloner")' )
			.removeClass( 'notice' )
			.addClass( 'ns-cloner-warning-message' )
			.remove()
			.prependTo( '.ns-cloner-form' );

		/**
		 * Form Submission
		 */

		// Validate and either show errors or submit form when clone button is clicked.
		$( document ).on(
			'click',
			'.ns-cloner-button',
			function ( e ) {
				e.preventDefault();
				// Show loading indicator and prevent double submissions.
				if ( $( this ).is( '.working' ) ) {
					return false;
				} else {
					$( this ).addClass( 'working' );
				}
				// Remove old error messages.
				$( '.ns-cloner-main-form' ).find( '.ns-cloner-error-message' ).remove();
				$( '.ns-cloner-button-steps > span' ).removeClass( 'invalid' );
				// Submit process_init request that will begin cloning process.
				ns_cloner_form.submit();
			}
		);

		/**
		 * Status page
		 */

		// Cancel/delete a scheduled cloning operation.
		$( '.ns-cloner-scheduled-delete' ).on(
			'click',
			function( e ){
				var button = $( this ).addClass( 'working' );
				ns_cloner_form.ajax(
					{
						'action' : 'ns_cloner_delete_schedule',
						'index'  : button.attr( 'data-index' )
					},
					function( result ){
						button.removeClass( 'working' );
						if ( true === result.success ) {
							button.parents( 'tr' ).slideUp();
						} else {
							alert( 'Item not found, deletion failed.' );
						}
					}
				);
				e.preventDefault();
			}
		);

		// Delete plugin options data.
		$( '.ns-cloner-options-delete' ).on(
			'click',
			function( e ){
				var button = $( this ).addClass( 'working' );
				ns_cloner_form.ajax(
					{
						'action' : 'ns_cloner_delete_options',
					},
					function( result ){
						button.removeClass( 'working' );
						if ( true === result.success ) {
							button.hide().after('<span class="ns-cloner-success-message">Data cleared successfully.</span>');
						} else {
							alert( 'Clearing data failed, please try again.' );
						}
					}
				);
				e.preventDefault();
			}
		);

		/**
		 * Utility functions
		 */

		window.ns_cloner_form = {

			'ajax': function ( data, success ) {
				// Automatically set the nonce.
				data.clone_nonce = ns_cloner.nonce;
				// Submit the ajax request.
				$.ajax(
					{
						type: 'POST',
						url: ns_cloner.ajaxurl + '?flag=' + data.action,
						dataType: 'JSON',
						data: data,
						success: success,
						error: function ( xhr, status, error ) {
							console.error( error );
						}
					}
				);
			},

			'validate_section': function ( section_id ) {
				var section    = $( '#' + section_id );
				var step_label = $( '[data-section="' + section_id + '"]' );
				// Make sure there's no pending validation request.
				if ( ! section.length || section.is( '.validating' ) ) {
					return;
				} else {
					section.addClass( 'validating' ).trigger( 'validation.start' );
				}
				// Get validation response and update ui.
				var data = ns_cloner_form.get_data(
					{
						'action': 'ns_cloner_validate_section',
						'section_id': section_id.replace( 'ns-cloner-section-', '' )
					}
				);
				ns_cloner_form.ajax(
					data,
					function ( response ) {
						// Don't bother validating if form has already been submitted -
						// let the process_init action handle any remaining validation errors.
						if ( $( '.ns-cloner-button' ).is( '.working' ) ) {
							return;
						}
						// Display validation results.
						var spinner = section.find( '.ns-cloner-validating-spinner' );
						section.removeClass( 'validating' );
						if ( response.success ) {
							spinner.addClass( 'valid' ).delay( 2000 ).fadeOut(
								function () {
									$( this ).remove();
								}
							);
							section.trigger( 'validation.success' );
							step_label.removeClass( 'invalid' ).addClass( 'valid' );
						} else {
							spinner.remove();
							section.trigger( 'validation.error' );
							step_label.removeClass( 'valid' ).addClass( 'invalid' );
							$.each(
								response.data,
								function ( index, item ) {
									var error_message = '<span class="ns-cloner-error-message">' + item.message + '</span>';
									section.find( '.ns-cloner-section-content' ).prepend( error_message );
								}
							);
						}
					}
				);
			},

			'submit': function () {
				// Set up data to post.
				var data = ns_cloner_form.get_data(
					{
						'action': 'ns_cloner_process_init'
					}
				);
				// Check first for any empty required fields (having data-required attr or instant validation).
				// Can't use regular HTML required attr because browser can get funny with fields in hidden sections.
				// This just speeds up validation with basic client-side checking. Probably not worth doing full
				// client site validation, but this is low hanging fruit to make UX a little better with fast
				// response if a field just gets missed by mistake.
				var missing_required = false;
				$( '.ns-cloner-section:visible' ).find( '[data-required], .ns-cloner-quick-validate' ).each(
					function(){
						if ( ! $( this ).val() ) {
							var label = $( this ).attr( 'data-label' );
							$( this ).parents( '.ns-cloner-section-content' )
							.prepend( '<span class="ns-cloner-error-message">' + label + ' is required.</span>' )
							.parent().removeClass( 'closed' );
							missing_required = true;
						}
					}
				);
				if ( missing_required ) {
					$( '.ns-cloner-button' ).removeClass( 'working' );
					// Scroll to location of first error message.
					var first_error = $( '.ns-cloner-error-message:first' );
					$( 'html,body' ).animate( { scrollTop: first_error.offset().top - 50 } );
					return;
				}
				// Submit request to begin the next step.
				ns_cloner_form.ajax(
					data,
					function ( response ) {
						$( '.ns-cloner-button' ).removeClass( 'working' );
						if ( response.success === true ) {
							// Validation + startup was successful, so start showing progress.
							$( '.ns-cloner-processes-modal' ).show( 500 );
							ns_cloner_form.get_progress();
						} else if ( response.success === false ) {
							// Validation + startup was not successful so show errors.
							$.each(
								response.data,
								function ( index, item ) {
									var error_message = $( '<span class="ns-cloner-error-message">' + item.message + '</span>' );
									// Add the error message to the appropriate section if associated with one, otherwise to the top of the form.
									if ( item.section ) {
										var section_id = 'ns-cloner-section-' + item.section;
										$( '#' + section_id )
										.removeClass( 'closed' )
										.find( '.ns-cloner-section-content' )
										.prepend( error_message );
										$( '[data-section="' + section_id + '"]' ).addClass( 'invalid' );
									} else {
										$( '.ns-cloner-main-form' ).prepend( error_message );
									}
								}
							);
							// Scroll to location of first error message.
							var first_error = $( '.ns-cloner-error-message:first' );
							$( 'html,body' ).animate( { scrollTop: first_error.offset().top - 50 } );
						} else {
							// Server error - invalid response (data.success not explicitly set as true OR false).
							alert( 'Sorry, an unidentified error occurred.' );
						}
					}
				);
			},

			'get_data': function ( data ) {
				if ( ! data ) {
					data = {};
				}
				var form = $( '.ns-cloner-main-form' );
				// Get all inputs that are in visible sections and have a name attribute.
				form.find( '.ns-cloner-section:visible [name]' ).each(
					function () {
						var name = $( this ).attr( 'name' );
						if ( name.match( /\[\]$/ ) ) {
							// Handles arrays, with more than one input sharing the same name.
							var plain_name     = name.replace( '[]', '' );
							var all_with_name  = $( '[name="' + name + '"]' );
							data[ plain_name ] = [];
							all_with_name.each(
								function () {
									if ( $( this ).is( 'select, [type=text], [type=hidden], :selected, :checked' ) ) {
										data[ plain_name ].push( $( this ).val() );
									}
								}
							);
							// Add empty string to cause data to be sent if a blank array.
							// This is so the backend can tell the difference between all items being unchecked
							// versus the field not being visible/sent at all - e.g. with tables, if all are
							// unchecked, we should apply the filter and have 0 tables copied, but if the control
							// wasn't shown (say the request was created programatically), we should default to
							// including all tables. This is necessary because jQuery ajax ignores blank arrays,
							// so a name/key won't get sent at all if it doesn't have at least a blank value.
							if ( ! data[ plain_name ].length ) {
								data[ plain_name ].push( '' );
							}
						} else {
							// Handle single elements.
							if ( $( this ).is( '[type=checkbox],[type=radio]' ) ) {
								value = $( this ).is( ":checked" ) ? 1 : '';
							} else {
								value = $( this ).val();
							}
							data[ name ] = value;
						}
					}
				);
				return data;
			},

			'get_progress': function () {
				var data = {
					'action': 'ns_cloner_get_progress'
				};
				// Note - there's a difference of response.data.status vs response.status.
				// 1) response.data.status is the status 'complete' or 'in_progress' of the cloning operation.
				// 2) response.status is the status 'success' or 'error' of wp_send_json_success/error.
				ns_cloner_form.ajax(
					data,
					function ( response ) {
						if ( response.data.report ) {
							// If a report was returned, we're all done - show it and hide the progress section.
							$( '.ns-process-report' ).html( response.data.report );
							$( '.ns-cloner-processes-working' ).slideUp(
								function () {
									$( '.ns-cloner-processes-done' ).slideDown();
								}
							);
						} else if ( true === response.success ) {
							// Cloning progress is not complete, so update progress and set timer to check progress again.
							ns_cloner_form.show_progress( response.data );
							window.setTimeout( ns_cloner_form.get_progress, 3000 );
						} else {
							// An error occurred.
							$( '.ns-modal-body' ).slideUp(
								function () {
									var error = response.data.pop();
									$( this ).after( '<span class="ns-cloner-error-message">' + error.message + '</span>' );
								}
							);
						}
					}
				);
			},

			'show_progress': function ( data ) {
				// Update progress bar.
				$( '.ns-create-site .ns-percents' ).text( Math.min( 100, data.percentage ) + '%' );
				$( '.ns-create-site .ns-cloner-progress-bar-inner' ).css( 'width', data.percentage + '%' );
				$( '.ns-cloner-processes-modal .objects-migrated' ).text( data.completed );
				$( '.ns-cloner-processes-modal .total-objects' ).text( data.total );
				// Update item counts.
				for ( var process in data.processes ) {
					var process_data = data.processes[ process ];
					var progress     = process_data.progress;
					var item         = $( '#' + process + '_progress' );
					// Before updating process progress, make sure it hasn't gone backward, and skip if so.
					// This sometimes happens right at the end due to finish() being called and deleting progress during cleanup.
					var last = ns_cloner_form.last_progress[ process ];
					if ( ! last || progress.completed > last.total ) {
						last = {
							total: progress.completed,
							time: new Date().valueOf()
						};
						ns_cloner_form.last_progress[ process ] = last;
						// If it was stalled (restart option shown) and now progress is added, we no longer need restart.
						ns_cloner_form.stalled[ process ] = false;
					}
					// Show progress for individual process.
					if ( item.length ) {
						var progress_display = item.find( 'em:first' ).data( 'scroller' );
						var total_display    = item.find( 'em:last' ).data( 'scroller' );
						// Update if existing - don't scroll total, because it will take too long for large numbers.
						var lag = progress.completed - progress_display.getCurrentValue();
						if (  lag > 500 || lag < -500 ) {
							progress_display.jumpTo( progress.completed );
						} else {
							progress_display.scrollTo( progress.completed );
						}
						total_display.jumpTo( progress.total );
					} else {
						// Or add new item if not.
						$( '.ns-cloner-progress-items' ).append(
							'<div id="' + process + '_progress">' +
							'<em>' + progress.completed + '</em> of <em>' + progress.total + '</em>' +
							'<small>' + process_data.label + '</small>' +
							'</div>'
						);
						$( '#' + process + '_progress' ).find( 'em' ).each(
							function () {
								$( this ).data( 'scroller', $( this ).digitScroller() );
							}
						);
					}
					// Auto re-dispatch if this process was dispatched more than 5 seconds ago but never marked received.
					var since_dispatched = ( Date.now() / 1000 ) - process_data.dispatched;
					if ( process_data.dispatched && since_dispatched > 5 && ! ns_cloner_form.ajax_force ) {
						ns_cloner_form.ajax(
							{
								'action' : 'ns_cloner_' + process + '_process',
								'nonce'  : process_data.nonce,
								'ajax'   : true
							}
						);
						$( '.ns-cloner-warning-message.ajax-on' ).show();
					}
					// Mark process as stalled if it's been more than 60 seconds since last progress.
					var since_last_progress = last && last.time ? new Date().valueOf() - last.time : 0;
					if ( ! process_data.dispatched && since_last_progress > 60000 && progress.completed != progress.total ) {
						$( '.ns-cloner-warning-message.ajax-force' ).show();
						ns_cloner_form.stalled[ process ] = true;
					}
					// Manually re-dispatch with force_process to override any locks, if manual restart clicked.
					if ( ns_cloner_form.stalled[ process ] && ns_cloner_form.ajax_force ) {
						ns_cloner_form.ajax(
							{
								'action' : 'ns_cloner_' + process + '_process',
								'nonce'  : process_data.nonce,
								'ajax'   : true,
								'force_process' : true,
							}
						);
						ns_cloner_form.stalled[ process ] = false;
						ns_cloner_form.last_progress[ process ].time = new Date().valueOf();
					}

				}
			},

			'last_progress': {},

			'stalled': {},

			'ajax_force': false,

		};

		$(document).ready(function () {
			$('.ns-cloner-extra-modal.load').fadeIn();
        });

		$(document).on('click', '#analytics-settings .save-analytics-mode', function () {
			var button = $(this).css({ opacity: 0.75 });
			button.find('span').text('Saving...');
			save_analytics_mode(button.attr('data-mode'), button.closest('.ns-cloner-extra-modal'));
        });

		$(document).on('change', '#analytics-settings [name=cloner_analytics_mode]', function () {
            save_analytics_mode($(this).val());
        });

		function save_analytics_mode(mode, modal) {
            $.post(
                ns_cloner.ajaxurl,
                {
                    action: 'ns_cloner_save_analytics_mode',
					nonce: ns_cloner.nonce,
                    mode: mode
                },
                function (response) {
                    if (typeof response.success !== 'undefined' && response.success === true && modal) {
                        modal.fadeOut();
                    }
                }
            );
        }
	}
);

/**
 * Lightweight custom jQuery plugin to create simple repeater input UI
 *
 * Uses format
 * <ul class="ns-repeater">
 *    <li>
 *    <!-- any input fields to be repeated -->
 *    <span class="ns-repeater-remove" title="remove"></span>
 *    </li>
 * </ul>
 * <input type="button" class="button ns-repeater-add" value="Add"/>
 */
jQuery.fn.nsRepeater = function () {
	this.on(
		'click',
		'.ns-repeater-remove',
		function () {
			var repeater = jQuery( this ).parents( '.ns-repeater' );
			var item     = jQuery( this ).parent( 'li' );
			if ( repeater.find( 'li' ).length > 1 ) {
				item.remove();
			} else {
				item.hide().addClass( 'invisible' );
				item.find( 'textarea,input,select' ).removeAttr( 'checked selected' ).val( '' );
			}
		}
	);
	this.next( '.ns-repeater-add' ).click(
		function () {
				var repeater = jQuery( this ).prev( '.ns-repeater' );
				var item     = repeater.children( 'li:last' ).clone();
				item.show().removeClass( 'invisible' );
				item.find( 'textarea,input,select' ).removeAttr( 'checked selected' ).val( '' );
				repeater.append( item );
				repeater.children('li:last').find('.chosen-container').remove();
				repeater.children('li:last').find('select').chosen();
		}
	);
};

/**
 * Project: https://github.com/svichas/jquery.digitScroller.js
 * Author: Stefanos Vichas
 * License: MIT
 */

! function ( o ) {
	o.fn.digitScroller = function ( s ) {
		var r, e                       = o( this ),
			l                          = (s = o.extend( {}, { scrollTo: 0, scrollDuration: 0 }, s ), "" == e.html() ? "0" : e.html()), n = ! 1;
		return this.init               = function () {
			return this.initDom(), this
		}, this.getCurrentValue        = function () {
			var t = 0;
			return e.find( ".__digit_scroller_digit" ).each(
				function () {
						t += o( this ).find( ".__digit_scroller_current_digit" ).html()
				}
			), parseInt( this.removeFrontZeros( t ) )
		}, this.transitionToUp         = function ( t ) {
			e.find( ".__digit_scroller_digit" ).eq( t ).css( "transition", "transform " + s.scrollDuration + "ms ease" ).addClass( "_digit_up" )
		}, this.setNextNumberToCurrent = function () {
			return e.find( ".__digit_scroller_digit" ).each(
				function () {
						o( this ).find( ".__digit_scroller_current_digit" ).html( o( this ).find( ".__digit_scroller_next_digit" ).html() )
				}
			), e.find( ".__digit_scroller_digit" ).css( "transition", "transform 0ms ease 0s" ).removeClass( "_digit_up" ), ! 0
		}, this.updateNextValue        = function ( t ) {
			var r = this, n = 0;
			for ( t = this.addZeros( t, l.length ), i = e.find( ".__digit_scroller_digit" ).length; i < t.length; i ++ ) {
				e.append( this.cunstuctDigit( "0" ) );
			}
			e.find( ".__digit_scroller_digit" ).each(
				function () {
						o( this ).find( ".__digit_scroller_next_digit" ).html() != t.charAt( n ) && (o( this ).find( ".__digit_scroller_next_digit" ).html( t.charAt( n ) ), r.transitionToUp( n, ! 0 )), n ++
				}
			), setTimeout( this.setNextNumberToCurrent, s.scrollDuration - 25 )
		}, this.addZeros               = function ( t, i ) {
			if ( (t = String( t )).length >= i ) {
				return t;
			}
			for ( var r = t.length; r < i; r ++ ) {
				t = "0" + t;
			}
			return t
		}, this.removeFrontZeros       = function ( t ) {
			return t.includes( "-" ) ? t.replace( new RegExp( "^[0]*" ), "" ) : t
		}, this.initDom                = function () {
			if ( e.hasClass( "__digit_scroller_wrap" ) ) {
				return ! 1;
			}
			e.addClass( "__digit_scroller_wrap" ), e.html( "" );
			for ( var t = 0; t < l.length; t ++ ) {
				e.append( this.cunstuctDigit( l[t] ) );
			}
			return ! 0
		}, this.cunstuctDigit          = function ( t ) {
			return o( "<span/>", { class: "__digit_scroller_digit" } ).append(
				o(
					"<span/>",
					{
						class: "__digit_scroller_current_digit",
						html: t
					}
				)
			).append( o( "<span/>", { class: "__digit_scroller_next_digit", html: t } ) )
		}, this.coreScroll             = function () {
			var t = this.getCurrentValue();
			if ( t == s.scrollTo ) {
				return n = ! 1, void clearInterval( r );
			}
			s.scrollTo > t ? t ++ : t --, this.updateNextValue( t )
		}, this.scrollTo               = function ( t ) {
			if ( ! n ) {
				var i = this;
				n     = ! 0, s.scrollTo = t, this.coreScroll(), r = setInterval(
					function () {
							i.coreScroll()
					},
					s.scrollDuration
				)
			}
			return this
		}, this.jumpTo                 = function ( t ) {
			return this.updateNextValue( t ), this
		}, this.scrollDuration         = function ( t ) {
			return s.scrollDuration = t, this
		}, this.init()
	}
}( jQuery );
