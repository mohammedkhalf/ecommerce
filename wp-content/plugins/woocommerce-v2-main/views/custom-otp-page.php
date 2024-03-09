<html lang="en">
    <body>
        <!-- Required div to display cowpay checkout button inside, with any id to be passed in mount() method. ! -->
        <div id="cowpay-checkout"></div>
        <!-- Loading Cowpay.js SDK -->
        <script src="public/js/cowpay.js"></script>
    </body>
</html>      

<script>
    var secret =  <?php  echo $_SESSION['creditCard']['secret']; ?>;
    var frameCode =  <?php  echo $_SESSION['creditCard']['frameCode']; ?>;

    console.log(secret)

    Cowpay.checkout(secret,frameCode).mount("cowpay-checkout");
</script>