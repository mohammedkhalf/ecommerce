<html lang="en">
    <body>
        <!-- Required div to display cowpay checkout button inside, with any id to be passed in mount() method. ! -->
        <div id="cowpay-checkout"></div>
        <!-- Loading Cowpay.js SDK -->
        <script src="public/js/cowpay.js"></script>
    </body>
</html>      

<script>
    Cowpay.checkout("2330ff3e-2911-4d52-bcf9-ad0b5f8862ba","584fc843-b6b3-466c-b05b-cfd01fb0af28").mount("cowpay-checkout");
</script>