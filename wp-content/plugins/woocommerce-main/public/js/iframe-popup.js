jQuery(document).ready(function ($) {
        console.log('Load here')
        var timesRefreshed = 0;
		var redirectUrl = cowpay_data.return_url;
		$("iframe").load(function(){
			console.log('iframe load success')
			console.log(redirectUrl)
			timesRefreshed++; 
			if(timesRefreshed == 2){
				window.location.href = redirectUrl;
			}
		});
});
