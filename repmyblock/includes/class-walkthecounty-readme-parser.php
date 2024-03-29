<?php
/**
 * WalkTheCounty Readme Parser
 *
 * @package     WalkTheCounty
 * @subpackage  Admin/Readme_Parser
 * @copyright   Copyright (c) 2018, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       2.1.4
 */
class WalkTheCounty_Readme_Parser{
	/**
	 * Readme file url
	 *
	 * @since  2.1.4
	 * @access private
	 * @var
	 */
	private $file_url;

	/**
	 * Readme file content
	 *
	 * @since  2.1.4
	 * @access private
	 * @var
	 */
	private $file_content;

	/**
	 * WalkTheCounty_Readme_Parser constructor.
	 *
	 * @param string $file_url
	 */
	function __construct( $file_url ) {
		$this->file_url     = $file_url;
		$this->file_content = wp_remote_retrieve_body( wp_remote_get( $this->file_url ) );
	}

	/**
	 * Get required WalkTheCounty core minimum version for addon
	 *
	 * @since 2.1.4
	 * @access public
	 *
	 * @return string
	 */
	public function requires_at_least() {
		// Regex to extract WalkTheCounty core minimum version from the readme.txt file.
		preg_match('|Requires WalkTheCounty:(.*)|i', $this->file_content, $_requires_at_least );

		if( is_array( $_requires_at_least ) && 1 < count( $_requires_at_least ) ) {
			$_requires_at_least = trim( $_requires_at_least[1] );
		}else{
			$_requires_at_least = null;
		}

		return $_requires_at_least;
	}
}
