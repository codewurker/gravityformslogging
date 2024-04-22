<?php

GFForms::include_addon_framework();

/**
 * Gravity Forms Logging Add-On.
 *
 * @since     1.0
 * @package   GravityForms
 * @author    Rocketgenius
 * @copyright Copyright (c) 2016, Rocketgenius
 */
class GFLogging extends GFAddOn {

	/**
	 * Contains an instance of this class, if available.
	 *
	 * @since  1.0
	 * @access private
	 * @var    object $_instance If available, contains an instance of this class.
	 */
	private static $_instance = null;

	/**
	 * Defines the version of the Logging Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_version Contains the version, defined from logging.php
	 */
	protected $_version = GF_LOGGING_VERSION;

	/**
	 * Defines the minimum Gravity Forms version required.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_min_gravityforms_version The minimum version required.
	 */
	protected $_min_gravityforms_version = '1.9';

	/**
	 * Defines the plugin slug.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_slug The slug used for this plugin.
	 */
	protected $_slug = 'gravityformslogging';

	/**
	 * Defines the main plugin file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_path The path to the main plugin file, relative to the plugins folder.
	 */
	protected $_path = 'gravityformslogging/logging.php';

	/**
	 * Defines the full path to this class file.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_full_path The full path.
	 */
	protected $_full_path = __FILE__;

	/**
	 * Defines the URL where this Add-On can be found.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string The URL of the Add-On.
	 */
	protected $_url = 'http://www.gravityforms.com';

	/**
	 * Defines the title of this Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_title The title of the Add-On.
	 */
	protected $_title = 'Gravity Forms Logging Add-On';

	/**
	 * Defines the short title of the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_short_title The short title.
	 */
	protected $_short_title = 'Logging';

	/**
	 * Defines if Add-On should use Gravity Forms servers for update data.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    bool
	 */
	protected $_enable_rg_autoupgrade = true;

	/**
	 * Defines the capability needed to access the Add-On settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_settings_page The capability needed to access the Add-On settings page.
	 */
	protected $_capabilities_settings_page = 'gravityforms_logging';

	/**
	 * Defines the capability needed to access the Add-On form settings page.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_form_settings The capability needed to access the Add-On form settings page.
	 */
	protected $_capabilities_form_settings = 'gravityforms_logging';

	/**
	 * Defines the capability needed to uninstall the Add-On.
	 *
	 * @since  1.0
	 * @access protected
	 * @var    string $_capabilities_uninstall The capability needed to uninstall the Add-On.
	 */
	protected $_capabilities_uninstall = 'gravityforms_logging_uninstall';

	/**
	 * Defines the capabilities needed for the Logging Add-On
	 *
	 * @since  1.0
	 * @access protected
	 * @var    array $_capabilities The capabilities needed for the Add-On
	 */
	protected $_capabilities = array( 'gravityforms_logging', 'gravityforms_logging_uninstall' );

	/**
	 * Contains the KLogger objects for plugins with logging enabled.
	 *
	 * @since  1.0
	 * @access private
	 * @var    array $loggers KLogger objects for plugins with logging enabled.
	 */
	private static $loggers = array();

	/**
	 * Defines the maximum file size for a log file.
	 *
	 * @since  1.0
	 * @access private
	 * @var    string $max_file_size Maximum file size for a log file.
	 */
	private static $max_file_size = 10485760;

	/**
	 * Defines the maximum number of log files to store for a plugin.
	 *
	 * @since  1.0
	 * @access private
	 * @var    string $max_file_count Maximum number of log files to store for a plugin.
	 */
	private static $max_file_count = 10;

	/**
	 * Defines the date format for logged messages.
	 *
	 * @since  1.0
	 * @access private
	 * @var    string $date_format_log_file Date format for logged messages.
	 */
	private static $date_format_log_file = 'YmdGis';

