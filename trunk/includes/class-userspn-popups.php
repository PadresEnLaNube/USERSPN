<?php
/**
 * Class USERSPN_Popups
 * Handles popup functionality for the USERSPN plugin
 */
class USERSPN_Popups {
    /**
     * The single instance of the class
     */
    protected static $_instance = null;

    /**
     * Main USERSPN_Popups Instance
     */
    public static function instance() {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Open a popup
     */
    public static function open($content, $options = array()) {
        $defaults = array(
            'id' => uniqid('userspn-popup-'),
            'class' => '',
            'closeButton' => true,
            'overlayClose' => true,
            'escClose' => true
        );

        $options = wp_parse_args($options, $defaults);

        ob_start();
        ?>
        <div id="<?php echo esc_attr($options['id']); ?>" class="userspn-popup <?php echo esc_attr($options['class']); ?>" style="display: none;">
            <div class="userspn-popup-overlay"></div>
            <div class="userspn-popup-content">
                <?php if ($options['closeButton']) : ?>
                    <button type="button" class="userspn-popup-close"><i class="material-icons-outlined">close</i></button>
                <?php endif; ?>
                <?php echo wp_kses_post($content); ?>
            </div>
        </div>
        <?php
        $html = ob_get_clean();

        return $html;
    }

    /**
     * Close a popup
     */
    public static function close($id = null) {
        if ($id) {
            return "<script>jQuery('#" . esc_js($id) . "').remove();</script>";
        }
        return "<script>jQuery('.userspn-popup').remove();</script>";
    }
} 