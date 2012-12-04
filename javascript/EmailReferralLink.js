/**
*@author nicolaas[at]sunnysideup . co . nz
*
**/

(function($){
	$(document).ready(
		function() {
			EmailReferralLink.init();
		}
	);


})(jQuery);


var EmailReferralLink = {

	popupLinkSelector: ".emailAFriendLink a",

	height: 600,
		set_height: function (v) {this.height = v;},

	width: 600,
		set_width: function(v) {this.width = v;},

	init: function() {
		jQuery(EmailReferralLink.popupLinkSelector).click(
			function() {
				day = new Date();
				id = day.getTime();
				url = jQuery(this).attr("href");
				window.open(url, id, 'toolbar=0,scrollbars=1,location=0,statusbar=0,menubar=1,resizable=1,width='+EmailReferralLink.width+',height='+EmailReferralLink.height+',left = 50,top = 50');
				return false;
			}
		);
	}

}


