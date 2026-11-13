<?php
/**
 * Tutorial Onboarding Manager.
 *
 * This class handles the interactive tutorial/onboarding experience for new users.
 *
 * @link       padresenlanube.com/
 * @since      1.0.0
 * @package    USERSPN
 * @subpackage userspn/includes
 * @author     Padres en la Nube
 */
class USERSPN_Tutorial
{
  /**
   * Check if tutorial should be displayed
   *
   * @return bool
   */
  public static function should_display()
  {
    return !get_option('userspn_tutorial_completed', false);
  }

  /**
   * Enqueue tutorial assets
   */
  public static function enqueue_assets()
  {
    if (!self::should_display()) {
      return;
    }

    wp_enqueue_style(
      'userspn-tutorial',
      USERSPN_URL . 'assets/css/admin/userspn-tutorial.css',
      [],
      USERSPN_VERSION
    );

    wp_enqueue_script(
      'userspn-tutorial',
      USERSPN_URL . 'assets/js/admin/userspn-tutorial.js',
      ['jquery'],
      USERSPN_VERSION,
      true
    );

    wp_localize_script('userspn-tutorial', 'userspnTutorial', [
      'ajaxUrl' => admin_url('admin-ajax.php'),
      'nonce' => wp_create_nonce('userspn-nonce'),
    ]);
  }

  /**
   * Render tutorial HTML
   */
  public static function render()
  {
    if (!self::should_display()) {
      return;
    }
    ?>
    <!-- Tutorial Onboarding Overlay -->
    <div id="userspn-tutorial-overlay" class="userspn-tutorial-overlay">
      <div class="userspn-tutorial-spotlight"></div>
      <div class="userspn-tutorial-box">
        <?php self::render_step_welcome(); ?>
        <?php self::render_step_profile_popup(); ?>
        <?php self::render_step_tabs(); ?>
        <?php self::render_step_security(); ?>
        <?php self::render_step_finish(); ?>
      </div>
    </div>
    <?php
  }

