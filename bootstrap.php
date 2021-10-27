<?php

namespace SIMPELBOEK;
class Bootstrap
{
/**
 * Checks if the system requirements are met
 *
 * @return bool True if system requirements are met, false if not
 */
	const NAMESPACE = 'SIMPELBOEK';
	const SHORTCODE = 'simpelboek';
	const NAME = 'simpelboek';
	const REQUIRED_PHP_VERSION = '7.0';
	const REQUIRED_WP_VERSION = '3.1';
	const PLUGINNAME = "simpelboek";
	public static function NameSpace()
	{ 
		return(self::NAMESPACE);
	}
	public static function PluginName()
	{ 
		return(self::PLUGINNAME);
	}
	protected function requirements() 
	{
		global $wp_version;

		if ( version_compare( PHP_VERSION, self::REQUIRED_PHP_VERSION, '<' ) ) {
			return false;
		}

		if ( version_compare( $wp_version, self::REQUIRED_WP_VERSION, '<' ) ) {
			return false;
		}
		return true;
	}
	/** Definieer constantes */
	protected function define_constants()
	{
		define ( 'SBK_PLUGIN_DIR', wp_normalize_path( plugin_dir_path( __FILE__ ) ) );
		define ( 'SBK_DATA_DIR', SBK_PLUGIN_DIR . 'data/' );
		define ( 'SBK_DOC_DIR', SBK_PLUGIN_DIR . 'doc/' );
		define ( 'SBK_FUNCTIONS_DIR', SBK_PLUGIN_DIR . 'functions/' );
		define ( 'SBK_PLUGIN_DIRNAME' , str_replace( '/' . basename( __FILE__ ), "", plugin_basename(__FILE__) ) );
	}

/**
 * Prints an error that the system requirements weren't met.
 */
	protected function requirements_error() 
	{
		global $wp_version;
		echo notices::requirements_error();
	}
	protected function trap()
	{
		$self = new self();
		echo notices::trap($self::PLUGINNAME . " in trap" . $self::NAMESPACE );
	}
	#
	# check if class file exists
	#
	public static function ClassFile($class)
	{
		$dirs=array("classes","classes/overzichten");
		$class=strtolower($class);
		foreach($dirs as $d)
		{
			$classfile = '/' . $d . '/' . $class . '.php';
			#echo "<br>file=".dirname( __FILE__ ) .$classfile;
			if(file_exists(dirname( __FILE__ ) . $classfile)) return($classfile);
		}
		return("");
	}
	#
	# autoloader for the classes defined in map classes
	#
	protected function autoloader()
	{
		spl_autoload_register(function ($class_name)
		{
			$self = new self();
			#echo $class_name;
			$parts = explode( '\\', $class_name );
			if($parts[0] == $self::NAMESPACE)
			{
				$classfile=$this->ClassFile($parts[1]);
				#require_once( dirname( __FILE__ ) . '/classes/' . $classfile . '.php' );
				require_once( dirname( __FILE__ ) . $classfile );
			}
		});
	}
	/** Laadt functiebestanden */
	protected function load_functions() {
		$files = glob( SBK_FUNCTIONS_DIR . '*.php' );
		foreach ( $files as $file ) {
			require_once $file;
		}
	}
	/**
	 * Start session
	 */
	function register_my_session()
	{
  		if( !session_id() )
 		{
   			 session_start();
  		}
	}
	public function init()
	{
		$self = new self();
		$this->autoloader();	#start autoloader for loading classes automatically
		$this->define_constants();
		$this->load_functions();
		$main = new main();
		$options = new options();
		if ( $this->requirements() ) 
		{
			# set shortcode, so plugin can be started in an article like: [maintaindbtable .... ]
			add_shortcode( $self::SHORTCODE, array($main ,'init') );
			$options->init();		#make parameters form
		}  
		else 
		{
			add_action( 'admin_notices', array($this,'requirements_error') ); # error message 
			add_action('init', 'register_my_session');

		}
	}
}