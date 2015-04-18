
		jQuery("#EmailToAdmin a").click(
			function(){
				var el = this;
				//el.preventDefault();
				var link = jQuery(el).attr("href");
				jQuery.get(link)
					.done(
						function( data ) {
							jQuery("#EmailToAdmin").html( data );


							// Get the form.
							var form = $('#EmailToAdmin form');

							// Get the messages div.
							var formMessages = $('#EmailToAdmin');

							// Set up an event listener for the contact form.
							$(form).submit(function(e) {
								// Stop the browser from submitting the form.
								e.preventDefault();

								// Serialize the form data.
								var formData = $(form).serialize();

								// Submit the form using AJAX.
								$.ajax({
									type: 'POST',
									url: $(form).attr('action'),
									data: formData
								})
								.done(function(response) {
									// Make sure that the formMessages div has the 'success' class.
									$(formMessages).removeClass('error');
									$(formMessages).addClass('success');

									// Set the message text.
									$(formMessages).html(response);
								})
								.fail(function(data) {
									// Make sure that the formMessages div has the 'error' class.
									$(formMessages).removeClass('success');
									$(formMessages).addClass('error');
									alert(data);
									// Set the message text.
									if (data.responseText !== '') {
										$(formMessages).text(data.responseText);
									}
									else {
										$(formMessages).text('An error occured and your message could not be sent.');
									}
								});

							});

						}
					)
					.fail(function() {alert( "Sorry, an error occurred, please contact us directly" );})
					.always(function() {});
				return false;
			}
		);
