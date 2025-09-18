/**
 * Copy existing profile element to navigation menu
 */
jQuery(document).ready(function($) {
    // Check if profile menu feature is enabled using localized data
    if (typeof userspnProfileMenu !== 'undefined' && userspnProfileMenu.enabled) {
        // Add class to body to enable CSS rules
        $('body').addClass('userspn-profile-menu-enabled');
        
        // Try to find and add container to menu
        addContainerToMenu();
        copyProfileToMenu();
    }
    
    function addContainerToMenu() {
        // Look for navigation menus
        var $navigationMenus = $('.wp-block-navigation, .menu, .nav-menu');
        
        if ($navigationMenus.length > 0) {
            // Add container to the first navigation menu found
            var $firstMenu = $navigationMenus.first();
            var $menuList = $firstMenu.find('ul').first();
            
            if ($menuList.length > 0) {
                // Create container if it doesn't exist
                if ($('#userspn-profile-menu-container').length === 0) {
                    var containerHtml = '<li class="menu-item userspn-profile-container" id="userspn-profile-menu-container"></li>';
                    $menuList.append(containerHtml);
                }
            }
        }
    }
    
    function copyProfileToMenu() {
        var $existingProfile = $('.userspn-profile');
        var $menuContainer = $('#userspn-profile-menu-container');
        
        if ($existingProfile.length && $menuContainer.length) {
            // Copy the existing profile element to the menu container
            var $profileCopy = $existingProfile.clone();
            $profileCopy.appendTo($menuContainer);
            
            // Add menu-specific classes
            $profileCopy.addClass('userspn-profile-in-menu');
        }
    }
    
    // Check periodically in case the profile element loads after this script
    var checkInterval = setInterval(function() {
        if ($('.userspn-profile').length && !$('.userspn-profile-in-menu').length) {
            addContainerToMenu();
            copyProfileToMenu();
        }
    }, 1000);
    
    // Clear interval after 15 seconds to avoid infinite checking
    setTimeout(function() {
        clearInterval(checkInterval);
    }, 15000);
    
    // Also check when window loads completely
    $(window).on('load', function() {
        setTimeout(function() {
            if ($('.userspn-profile').length && !$('.userspn-profile-in-menu').length) {
                addContainerToMenu();
                copyProfileToMenu();
            }
        }, 500);
    });
});