  /**
   * Render Step 1: Welcome
   */
  private static function render_step_welcome()
  {
    ?>
    <div class="userspn-tutorial-step" data-step="1" data-target=".userspn-options">
      <div class="userspn-tutorial-header">
        <h2><?php esc_html_e('Welcome to UsersPN', 'userspn'); ?></h2>
        <button type="button" class="userspn-tutorial-skip" title="<?php esc_attr_e('Skip tutorial', 'userspn'); ?>">
          <i class="material-icons-outlined">close</i>
        </button>
      </div>
      <div class="userspn-tutorial-content">
        <p><?php esc_html_e('Let us guide you through the main features of this powerful user management plugin.', 'userspn'); ?></p>
        <ul class="userspn-tutorial-features">
          <li><i class="material-icons-outlined">check_circle</i> <?php esc_html_e('Custom profile popup with tabs', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">check_circle</i> <?php esc_html_e('Social login with Google, Facebook, GitHub, and Apple', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">check_circle</i> <?php esc_html_e('Two-factor authentication (2FA)', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">check_circle</i> <?php esc_html_e('Extended registration with custom fields', 'userspn'); ?></li>
        </ul>
      </div>
      <div class="userspn-tutorial-footer">
        <?php self::render_progress(1, 5); ?>
        <button type="button" class="userspn-btn userspn-btn-mini userspn-tutorial-next">
          <?php esc_html_e('Get Started', 'userspn'); ?> <i class="material-icons-outlined">arrow_forward</i>
        </button>
      </div>
    </div>
    <?php
  }

  /**
   * Render Step 2: Profile Popup Configuration
   */
  private static function render_step_profile_popup()
  {
    ?>
    <div class="userspn-tutorial-step" data-step="2" data-target="#userspn_section_user_popup_start">
      <div class="userspn-tutorial-header">
        <h2><?php esc_html_e('Configure Profile Popup', 'userspn'); ?></h2>
        <button type="button" class="userspn-tutorial-skip" title="<?php esc_attr_e('Skip tutorial', 'userspn'); ?>">
          <i class="material-icons-outlined">close</i>
        </button>
      </div>
      <div class="userspn-tutorial-content">
        <p><?php esc_html_e('Customize where and how your profile bubble appears on your site.', 'userspn'); ?></p>
        <ul class="userspn-tutorial-features">
          <li><i class="material-icons-outlined">adjust</i> <?php esc_html_e('Position the bubble in menu or custom location', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">app_registration</i> <?php esc_html_e('Enable front-end registration', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">dynamic_form</i> <?php esc_html_e('Add custom fields to user profiles', 'userspn'); ?></li>
        </ul>
        <p class="userspn-tutorial-tip">
          <i class="material-icons-outlined">lightbulb</i>
          <span>
            <strong><?php esc_html_e('Tip:', 'userspn'); ?></strong>
            <?php esc_html_e('Use the preview button at the top to see how your profile popup looks to visitors.', 'userspn'); ?>
          </span>
        </p>
      </div>
      <div class="userspn-tutorial-footer">
        <?php self::render_progress(2, 5); ?>
        <?php self::render_navigation(); ?>
      </div>
    </div>
    <?php
  }

  /**
   * Render Step 3: Tabs Configuration
   */
  private static function render_step_tabs()
  {
    ?>
    <div class="userspn-tutorial-step" data-step="3" data-target="#userspn_tabs_section_start">
      <div class="userspn-tutorial-header">
        <h2><?php esc_html_e('Profile Tabs', 'userspn'); ?></h2>
        <button type="button" class="userspn-tutorial-skip" title="<?php esc_attr_e('Skip tutorial', 'userspn'); ?>">
          <i class="material-icons-outlined">close</i>
        </button>
      </div>
      <div class="userspn-tutorial-content">
        <p><?php esc_html_e('Enable and configure tabs for your user profile popup.', 'userspn'); ?></p>
        <ul class="userspn-tutorial-features">
          <li><i class="material-icons-outlined">account_circle</i> <?php esc_html_e('Custom profile pictures (PFP)', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">notifications</i> <?php esc_html_e('User notifications tab', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">folder</i> <?php esc_html_e('File uploads by role', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">shopping_cart</i> <?php esc_html_e('WooCommerce purchases (if installed)', 'userspn'); ?></li>
        </ul>
      </div>
      <div class="userspn-tutorial-footer">
        <?php self::render_progress(3, 5); ?>
        <?php self::render_navigation(); ?>
      </div>
    </div>
    <?php
  }

  /**
   * Render Step 4: Security Settings
   */
  private static function render_step_security()
  {
    ?>
    <div class="userspn-tutorial-step" data-step="4" data-target="#userspn_section_security_start">
      <div class="userspn-tutorial-header">
        <h2><?php esc_html_e('Security Settings', 'userspn'); ?></h2>
        <button type="button" class="userspn-tutorial-skip" title="<?php esc_attr_e('Skip tutorial', 'userspn'); ?>">
          <i class="material-icons-outlined">close</i>
        </button>
      </div>
      <div class="userspn-tutorial-content">
        <p><?php esc_html_e('Enhance your site security with two-factor authentication and social login options.', 'userspn'); ?></p>
        <ul class="userspn-tutorial-features">
          <li><i class="material-icons-outlined">lock</i> <?php esc_html_e('Two-factor authentication (2FA)', 'userspn'); ?></li>
          <li><i class="material-icons-outlined">vpn_key</i> <?php esc_html_e('Backup codes and recovery', 'userspn'); ?></li>
        </ul>
        <p class="userspn-tutorial-tip">
          <i class="material-icons-outlined">security</i>
          <span>
            <strong><?php esc_html_e('Social Login:', 'userspn'); ?></strong>
            <?php esc_html_e('Configure Google, Facebook, GitHub, or Apple login in the Social Login subsection above.', 'userspn'); ?>
          </span>
        </p>
      </div>
      <div class="userspn-tutorial-footer">
        <?php self::render_progress(4, 5); ?>
        <?php self::render_navigation(); ?>
      </div>
    </div>
    <?php
  }

  /**
   * Render Step 5: Finish
   */
  private static function render_step_finish()
  {
    ?>
    <div class="userspn-tutorial-step" data-step="5" data-target=".userspn-settings-footer">
      <div class="userspn-tutorial-header">
        <h2><?php esc_html_e('You\'re All Set!', 'userspn'); ?></h2>
      </div>
      <div class="userspn-tutorial-content">
        <p><?php esc_html_e('You now know the basics of UsersPN. Start by configuring your profile popup and enabling the features you need.', 'userspn'); ?></p>
        <p class="userspn-tutorial-tip">
          <i class="material-icons-outlined">help_outline</i>
          <span>
            <strong><?php esc_html_e('Need help?', 'userspn'); ?></strong>
            <?php esc_html_e('Check our documentation or use the tooltips throughout the interface for guidance.', 'userspn'); ?>
          </span>
        </p>
      </div>
      <div class="userspn-tutorial-footer">
        <?php self::render_progress(5, 5); ?>
        <div class="userspn-tutorial-navigation">
          <button type="button" class="userspn-btn userspn-btn-mini userspn-btn-transparent userspn-tutorial-prev">
            <i class="material-icons-outlined">arrow_back</i> <?php esc_html_e('Back', 'userspn'); ?>
          </button>
          <button type="button" class="userspn-btn userspn-btn-mini userspn-tutorial-finish">
            <?php esc_html_e('Finish Tutorial', 'userspn'); ?> <i class="material-icons-outlined">done</i>
          </button>
        </div>
      </div>
    </div>
    <?php
  }

  /**
   * Render progress indicator
   *
   * @param int $current Current step number
   * @param int $total Total number of steps
   */
  private static function render_progress($current, $total)
  {
    $percentage = ($current / $total) * 100;
    ?>
    <div class="userspn-tutorial-progress">
      <span class="userspn-tutorial-progress-text">
        <?php
        // translators: %1$d is the current step number, %2$d is the total number of steps.
        echo esc_html(sprintf(__('Step %1$d of %2$d', 'userspn'), $current, $total));
        ?>
      </span>
      <div class="userspn-tutorial-progress-bar">
        <div class="userspn-tutorial-progress-fill" style="width: <?php echo esc_attr($percentage); ?>%;"></div>
      </div>
    </div>
    <?php
  }

  /**
   * Render navigation buttons (back/next)
   */
  private static function render_navigation()
  {
    ?>
    <div class="userspn-tutorial-navigation">
      <button type="button" class="userspn-btn userspn-btn-mini userspn-btn-transparent userspn-tutorial-prev">
        <i class="material-icons-outlined">arrow_back</i> <?php esc_html_e('Back', 'userspn'); ?>
      </button>
      <button type="button" class="userspn-btn userspn-btn-mini userspn-tutorial-next">
        <?php esc_html_e('Next', 'userspn'); ?> <i class="material-icons-outlined">arrow_forward</i>
      </button>
    </div>
    <?php
  }

  /**
   * Mark tutorial as completed
   *
   * @param bool $completed Whether tutorial was completed or skipped
   */
  public static function mark_completed($completed = true)
  {
    update_option('userspn_tutorial_completed', $completed ? 'completed' : 'skipped');
  }

  /**
   * Reset tutorial (for testing or re-showing)
   */
  public static function reset()
  {
    delete_option('userspn_tutorial_completed');
  }
}
