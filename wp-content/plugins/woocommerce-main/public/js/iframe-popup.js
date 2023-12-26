jQuery(document).ready(function ($) {
    console.log("<?php echo $_SESSION['return_url']; ?>")
    var timesRefreshed = 0;
    var redirectUrl = "<?php echo $_SESSION['return_url']; ?>";
    $("iframe").load(function(){
        // console.log('iframe load success')
        // console.log(redirectUrl)
        timesRefreshed++; 
        if(timesRefreshed == 2){
            window.location.href = redirectUrl;
        }
    });
});