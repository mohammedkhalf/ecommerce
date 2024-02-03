jQuery(document).ready(function ($) {
	console.log('Load Redirect Url')
			var timesRefreshed = 0;
			var redirectUrl = cowpay_data.return_url;
			$("iframe").load(function(){
				timesRefreshed++; 
				if(timesRefreshed == 1){
					window.location.href = redirectUrl;
					// setTimeout(function() {
					// 	window.location.href = redirectUrl;
					// }, 9000);
				}
			});
});
