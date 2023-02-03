<?php
namespace Codexonics\PrimeMoverFramework\compatibility;

/*
 * This file is part of the Codexonics.PrimeMoverFramework package.
 *
 * (c) Codexonics Ltd
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Codexonics\PrimeMoverFramework\classes\PrimeMover;
use FS_Options;
use FS_Plugin;
use WP_Site;
use Freemius;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Prime Mover Freemius Compatibility Class
 * Helper class for interacting with Freemius
 *
 */
class PrimeMoverFreemiusCompat
{     
    private $prime_mover;
    private $freemius_options;
    private $freemius;
    private $core_modules;
    private $free_deactivation_option;
    private $action_links;
    
    /**
     * Construct
     * @param PrimeMover $prime_mover
     * @param array $utilities
     * @param Freemius $freemius
     */
    public function __construct(PrimeMover $prime_mover, $utilities = [], Freemius $freemius = null)
    {
        $this->prime_mover = $prime_mover;
        $this->freemius_options = [
            'fs_accounts',
            'fs_dbg_accounts',
            'fs_active_plugins',
            'fs_api_cache',
            'fs_dbg_api_cache',
            'fs_debug_mode'
        ];
        
        $this->action_links = [
            'upgrade',
            'activate-license prime-mover',
            'opt-in-or-opt-out prime-mover',            
        ];
        
        $this->freemius = $freemius;
        $this->core_modules = [PRIME_MOVER_DEFAULT_FREE_BASENAME, PRIME_MOVER_DEFAULT_PRO_BASENAME];
        $this->free_deactivation_option = '_prime_mover_free_autodeactivated';
    }   
 
    /**
     * Get action links
     * @return string[]
     */
    public function getActionLinks()
    {
        return $this->action_links;
    }
    
    /**
     * Get auto deactivation option
     * @return string
     */
    public function getAutoDeactivationOption()
    {
        return $this->free_deactivation_option;
    }
    
    /**
     * Get core modules
     * @return string[]
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverFreemiusCompat::itGetsCoreModules()
     */
    public function getCoreModules()
    {
        return $this->core_modules;
    }
    
    /**
     * Get Freemius
     * @return Freemius
     */
    public function getFreemius()
    {
        return $this->freemius;
    }
    
    /**
     * Get Freemius options
     * @return string[]
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverFreemiusCompat::itGetsFreemiusOptions() 
     */
    public function getFreemiusOptions()
    {
        return $this->freemius_options;
    }
    
    /**
     * Register hooks
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverFreemiusCompat::itRegisterDeactivationHook() 
     */
    public function registerHooks()
    {        
        register_deactivation_hook(PRIME_MOVER_MAINPLUGIN_FILE, [$this, 'deactivationHook']);
        add_action('admin_init', [$this, 'activationHook'], 0);
        
        add_filter('network_admin_plugin_action_links_' . PRIME_MOVER_DEFAULT_PRO_BASENAME , [$this, 'userFriendlyActionLinks'], PHP_INT_MAX, 1);
        add_filter('plugin_action_links_' . PRIME_MOVER_DEFAULT_PRO_BASENAME , [$this, 'userFriendlyActionLinks'], PHP_INT_MAX, 1);
        
        add_filter('network_admin_plugin_action_links_' . PRIME_MOVER_DEFAULT_FREE_BASENAME , [$this, 'userFriendlyActionLinks'], PHP_INT_MAX, 1);
        add_filter('plugin_action_links_' . PRIME_MOVER_DEFAULT_FREE_BASENAME , [$this, 'userFriendlyActionLinks'], PHP_INT_MAX, 1);
       
        add_action('network_admin_notices', [$this, 'maybeShowMainSiteOnlyMessage'] );
        add_action( 'init', [$this, 'maybeUpdateIfUserReadMessage']); 
    }
    
    /**
     * Update if user read message
     */
    public function maybeUpdateIfUserReadMessage() {
        if (!$this->getPrimeMover()->getSystemAuthorization()->isUserAuthorized()) {
            return;
        }
        
        $args = [
            'prime_mover_networksites_nonce' => FILTER_SANITIZE_STRING,
            'prime_mover_networksites_action' => FILTER_SANITIZE_STRING,
        ];
        
        $settings_get = $this->getPrimeMover()->getSystemInitialization()->getUserInput('get', $args, '', '', 0, true, true);
        if (empty($settings_get['prime_mover_networksites_action']) || empty($settings_get['prime_mover_networksites_nonce'])) {
            return;
        }
        
        $action = $settings_get['prime_mover_networksites_action'];
        $nonce = $settings_get['prime_mover_networksites_nonce'];
        
        if ('prime_mover_mark_user_read' === $action && $this->getSystemFunctions()->primeMoverVerifyNonce($nonce, 'prime_mover_user_read_mainsiteonly_notice')) {
            $this->getPrimeMover()->getSystemFunctions()->updateSiteOption($this->getPrimeMover()->getSystemInitialization()->getUserUnderstandMainSiteOnly(), 'yes', true);
            $this->redirectAndExit();
        }
    }
 
