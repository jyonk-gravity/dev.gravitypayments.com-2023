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

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Prime Mover custom configuration class
 * Helper class for WordPress sites with customized wp-config.php file implementation.
 */
class PrimeMoverCustomConfig
{     
    private $prime_mover;
    private $config_utilities;
    
    /**
     * Construct
     * @param PrimeMover $prime_mover
     * @param array $utilities
     */
    public function __construct(PrimeMover $prime_mover, $utilities = [])
    {
        $this->prime_mover = $prime_mover;
        $this->config_utilities = $utilities['config_utilities'];
    }
    
    /**
     * Get config utilities
     * @return array
     */
    public function getConfigUtilities()
    {
        return $this->config_utilities;
    }
    
    /**
     * Get Prime Mover instance
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMover
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
    
    /**
     * Get system authorization
     * @return \Codexonics\PrimeMoverFramework\classes\PrimeMoverSystemAuthorization
     */
    public function getSystemAuthorization()
    {
        return $this->getPrimeMover()->getSystemAuthorization();
    }
        
    /**
     * Initialize hooks
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverCustomConfig::itAddsInitHooks() 
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverCustomConfig::itChecksIfHooksAreOutdated() 
     */
    public function initHooks()
    {
        add_action('prime_mover_load_module_apps', [$this, 'maybeDeactivateIfCustomConfig'], 1);
        add_filter('prime_mover_is_config_usable', [$this, 'isConfigUsable'], 10, 1);
    }
        
    /**
     * Checks if config file is usable
     * Must be writable and not customized
     * @param boolean $ret
     * @return string|boolean
     */
    public function isConfigUsable($ret = false)
    {
        if ($this->getSystemFunctions()->isConfigFileWritable() && false === $this->isUsingCustomConfigFile()) {
            $ret = true;
        }        
        
        return $ret;
    }
    
    /**
     * Checks if wp-config is customized
     * @return boolean
     */
    public function isUsingCustomConfigFile()
    {
        $config_transformer = $this->getConfigUtilities()->getConfigTransformer();
        if (!is_object($config_transformer)) {
            return false;
        }
        
        return (!$config_transformer->exists('variable', 'table_prefix'));
    }
    
    /**
     * Deactivate plugin
     */
    public function deactivate()
    {
        if ($this->getSystemAuthorization()->isUserAuthorized()) {
            primeMoverAutoDeactivatePlugin();
        }        
    }
    
    /**
     * Deactivate Prime Mover plugin when using customized wp-config.php implementation
     * And that required constants are not set
     */
    public function maybeDeactivateIfCustomConfig()
    {
        if (!$this->getSystemAuthorization()->isUserAuthorized()) {
            return;
        }
        
        if (!$this->isUsingCustomConfigFile()) {
            return;
        }       
              
        if (primeMoverIsEncKeyValid() && primeMoverIsWpSiteUrlValid() && primeMoverIsWpHomeUrlValid()) {
            return;
        }
        
        $notice_hook = 'admin_notices';
        if (is_multisite()) {
            $notice_hook = 'network_admin_notices';
        }
        
        add_action($notice_hook, [$this, 'showRequiredConstants']);
        
         global $pm_fs;
         if (is_object($pm_fs)) {
             remove_action( 'admin_init', [$pm_fs, '_admin_init_action']);
         }            
         
         remove_all_actions('prime_mover_load_module_apps');
         add_action('admin_init', [$this, 'deactivate']);  
    }
        
    /**
     * Show required constants during plugin auto-deactivation
     */
    public function showRequiredConstants()
    {
        ?>
        <div class="notice notice-error">        
            <p><?php printf( esc_html__( 'The %s plugin is unable to add required constants to your WordPress configuration because it is not accessible', 'prime-mover' ), 
             '<strong>' . esc_html(PRIME_MOVER_PLUGIN_CODENAME) . '</strong>' )?>.</p>

            <p><?php esc_html_e('Please add or edit the following constants to your configuration file and then re-activate the plugin', 'prime-mover' ); ?>.</p>
             <ul>
             <?php 
             if (!primeMoverIsEncKeyValid()) {
             ?>
                 <li><code><?php echo primeMoverOutputSuggestedEncConstant(); ?></code></li>
             <?php
             }
             
             if (!primeMoverIsWpSiteUrlValid()) {
             ?>
                 <li><code><?php echo primeMoverOutputSiteUrlParameter(); ?></code></li>
             <?php 
             }
             if (!primeMoverIsWpHomeUrlValid()) {
             ?>
                 <li><code><?php echo primeMoverOutputHomeUrlParameter(); ?></code></li>
       <?php } ?>
             </ul>
        </div>
    <?php         
    }    
}