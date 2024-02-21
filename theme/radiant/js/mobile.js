// Check if the device is a mobile device
function isMobileDevice() {
    return /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
}
// Load jQuery for mobile devices
$(document).ready(function() {
     // Hide the block by default
     if (isMobileDevice()) {
        $('#block-region-side-pre').hide();
        $('.admin-sidebar').hide();
     }
    if (isMobileDevice()) {
        $('div[data-region="drawer-toggle"]').find('button').click(function() {
            $('#block-region-side-pre').toggle();
            $('.admin-sidebar').toggle();
        });
        
    }

});