    /**
     * Redirect and exit helper
     */
    protected function redirectAndExit()
    {
        wp_safe_redirect(network_admin_url('sites.php') );
        exit;
    }
  
    /**
     * Generate import notice success URL
     * @return string
     */
    protected function generateNoticeSuccessUrl() {
        
        return add_query_arg(
            [
                'prime_mover_networksites_action' => 'prime_mover_mark_user_read',
                'prime_mover_networksites_nonce'  => $this->getSystemFunctions()->primeMoverCreateNonce('prime_mover_user_read_mainsiteonly_notice'),
            ], network_admin_url('sites.php')            
            );
    }
    
    /**
     * Show main site only message to user
     */
    public function maybeShowMainSiteOnlyMessage()
    {
        if (!$this->isOnNetworkSitesAuthorized()) {
            return;
        }
        
        if (!$this->isNetworkUsingOnlyMainSite()) {
            return;
        }        
        
        if (!$this->isUserNeedsToCreateSubSite()) {
            return;
        }    
        
        $upgrade_url = network_admin_url( 'admin.php?page=migration-panel-settings-pricing');
        $addsites_url = network_admin_url('site-new.php');        
        ?>
	    <div class="notice notice-info">  
	        <h2><?php esc_html_e('Important notice', 'prime-mover'); ?></h2>
	        <p><?php echo sprintf(esc_html__('Thank you for using %s. 
        To get started using the free version, you need to %s. Free version works on any number of multisite subsites. 
        If you want to export and restore the multisite main site, you need to %s. Thanks!', 'prime-mover'), 
	            '<strong>' . PRIME_MOVER_PLUGIN_CODENAME . '</strong>', 
	            '<a href="' . esc_url($addsites_url) . '">' . esc_html__('add a subsite for testing', 'prime-mover') . '</a>',
	            '<a href="' . esc_url($upgrade_url) . '">' . esc_html__('upgrade to the PRO version', 'prime-mover') . '</a>'
	            );
                ?>
	        </p>	
       
		    <p><a class="button" href="<?php echo esc_url($this->generateNoticeSuccessUrl()); ?>"><?php esc_html_e('Yes, I understand', 'prime-mover'); ?></a>
		</div>
		<?php        
    }

    /**
     * Checks if only using main site
     * @return boolean
     */
    protected function isNetworkUsingOnlyMainSite()
    {
        $count = (int)get_blog_count();
        if ($count === 0) {
            return false;
        }
        if ($count > 1) {
            return false;
        }
        
        $mainsite_blogid = $this->getPrimeMover()->getSystemInitialization()->getMainSiteBlogId();
        if (apply_filters('prime_mover_maybe_load_migration_section', false, $mainsite_blogid)) {
            return false;
        }    
        
        return true;
    }
    
    /**
     * Is on network sites and authorized
     * @return boolean
     */
    protected function isOnNetworkSitesAuthorized()
    {        
        return (is_multisite() && $this->getPrimeMover()->getSystemInitialization()->isNetworkSites() && $this->getPrimeMover()->getSystemAuthorization()->isUserAuthorized());        
    }
    
    /**
     * Returns TRUE if user needs to create subsite
     * Otherwise FALSE
     * @return void|boolean
     */
    protected function isUserNeedsToCreateSubSite()
    {
        $shouldread = false;
        $importantreadmsg_setting = $this->getPrimeMover()->getSystemInitialization()->getUserUnderstandMainSiteOnly();
                
        if ('yes' !== $this->getPrimeMover()->getSystemFunctions()->getSiteOption($importantreadmsg_setting)) {   
            $shouldread = true;
        }
        
        return $shouldread;
    }   
    
    /**
     * User friendly action links.
     * @param array $actions
     * @return array
     */
    public function userFriendlyActionLinks($actions = [])
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized() ) {
            return $actions;
        }        
        if (!is_array($actions)) {
            return $actions;
        }
        if (empty($actions)) {
            return $actions;
        }
        $freemius = [];
        $core = [];
        $prime_mover = [];
        
