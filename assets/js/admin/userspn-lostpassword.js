// Wait for jQuery to be available
function initLostPasswordForm() {
    if (typeof jQuery === 'undefined') {
        setTimeout(initLostPasswordForm, 100);
        return;
    }
    
    jQuery(document).ready(function($) {
        var $form = $('form[name="lostpasswordform"]');
        
        if ($form.length) {
            // Submit form directly without nonce validation
            $form.on('submit', function(e) {
                // Let the form submit normally
                return true;
            });
        }
    });
}

// Start initialization
initLostPasswordForm(); 