	/**
	 * Get instance of this class.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return GFLogging
	 */
	public static function get_instance() {

		if ( null === self::$_instance ) {
			self::$_instance = new self;
		}

		return self::$_instance;

	}

	/**
	 * Register needed hooks and included needed libraries.
	 *
	 * @since  1.0
	 * @access public
	 */
	public function init() {

		parent::init();

		self::include_logger();

	}

	/**
	 * Remove log files during uninstall.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return bool
	 */
	public function uninstall() {

		self::delete_log_files();

		return true;

	}

	/**
	 * Register needed scripts.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return array $scripts
	 */
	public function scripts() {

		$scripts = array(
			array(
				'handle'  => 'clipboard_js',
				'src'     => 'https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.5/clipboard.min.js',
				'version' => '1.5.5',
				'enqueue' => array(
					array( 'admin_page' => array( 'plugin_settings' ) ),
				),
			),
		);

		return array_merge( parent::scripts(), $scripts );

	}

	/**
	 * Register needed styles.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return array $styles
	 */
	public function styles() {

		$styles = array(
			array(
				'handle'  => 'gform_logging_plugin_settings_css',
				'src'     => $this->get_base_url() . '/css/plugin_settings.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'admin_page' => array( 'plugin_settings' ) ),
				),
			),
		);

