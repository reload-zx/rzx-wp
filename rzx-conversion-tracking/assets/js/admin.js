/**
 * RZX.bio Conversion Tracking - Admin JavaScript
 *
 * @package RZX_Conversion_Tracking
 */

(function($) {
	'use strict';

	/**
	 * RZX Admin handler.
	 */
	var RZXAdmin = {
		/**
		 * Initialize.
		 */
		init: function() {
			this.bindEvents();
		},

		/**
		 * Bind event handlers.
		 */
		bindEvents: function() {
			$('#rzx-test-connection').on('click', this.testConnection);
		},

		/**
		 * Test API connection.
		 *
		 * @param {Event} e Click event.
		 */
		testConnection: function(e) {
			e.preventDefault();

			var $button = $(this);
			var $result = $('#rzx-test-result');
			var apiKey = $('#rzx_api_key').val();

			// Check if API key is entered.
			if (!apiKey || apiKey.trim() === '') {
				$result
					.removeClass('success loading')
					.addClass('error')
					.text(rzxAdmin.noApiKey);
				return;
			}

			// Show loading state.
			$button.addClass('loading').prop('disabled', true);
			$result
				.removeClass('success error')
				.addClass('loading')
				.text(rzxAdmin.testing);

			// Make AJAX request.
			$.ajax({
				url: rzxAdmin.ajaxUrl,
				type: 'POST',
				data: {
					action: 'rzx_test_connection',
					nonce: rzxAdmin.nonce
				},
				success: function(response) {
					$button.removeClass('loading').prop('disabled', false);
					$result.removeClass('loading');

					if (response.success) {
						$result
							.removeClass('error')
							.addClass('success')
							.text(response.data.message);

						// Reload page after short delay to update status.
						setTimeout(function() {
							location.reload();
						}, 1500);
					} else {
						$result
							.removeClass('success')
							.addClass('error')
							.text(rzxAdmin.error + ' ' + response.data.message);
					}
				},
				error: function(xhr, status, error) {
					$button.removeClass('loading').prop('disabled', false);
					$result
						.removeClass('loading success')
						.addClass('error')
						.text(rzxAdmin.error + ' ' + error);
				}
			});
		}
	};

	// Initialize on document ready.
	$(document).ready(function() {
		RZXAdmin.init();
	});

})(jQuery);