        foreach ($actions as $k => $v) {
            if (in_array($k, $this->getActionLinks())) {
                $freemius[$k] = $v;                
            } elseif ($k === $this->getSystemInitialization()->getPrimeMoverActionLink()) {
                $prime_mover[$k] = $v;
            } else {
                $core[$k] = $v;
            }
        }
        
        return array_merge($core, $freemius, $prime_mover);
    }
    
    /**
     * Deactivation hook
     */
    public function activationHook()
    {
        if (wp_doing_ajax()) {
            return;
        }
        $current =basename(PRIME_MOVER_PLUGIN_PATH) . '/' . PRIME_MOVER_PLUGIN_FILE;
        if (PRIME_MOVER_DEFAULT_FREE_BASENAME === $current) {
            return;
        }
        if (!$this->getSystemAuthorization()->isUserAuthorized() ) {
            return;
        }
        
        $activation_params = $this->getSystemInitialization()->getUserInput('get',
            [
                'activate' => FILTER_SANITIZE_STRING
            ], 'prime_mover_activate_validate');
        
        if ($this->getSystemFunctions()->getSiteOption($this->getAutoDeactivationOption()) && isset($activation_params['activate']) && 'true' === $activation_params['activate']) {
            $this->getSystemFunctions()->deleteSiteOption($this->getAutoDeactivationOption());
            $this->freemiusAllCleanedUp();
            $this->setRedirectTransient(false);
        }
    }
    
    /**
     * Deactivation hook
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverFreemiusCompat::itRunsDeactivationHooks()
     */
    public function deactivationHook()
    {      
        do_action('prime_mover_deactivated');
        $current =basename(PRIME_MOVER_PLUGIN_PATH) . '/' . PRIME_MOVER_PLUGIN_FILE;
        if (PRIME_MOVER_DEFAULT_PRO_BASENAME === $current) {
            return;
        }
       
        if (!$this->getSystemAuthorization()->isUserAuthorized() ) {
            return;
        }
          
        if ($this->deactivationUserInitiated()) {
            return;
        }
        
        if ($this->deactivationNotProVersionInitiated()) {
            return;
        }
       
        if ($this->moreThanOneFreemiusModules()) {
            return;
        }
      
        $this->freemiusAllCleanedUp(); 
        $this->setRedirectTransient(true);
    }
    
    /**
     * Set redirect transient
     */
    protected function setRedirectTransient($update_option = false)
    {
        $transient = "fs_{$this->getFreemius()->get_module_type()}_{$this->getFreemius()->get_slug()}_activated";
        delete_transient($transient);
        set_transient($transient, true, 60);
        if ($update_option) {
            $this->getSystemFunctions()->updateSiteOption($this->getAutoDeactivationOption(), true);
        }        
    }
    
    /**
     * Clean up Freemius option
     * @param boolean $network
     */
    protected function freemiusCleanup($network = false)
    {
        foreach ($this->getFreemiusOptions() as $option) {
            if ($network) {
                delete_site_option($option);
            } else {
                delete_option($option);
            }            
        }
    }
    
    /**
     * Clean up all Freemius options
     */
    protected function freemiusAllCleanedUp() 
    {
        if (is_multisite()) {            
            if (wp_is_large_network()) {
                return;
            }
            
            $sites = get_sites();            
            foreach ( $sites as $site ) {
                $blog_id = ($site instanceof WP_Site) ?
                $site->blog_id :
                $site['blog_id'];
                
                switch_to_blog($blog_id );                
                $this->freemiusCleanup(false);
                restore_current_blog();
            }            
            $this->freemiusCleanup(true);            
        } else {          
            $this->freemiusCleanup(false);
        }
        
        do_action('prime_mover_log_processed_events', "Prime Mover successfully executes Freemius Fixer", 0, 'common', __FUNCTION__ , $this);
    }
    
    /**
     * Checks if more than one Freemius modules
     * @return boolean
     */
    protected function moreThanOneFreemiusModules()
    {
        if (!$this->isFreemiusLoaded()) {
            return true;
        }
        
        $fs_options = FS_Options::instance(WP_FS__ACCOUNTS_OPTION_NAME, true);
        if (!is_object($fs_options)) {
            return true;
        }
        
        $modules = fs_get_entities($fs_options->get_option('plugins'), FS_Plugin::get_class_name());
        if (!is_array($modules)) {
            return true;
        }
        
        $active = $this->getActiveModules($modules);
        $counted = count($active);
        
        return ($counted > 0);
    }
    
    /**
     * Get active Freemius modules
     * @param array $modules
     * @return []
     */
    protected function getActiveModules($modules = [])
    {
        $active = [];
        foreach ($modules as $module) {
            if (!isset($module->file)) {
                continue;
            }
            
            $file = $module->file;
            if (!$file) {
                continue;
            }
            
            $file = strtolower($file);
            if (in_array($file, $this->getCoreModules())) {
                continue;
            }
            
            if ($this->isPluginActive($file)) {
                $active[] = $file;
            }
        }
               
        return $active;
    }
    
    /**
     * Checks if plugin is active
     * Multisite or single site compatible
     * @param string $file
     * @return boolean
     */
    protected function isPluginActive($file = '')
    {
        if (!$file) {
            return false;
        }
        
        if (is_multisite() && $this->getSystemFunctions()->isPluginActive($file, true)) {
            return true;
        } elseif ($this->getSystemFunctions()->isPluginActive($file)) {
            return true;
        }
        return false;
    }
    
    /**
     * Checks if Freemius classes loaded
     * @return boolean
     */
    protected function isFreemiusLoaded()
    {
        return (class_exists('FS_Options') && defined('WP_FS__ACCOUNTS_OPTION_NAME') && function_exists('fs_get_entities') && class_exists('FS_Plugin'));       
    }
    
    /**
     * Returns FALSE if deactivation is pro version initiated
     * @return boolean
     */
    protected function deactivationNotProVersionInitiated()
    {
        $action = '';
        $plugin = '';
        $nonce = '';
        list($action, $plugin, $nonce) = $this->getDeactivationParams(); 
        
        if (!$action || !$plugin || !$nonce) {
            return true;
        }
        
        if ('activate' === $action && PRIME_MOVER_DEFAULT_PRO_BASENAME === $plugin && $this->getSystemFunctions()->primeMoverVerifyNonce( $nonce, 'activate-plugin_' . PRIME_MOVER_DEFAULT_PRO_BASENAME, true)) {
            return false;
        }
        
        return true;        
    }

    /**
     * Returns TRUE if deactivation is user initiated
     * @return boolean
     */
    protected function deactivationUserInitiated()
    {        
        $action = '';
        $plugin = '';
        $nonce = '';
        
        list($action, $plugin, $nonce) = $this->getDeactivationParams();        
        return ('deactivate' === $action && PRIME_MOVER_DEFAULT_FREE_BASENAME === $plugin && $this->getSystemFunctions()->primeMoverVerifyNonce($nonce, 'deactivate-plugin_' . PRIME_MOVER_DEFAULT_FREE_BASENAME, true));
    }
    
    /**
     * Get deactivation parameters
     * @return string[]|
     */
    protected function getDeactivationParams()
    {
        $queried = $this->getSystemInitialization()->getUserInput('get',
            [
                'action' => FILTER_SANITIZE_STRING,
                'plugin' => FILTER_SANITIZE_STRING,
                '_wpnonce' => FILTER_SANITIZE_STRING
            ], 'prime_mover_free_deactivation');
        
        $action = '';
        $plugin = '';
        $nonce = '';
        
        if (!empty($queried['action'])) {
            $action = $queried['action'];
        }
        
        if (!empty($queried['plugin'])) {
            $plugin = $queried['plugin'];
        }
        
        if (!empty($queried['_wpnonce'])) {
            $nonce = $queried['_wpnonce'];
        }
        
        return [$action, $plugin, $nonce];
    }
    
    /**
     * Get system initialization
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemInitialization
     */
    public function getSystemInitialization()
    {
        return $this->getPrimeMover()->getSystemInitialization();
    }
    
    /**
     * Get system authorization
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemAuthorization
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverFreemiusCompat::itRunsDeactivationHooks()
     */
    public function getSystemAuthorization()
    {
        return $this->getPrimeMover()->getSystemAuthorization();
    }
    
    /**
     * Get Prime Mover instance
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMover
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverFreemiusCompat::itRunsDeactivationHooks()
     */
    public function getPrimeMover()
    {
        return $this->prime_mover;
    }
    
    /**
     * Get system functions
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemFunctions
     */
    public function getSystemFunctions()
    {
        return $this->getPrimeMover()->getSystemFunctions();
    }
}