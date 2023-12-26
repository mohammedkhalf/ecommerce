jQuery(document).ready(function($) {
    // Wait for the DOM to be fully loaded
    $(document).ready(function() {
        // Add a click event listener to the "Place Order" button
        $('body').on('click', '#place_order', function(e) {
            // Prevent the default form submission
            e.preventDefault();

            // Replace 'YOUR_IFRAME_URL' with the actual URL of your iframe content
            var iframeUrl = redirectUrl;

            // Create a modal with the iframe
            var modalHtml = '<div id="iframe-popup-modal" style="display:none;"><iframe src="' + iframeUrl + '" width="100%" height="100%"></iframe></div>';
            $('body').append(modalHtml);

            // Open the modal
            $('#iframe-popup-modal').fadeIn();

            // Close the modal when clicking outside the iframe
            $(document).on('click', function(event) {
                if ($(event.target).is('#iframe-popup-modal')) {
                    $('#iframe-popup-modal').fadeOut();
                }
            });
        });
    });
});
