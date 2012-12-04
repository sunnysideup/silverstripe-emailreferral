/**
*@author nicolaas[at]sunnysideup . co . nz
*
**/

(function($){

	$(document).ready(
		function() {
			EmailReferralForm.init();
		}
	);

})(jQuery);


var EmailReferralForm = {

	emailFieldSelector: "#To input",

	emailSpanReplaceSelector: "#emailReplacer",

	init: function() {
		jQuery(EmailReferralForm.emailFieldSelector).change(
			function() {
				jQuery(EmailReferralForm.emailSpanReplaceSelector).text(jQuery(this).val());
			}
		);
	}

}


