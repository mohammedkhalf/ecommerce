jQuery(document).ready(function ($) {
	console.log('Load Redirect Url')

	function redirectSuccess(delay) {
		setTimeout(function() {
			var timesRefreshed = 0;
			var redirectUrl = cowpay_data.return_url;
			$("iframe").load(function(){
				timesRefreshed++; 
				if(timesRefreshed == 2){
					window.location.href = redirectUrl;
				}
			});
		}, delay);
	}
	redirectSuccess(4000);   
});