		return array_merge( parent::styles(), $styles );

	}


	/**
	 * Maybe delete log files.
	 *
	 * @since  1.1
	 * @access public
	 */
	public function plugin_settings_page() {

		if ( $this->is_gravityforms_supported( '2.2' ) ) {
			GFCommon::add_message( esc_html__( 'Gravity Forms 2.2 and newer include built-in logging functionality. This add-on can be safely removed using the uninstall button below or from the installed plugins page. Once removed logging can be enabled on the Forms > Settings page.', 'gravityformslogging' ) );
		}

		// If the delete_log parameter is set, delete the log file and display a message.
		if ( rgget( 'delete_log' ) ) {

			$deleted = self::delete_log_file( rgget( 'delete_log' ) );
			if ( $deleted ) {
				GFCommon::add_message( esc_html__( 'Log file was successfully deleted.', 'gravityformslogging' ) );
			} else {
				GFCommon::add_error_message( esc_html__( 'Log file could not be deleted.', 'gravityformslogging' ) );
			}

		}

		parent::plugin_settings_page();

	}

	/**
	 * Setup plugin settings fields.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {

		/* Get supported plugin fields. */
		$plugin_fields = $this->supported_plugins_fields();

		/* Add save button to the plugin fields array. */
		$plugin_fields[] = array(
			'type'     => 'save',
			'messages' => array(
				'success' => esc_html__( 'Plugin logging settings have been updated.', 'gravityformslogging' ),
			),
		);

		return array(
			array(
				'title'       => esc_html__( 'System Information Report', 'gravityformslogging' ),
				'description' => $this->get_system_information_report(),
				'fields'      => array(),
			),
			array(
				'title'       => esc_html__( 'Plugin Logging Setup', 'gravityformslogging' ),
				'description' => $this->plugin_settings_description(),
				'fields'      => $plugin_fields,
			),
		);

	}

	/**
	 * Setup plugin settings description.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return string $html
	 */
	public function plugin_settings_description() {

		$html  = '<p>';
		$html .= esc_html__( 'Gravity Forms Logging Add-On assists in tracking down issues by logging debug and error messages in Gravity Forms Core and Gravity Forms Add-Ons. Important information may be included in the logging messages, including API usernames, passwords and credit card numbers. Gravity Forms Logging Add-On is intended only to be used temporarily while trying to track down issues. Once the issue is identified and resolved, it should be uninstalled, which deletes the logs.', 'gravityformslogging' );
		$html .= '</p>';

		return $html;

	}

	/**
	 * Setup plugin settings title.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return string
	 */
	public function plugin_settings_title() {

		return esc_html__( 'Plugin Logging Settings', 'gravityformslogging' );

	}

	/**
	 * Renders and initializes a drop down field based on the $field array
	 * (included for users running a version of Gravity Forms earlier than 1.9.15.1)
	 *
	 * @since 1.1
	 * @param array $field - Field array containing the configuration options of this field.
	 * @param bool  $echo  = true - true to echo the output to the screen, false to simply return the contents as a string.
	 *
	 * @return string The HTML for the field
	 */
	public function settings_select( $field, $echo = true ) {

		// If Gravity Forms is 1.9.15.1 or newer, use the Add-On framework version of the select field.
		if ( version_compare( GFForms::$version, '1.9.15.1', '<' ) ) {
			return parent::settings_select( $field, $echo );
		}

		$field['type'] = 'select'; // Making sure type is set to select.
		$attributes    = $this->get_field_attributes( $field );
		$value         = $this->get_setting( $field['name'], rgar( $field, 'default_value' ) );
		$name          = '' . esc_attr( $field['name'] );

		$html = sprintf(
			'<select name="%1$s" %2$s>%3$s</select>',
			'_gaddon_setting_' . $name, implode( ' ', $attributes ), $this->get_select_options( $field['choices'], $value )
		);

		$html .= rgar( $field, 'after_select' );

		if ( $this->field_failed_validation( $field ) ) {
			$html .= $this->get_error_icon( $field );
		}

		if ( $echo ) {
			echo $html;
		}

		return $html;
	}

	/**
	 * Prepare supported plugins as plugin settings fields.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return array $fields
	 */
	public function supported_plugins_fields() {

		// Get the supported plugins.
		$supported_plugins = self::get_supported_plugins();

		// Setup logging options.
		$logging_options = array(
			array(
				'label' => esc_html__( 'Off', 'gravityformslogging' ),
				'value' => KLogger::OFF,
			),
			array(
				'label' => esc_html__( 'Log all messages', 'gravityformslogging' ),
				'value' => KLogger::DEBUG,
			),
			array(
				'label' => esc_html__( 'Log errors only', 'gravityformslogging' ),
				'value' => KLogger::ERROR,
			),
		);

		$plugin_fields = array();

		// Build the supported plugins fields array.
		foreach ( $supported_plugins as $plugin_slug => $plugin_name ) {

			$after_select = '';

			if ( self::log_file_exists( $plugin_slug ) ) {
				$after_select  = '&nbsp;&nbsp;<a href="' . esc_attr( self::get_log_file_url( $plugin_slug ) ) . '" target="_blank">' . esc_html__( 'view log', 'gravityformslogging' ) . '</a>';
				$after_select .= '&nbsp;&nbsp;<a href="' . add_query_arg( 'delete_log', $plugin_slug, admin_url( 'admin.php?page=gf_settings&subview=gravityformslogging' ) ) . '">' . esc_html__( 'delete log', 'gravityformslogging' ) . '</a>';
				$after_select .= '&nbsp;&nbsp;(' . self::get_log_file_size( $plugin_slug ) . ')';
			}

			$plugin_fields[] = array(
				'name'         => $plugin_slug . '[log_level]',
				'label'        => $plugin_name,
				'type'         => 'select',
				'choices'      => $logging_options,
				'after_select' => $after_select,
			);

			$plugin_fields[] = array(
				'name'          => $plugin_slug . '[file_name]',
				'type'          => 'hidden',
				'default_value' => sha1( $plugin_slug . time() ),
			);

		}

		return $plugin_fields;

	}

	/**
	 * Prepares system information report HTML for plugin settings page.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return string $html
	 */
	public function get_system_information_report() {

		$html  = '<p>';
		$html .= esc_html__( 'The following is a system report containing useful technical information for troubleshooting issues. If you need further help after using this Add-On, click on the "Copy System Report" button below to copy the report and paste it in your message to Gravity Forms support.', 'gravityformslogging' );
		$html .= '</p>';

		$html .= '<p><button class="btn button-secondary" type="button" data-clipboard-target="#gform_logging_report">' . esc_html__( 'Copy System Report', 'gravityformslogging' ) . '</button></p>';

		$html .= '<textarea id="gform_logging_report" readonly>' . $this->prepare_system_information_report() . '</textarea>';

		$html .= '<script type="text/javascript">( function() { new Clipboard( ".btn" ); } )();</script>';

		return $html;

	}

	/**
	 * Prepares the system information report.
	 *
	 * @since  1.1
	 * @access public
	 *
	 * @return string $report
	 */
	public function prepare_system_information_report() {

		global $wpdb;

		// Queue up needed information.
		$php_extensions         = get_loaded_extensions();
		$php_ini_get            = array( 'memory_limit', 'max_execution_time', 'upload_max_filesize', 'max_file_uploads', 'post_max_size', 'max_input_vars' );
		$mysql_version          = $wpdb->get_var( $wpdb->prepare( "SELECT VERSION() AS %s", 'version') );
		$db_character_set       = $wpdb->get_var( "SELECT @@character_set_database" );
		$db_collation           = $wpdb->get_var( "SELECT @@collation_database" );
		$plugins                = get_plugins();
		$active_plugins         = get_option( 'active_plugins', array() );
		$active_network_plugins = is_multisite() ? $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->sitemeta} WHERE meta_key=%s", 'active_sitewide_plugins' ) ) : array();
		$supported_plugins      = self::get_supported_plugins();
		$has_log_files          = false;

		// Begin report.
		$report  = '#### System Information Report ####' . "\n\n";

		// PHP information.
		$report .= '*** PHP Information ***' . "\n";
		$report .= 'Version: ' . phpversion() . "\n";
		foreach ( $php_ini_get as $ini_get ) {
			$report .= $ini_get .': ' . ini_get( $ini_get ) . "\n";
		}
		$report .= 'cURL Enabled: ' . ( function_exists( 'curl_init' ) ? 'Yes' : 'No' ) . "\n";
		$report .= 'Mcrypt Enabled: ' . ( function_exists( 'mcrypt_encrypt' ) ? 'Yes' : 'No' ) . "\n";
		$report .= 'Mbstring Enabled: ' . ( function_exists( 'mb_strlen' ) ? 'Yes' : 'No' ) . "\n";
		$report .= 'Loaded Extensions: ' . implode( ', ', $php_extensions ) . "\n\n";

		// MySQL information.
		$report .= '*** MySQL Information ***' . "\n";
		$report .= 'Version: ' . $mysql_version . "\n";
		$report .= 'Database Character Set: ' . $db_character_set . "\n";
		$report .= 'Database Collation: ' . $db_collation . "\n\n";

		// Web server information.
		$report .= '*** Web Server Information ***' . "\n";
		$report .= 'Software: ' . esc_html( $_SERVER['SERVER_SOFTWARE'] ) . "\n";
		$report .= 'Port: ' . esc_html( $_SERVER['SERVER_PORT'] ) . "\n";
		$report .= 'Document Root: ' . esc_html( $_SERVER['DOCUMENT_ROOT'] ) . "\n\n";

		// WordPress information.
		$report .= '*** WordPress Information ***' . "\n";
		$report .= 'WP_DEBUG: ' . ( WP_DEBUG ? 'Enabled' : 'Disabled' ) . "\n";
		$report .= 'WP_DEBUG_LOG: ' . ( WP_DEBUG_LOG ? 'Enabled' : 'Disabled' ) . "\n";
		$report .= 'WP_MEMORY_LIMIT: ' . WP_MEMORY_LIMIT . "\n";
		$report .= 'Multisite: ' . ( is_multisite() ? 'Enabled' : 'Disabled' ) . "\n";
		$report .= 'Site URL: ' . get_site_url() . "\n";
		$report .= 'Home URL: ' . get_home_url() . "\n\n";

		// Plugin information.
		$report .= '*** Active Plugins ***' . "\n";
		foreach ( $plugins as $plugin_path => $plugin ) {
			if ( in_array( $plugin_path, $active_plugins ) ) {
				$report .= $plugin['Name'] . ' (' . $plugin['Version'] . ')' . "\n";
			}
		}
		$report .= "\n";

		// Multisite plugin information.
		if ( is_multisite() && ! empty( $active_network_plugins ) ) {
			$report .= '*** Network Active Plugins ***' . "\n";
			$active_network_plugins = maybe_unserialize( $active_network_plugins );
			foreach ( $active_network_plugins as $plugin_slug => $activated ) {
				$plugin = get_plugin_data( WP_CONTENT_DIR . '/plugins/' . $plugin_slug );
				$report .= $plugin['Name'] . ' (' . $plugin['Version'] . ')' . "\n";
			}
			$report .= "\n";
		}

		// Log files.
		$report .= '*** Gravity Forms Log Files ***' . "\n";
		foreach ( $supported_plugins as $plugin_slug => $plugin_name ) {
			if ( self::log_file_exists( $plugin_slug ) ) {
				$has_log_files = true;
				$report .= $plugin_name .': ' . self::get_log_file_url( $plugin_slug ) . "\n";
			}
		}
		if ( ! $has_log_files ) {
			$report .= 'No logs are configured.' . "\n";
		}

		$report .= "\n" . '#### System Information Report ####';

		return $report;

	}

	/**
	 * Log message.
	 *
	 * @access public
	 * @static
	 * @param string   $plugin Plugin name.
	 * @param string   $message (default: null) Message to log.
	 * @param constant $message_type (default: KLogger::DEBUG) Message type.
	 */
	public static function log_message( $plugin, $message = null, $message_type = KLogger::DEBUG ) {

		// If message is empty, exit.
		if ( rgblank( $message ) || ! class_exists( 'GFForms' ) ) {
			return;
		}

		// Include KLogger library.
		self::include_logger();

		// Get logging setting for plugin.
		$plugin_setting = gf_logging()->get_plugin_setting( $plugin );
		$log_level      = $plugin_setting['log_level'];

		// If logging is turned off, exit.
		if ( rgblank( $log_level ) || KLogger::OFF === $log_level ) {
			return;
		}

		// Log message.
		$log = self::get_logger( $plugin, $log_level );
		$log->Log( $message, $message_type );

	}

	/**
	 * Delete log file for plugin.
	 *
	 * @since  1.1
	 * @access public
	 * @static
	 * @param  string $plugin_name Plugin name.
	 *
	 * @return bool If file was successfully deleted
	 */
	public static function delete_log_file( $plugin_name ) {

		// Get log file path.
		$log_file = self::get_log_file_name( $plugin_name );

		// Delete log file.
		return file_exists( $log_file ) ? unlink( $log_file ) : false;

	}

	/**
	 * Delete all log files and log file directory.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 */
	public static function delete_log_files() {

		$dir = self::get_log_dir();

		if ( is_dir( $dir ) ) {
			$files = glob( "{$dir}{,.}*", GLOB_BRACE ); // Get all file names.
			foreach ( $files as $file ) {
				if ( is_file( $file ) ) {
					unlink( $file ); // Delete file.
				}
			}
			rmdir( $dir );
		}

	}

	/**
	 * Get path to log file directory.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 *
	 * @return string
	 */
	public static function get_log_dir() {

		return GFFormsModel::get_upload_root() . 'logs/';

	}

	/**
	 * Get url to log file directory.
	 *
	 * @since  1.1
	 * @access public
	 * @static
	 *
	 * @return string
	 */
	public static function get_log_dir_url() {

		return GFFormsModel::get_upload_url_root() . 'logs/';

	}

	/**
	 * Get file name for plugin log file.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 * @param  string $plugin_name Plugin name.
	 *
	 * @return string File path to log file.
	 */
	public static function get_log_file_name( $plugin_name ) {

		$log_dir = self::get_log_dir();

		if ( ! file_exists( $log_dir ) ) {
			wp_mkdir_p( $log_dir );
			touch( $log_dir . 'index.html' );
		}

		$plugin_setting = gf_logging()->get_plugin_setting( $plugin_name );
		return $log_dir . $plugin_name . '_' . $plugin_setting['file_name'] . '.txt';

	}

	/**
	 * Get file url for plugin log file.
	 *
	 * @since  1.1
	 * @access public
	 * @static
	 * @param  string $plugin_name Plugin name.
	 *
	 * @return string URL to log file.
	 */
	public static function get_log_file_url( $plugin_name ) {

		$plugin_setting = gf_logging()->get_plugin_setting( $plugin_name );
		return self::get_log_dir_url() . $plugin_name . '_' . $plugin_setting['file_name'] . '.txt';

	}

	/**
	 * Check if log file exists for plugin.
	 *
	 * @since  1.1
	 * @access public
	 * @param  string $plugin_name Plugin slug.
	 * @return bool
	 */
	public static function log_file_exists( $plugin_name ) {

		$log_filename = self::get_log_file_name( $plugin_name );
		return file_exists( $log_filename );

	}

	/**
	 * Get log file size for plugin
	 *
	 * @since 1.2.1
	 * @access public
	 * @param  string $plugin_name Plugin slug.
	 *
	 * @return string File size with unit of measurement.
	 */
	public static function get_log_file_size( $plugin_name ) {

		// Get log file name.
		$file = self::get_log_file_name( $plugin_name );

		// Get log file size.
		$size = filesize( $file );

		// Convert log file size to human readable format.
		$units  = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$factor = floor( ( strlen( $size ) - 1 ) / 3 );
		$size   = sprintf( '%.2f', $size / pow( 1024, $factor ) ) . ' ' . $units[ $factor ];

		return $size;

	}

	/**
	 * Include KLogger library.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 */
	public static function include_logger() {

		if ( ! class_exists( 'KLogger' ) ) {
			require_once 'includes/KLogger.php';
		}

	}

	/**
	 * Get logging object for plugin.
	 *
	 * @since  1.0
	 * @access private
	 * @static
	 * @param  string   $plugin Plugin slug.
	 * @param  constant $log_level Level messages are being logged at.
	 *
	 * @return object $log
	 */
	private static function get_logger( $plugin, $log_level ) {

		if ( isset( self::$loggers[ $plugin ] ) ) {

			// Use existing logger.
			$log = self::$loggers[ $plugin ];

		} else {

			// Get time offset.
			$offset = get_option( 'gmt_offset' ) * 3600;

			// Get log file name.
			$log_file_name = self::get_log_file_name( $plugin );

			// Clean up log files.
			self::maybe_reset_logs( $log_file_name, $offset );

			// Create new logger class.
			$log = new KLogger( $log_file_name, $log_level, $offset, $plugin );

			// Set date format.
			$log->DateFormat = 'Y-m-d G:i:s.u';

			// Assign logger class to loggers array.
			self::$loggers[ $plugin ] = $log;

		}

		return $log;

	}

	/**
	 * Disable all logging.
	 *
	 * @since  1.0
	 * @access public
	 * @static
	 */
	public static function disable_logging() {

		gf_logging()->update_plugin_settings( array() );

	}

	/**
	 * Clean up log files.
	 *
	 * @since  1.1.5
	 * @access private
	 * @static
	 * @param  string $file_path Path to log.
	 * @param  string $gmt_offset GMT time offset.
	 */
	private static function maybe_reset_logs( $file_path, $gmt_offset ) {

		$path      = pathinfo( $file_path );
		$folder    = $path['dirname'] . '/';
		$file_base = $path['filename'];
		$file_ext  = $path['extension'];

		// Check size of current file. If greater than max file size, rename using time.
		if ( file_exists( $file_path ) && filesize( $file_path ) > self::$max_file_size ) {

			$adjusted_date = gmdate( self::$date_format_log_file, time() + $gmt_offset );
			$new_file_name = $file_base . '_' . $adjusted_date . '.' . $file_ext;
			rename( $file_path, $folder . $new_file_name );

		}

		// Get files which match the base name.
		$similar_files = glob( $folder . $file_base . '*.*' );
		$file_count    = count( $similar_files );

		// Check quantity of files and delete older ones if too many.
		if ( false !== $similar_files && $file_count > self::$max_file_count ) {

			// Sort by date so oldest are first.
			usort( $similar_files, create_function( '$a,$b', 'return filemtime($a) - filemtime($b);' ) );

			$delete_count = $file_count - self::$max_file_count;

			for ( $i = 0; $i < $delete_count; $i++ ) {
				if ( file_exists( $similar_files[ $i ] ) ) {
					unlink( $similar_files[ $i ] );
				}
			}

		}

	}

	/**
	 * Run necessary upgrade routines.
	 *
	 * @since  1.1
	 * @access public
	 * @param  string $previous_version The version of the Logging Add-On we're upgrading from.
	 */
	public function upgrade( $previous_version ) {

		// If previous version is empty, run pre Add-On Framework upgrade.
		if ( empty( $previous_version ) ) {
			$this->upgrade_from_pre_addon_framework();
		}

	}

	/**
	 * Upgrade plugin from pre Add-On Framework version.
	 *
	 * @since  1.1
	 * @access public
	 */
	public function upgrade_from_pre_addon_framework() {

		if ( is_multisite() ) {

			// Get network sites.
			$sites = wp_get_sites();

			foreach ( $sites as $site ) {

				// Get old settings.
				$old_settings = get_blog_option( $site['blog_id'], 'gf_logging_settings', array() );

				// If old settings don't exist, exit.
				if ( ! $old_settings ) {
					continue;
				}

				// Build new settings.
				$new_settings = array();

				foreach ( $old_settings as $plugin_slug => $log_level ) {
					$new_settings[ $plugin_slug ] = array(
						'log_level' => $log_level,
						'file_name' => sha1( $plugin_slug . time() ),
					);
				}

				// Save new settings.
				update_blog_option( $site['blog_id'], 'gravityformsaddon_' . $this->_slug . '_settings', $new_settings );

				// Delete old settings.
				delete_blog_option( $site['blog_id'], 'gf_logging_settings' );

			}

		} else {

			// Get old settings.
			$old_settings = get_option( 'gf_logging_settings' );

			// If old settings don't exist, exit.
			if ( ! $old_settings ) {
				return;
			}

			// Build new settings.
			$new_settings = array();

			foreach ( $old_settings as $plugin_slug => $log_level ) {
				$new_settings[ $plugin_slug ] = array(
					'log_level' => $log_level,
					'file_name' => sha1( $plugin_slug . time() ),
				);
			}

			// Save new settings.
			$this->update_plugin_settings( $new_settings );

			// Delete old settings.
			delete_option( 'gf_logging_settings' );

		}

	}

	/**
	 * The Logging Add-On itself does not support logging.
	 *
	 * @since  1.1
	 * @param  array $plugins The plugins which support logging.
	 *
	 * @return array
	 */
	public function set_logging_supported( $plugins ) {

		return $plugins;

	}

	/**
	 * Get list of plugins that support the Logging Add-On.
	 *
	 * @since  1.1
	 * @access public
	 * @static
	 * @return array $supported_plugins
	 */
	public static function get_supported_plugins() {

		$supported_plugins = apply_filters( 'gform_logging_supported', array() );
		asort( $supported_plugins );

		return $supported_plugins;

	}

}
