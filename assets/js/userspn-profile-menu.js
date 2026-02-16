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
        // Skip if container already exists (could be added server-side by PHP)
        if ($('#userspn-profile-menu-container').length > 0) {
            return;
        }

        // Try block theme navigation first
        var $blockNav = $('.wp-block-navigation');
        if ($blockNav.length > 0) {
            // Block themes use a different DOM structure without <ul> elements
            var $navContainer = $blockNav.first().find('.wp-block-navigation__container').first();

            if ($navContainer.length === 0) {
                // Fallback to responsive container content (overlay/mobile mode)
                $navContainer = $blockNav.first().find('.wp-block-navigation__responsive-container-content').first();
            }

            if ($navContainer.length > 0) {
                if ($navContainer.is('ul')) {
                    $navContainer.append('<li class="wp-block-navigation-item menu-item userspn-profile-container" id="userspn-profile-menu-container"></li>');
                } else {
                    $navContainer.append('<div class="wp-block-navigation-item menu-item userspn-profile-container" id="userspn-profile-menu-container"></div>');
                }
                return;
            }
        }

        // Fallback for classic themes
        var $classicMenus = $('.menu, .nav-menu');
        if ($classicMenus.length > 0) {
            var $menuList = $classicMenus.first().find('ul').first();

            if ($menuList.length > 0) {
                $menuList.append('<li class="menu-item userspn-profile-container" id="userspn-profile-menu-container"></li>');
            }
        }
    }
    
    function copyProfileToMenu() {
        var $existingProfile = $('.userspn-profile');
        var $menuContainer = $('#userspn-profile-menu-container');

        if ($existingProfile.length && $menuContainer.length) {
            // Clone only the trigger button that opens the popup
            var $triggerBtn = $existingProfile.find('.userspn-profile-popup-btn').first();
            if ($triggerBtn.length && !$menuContainer.find('.userspn-profile-popup-btn').length) {
                var $btnClone = $triggerBtn.clone(true);
                // Mark as in-menu for styling purposes
                $btnClone.addClass('userspn-profile-in-menu');
                $menuContainer.empty().append($btnClone);
            }
        }
    }

    function movePopupOutOfProfile() {
        // Ensure the popup is not inside the hidden profile wrapper on mobile
        var $popup = $('#userspn-profile-popup');
        if ($popup.length && !$popup.parent().is('body')) {
            $('body').append($popup);
        }
    }
    
    // Check periodically in case the profile element loads after this script
    var checkInterval = setInterval(function() {
        if ($('.userspn-profile').length) {
            addContainerToMenu();
            copyProfileToMenu();
            movePopupOutOfProfile();
        }
    }, 1000);
    
    // Clear interval after 15 seconds to avoid infinite checking
    setTimeout(function() {
        clearInterval(checkInterval);
    }, 15000);
    
    // Also check when window loads completely
    $(window).on('load', function() {
        setTimeout(function() {
            if ($('.userspn-profile').length) {
                addContainerToMenu();
                copyProfileToMenu();
                movePopupOutOfProfile();
            }
        }, 500);
    });
});
