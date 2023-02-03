<?php
/**
 * 
 * This is the final system dependency class, purpose is to manage file-system permission checks.
 *
 */
class PrimeMoverFileSystemDependencies
{
    /**
     * Paths that dont have permission to write
     * @var array
     */
    private $problematic_paths = array();
    
    /**
     * Paths that require permission to write
     * @var array
     */
    private $required_allowed_permission_paths = array();
 
    /**
     * Constructor
     */
    public function __construct( $required_paths = array() ) 
    {
        $this->required_allowed_permission_paths = $required_paths;
    }
    
    /**
     * Gets required permission paths
     * @return array
     * @compatible 5.6
     */
    public function getRequiredPermissionPaths()
    {
        return $this->required_allowed_permission_paths;
    }
    
    /**
     * Checks file system permission issues
     * @compatible 5.6
     */
    private function checkFileSystemPermissionIssues()
    {
        foreach ( $this->getRequiredPermissionPaths() as $path ) {
            if ( $this->isValidPath($path) && $this->wpIsNotWritable($path)) {
                $this->problematic_paths[] = $path;
            }
        }
    }
    
    protected function wpIsNotWritable($path)
    {
       return ! wp_is_writable($path);        
    }
    
    /**
     * Checks if directory
     * @param string $path
     * @return boolean
     */
    protected function isValidPath($path = '')
    {
        return (is_dir($path) || is_file($path) );
    }
    
    /**
     * Get paths having permission issues
     * @return array
     * @compatible 5.6
     */
    public function getProblematicPaths()
    {
        return $this->problematic_paths;
    }
    
    /**
     * File system permission checks
     * @return boolean
     * @compatible 5.6
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverRequirements::itChecksCorrectPluginRequirementsSingleSite()
     * @tested Codexonics\PrimeMoverFramework\Tests\TestPrimeMoverRequirements::itChecksCorrectPluginRequirementsMultisite()
     */
    public function fileSystemPermissionsRequisiteCheck()
    {
        $notice_hook = 'admin_notices';
        if (is_multisite()) {
            $notice_hook = 'network_admin_notices';
        }
        $this->checkFileSystemPermissionIssues();
        
        $problematic_paths = $this->getProblematicPaths();
        $problematic_paths = $this->areConstantsSetAndValid($problematic_paths);
        if ( ! empty( $problematic_paths ) ) {
            add_action($notice_hook, array( $this, 'warnIncorrectPermissions'));
            return false;
        } else {
            return true;
        }
    }
    
    /**
     * Check required constants and check if they are valid
     * @param array $problematic_paths
     * @return array
     */
    protected function areConstantsSetAndValid($problematic_paths = array())
    {
        if (empty($problematic_paths)) {
            return $problematic_paths;
        }
        
        $config_path = primeMoverGetConfigurationPath();
        if (!$config_path) {
            return $problematic_paths;
        }
        
        $key = array_search($config_path, $problematic_paths);
        if (false === $key) {
            return $problematic_paths;
        }
       
        if ($this->isWpSiteUrlValid() && $this->isWpHomeUrlValid() && $this->isEncKeyValid()) {
            unset($problematic_paths[$key]);               
        }
        
        return $problematic_paths;
    }
    
    /**
     * Checks if encryption key valid
     * @return boolean
     */
    protected function isEncKeyValid()
    {
        return primeMoverIsEncKeyValid();
    }
    
    /**
     * Checks if site url constant is valid
     * @return boolean
     */
    protected function isWpSiteUrlValid()
    {
        return primeMoverIsWpSiteUrlValid();
    } 
 
    /**
     * Checks if home url constant is valid
     * @return boolean
     */
    protected function isWpHomeUrlValid()
    {
        return primeMoverIsWpHomeUrlValid();
    }
    
    /**
     * Warn user of incorrect permissions
     * @compatible 5.6
     */
    public function warnIncorrectPermissions()
    {
        $problematic_paths = $this->getProblematicPaths();
        if ( empty($problematic_paths) || ! is_array($problematic_paths)) {
            return;
        }
        ?>
        <div class="error">        
         <p><?php printf( esc_html__( 'The %s plugin cannot be activated if the following paths were not writable by WordPress', 'prime-mover' ), 
             '<strong>' . esc_html(PRIME_MOVER_PLUGIN_CODENAME) . '</strong>' )?>:</p>
            <ul>
                <?php 
                foreach ( $this->getProblematicPaths() as $path ) {
                ?>
                    <li><strong><?php echo $path;?></strong></li>
                <?php    
                }
                ?>
            </ul>
             <?php 
             $file = basename($path);
             $text = esc_html__('', 'prime-mover');
             if ('wp-config.php' === $file && 1 === count($problematic_paths)) {
             ?>
                 <p><?php printf(esc_html__('Please add or edit the following constants to %s manually and then activate again', 'prime-mover' ), '<strong>wp-config.php</strong>'); ?>.</p>
                 <ul>
                 <?php 
                 if (!$this->isEncKeyValid()) {
                 ?>
                     <li><code><?php echo $this->outputSuggestedEncConstant(); ?></code></li>
                 <?php
                 }
                 
                 if (!$this->isWpSiteUrlValid()) {
                 ?>
                     <li><code><?php echo $this->outputSiteUrlParameter(); ?></code></li>
                 <?php 
                 }
                 if (!$this->isWpHomeUrlValid()) {
                 ?>
                     <li><code><?php echo $this->outputHomeUrlParameter(); ?></code></li>
                 </ul>
                 <?php 
                 }
                 $text = esc_html__('If you do not know how to add these constants -', 'prime-mover');
                 ?>
             <?php 
             }
             ?>
             <p><?php echo sprintf(esc_html__('%s Please contact your web hosting provider or request to make these paths writable.', 'prime-mover' ), $text); ?></p>
            </div>
    <?php
    }
    
    /**
     * Output suggested enc constant
     * @return string
     */
    protected function outputSuggestedEncConstant()
    {
        return primeMoverOutputSuggestedEncConstant();
    }
    
    /**
     * Output site url parameter constant
     * @return string
     */
    protected function outputSiteUrlParameter()
    {        
        return primeMoverOutputSiteUrlParameter();
    }
  
    /**
     * Output home URL parameter constant
     * @return string
     */
    protected function outputHomeUrlParameter()
    {
        return primeMoverOutputHomeUrlParameter();
    }
    
    /**
     * Check if maybe Windows OS
     * @return boolean
     */
    protected function maybeWindows()
    {
        return (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
        
    }
}