<?php
/**
 * Country Functions
 *
 * @package     WalkTheCounty
 * @subpackage  Functions
 * @copyright   Copyright (c) 2016, WalkTheCountyWP
 * @license     https://opensource.org/licenses/gpl-license GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Get Site Base Country
 *
 * @since 1.0
 * @return string $country The two letter country code for the site's base country
 */
function walkthecounty_get_country() {
	$walkthecounty_options = walkthecounty_get_settings();
	$country      = isset( $walkthecounty_options['base_country'] ) ? $walkthecounty_options['base_country'] : 'US';

	return apply_filters( 'walkthecounty_walkthecounty_country', $country );
}

/**
 * Get Site Base State
 *
 * @since 1.0
 * @return string $state The site's base state name
 */
function walkthecounty_get_state() {
	$walkthecounty_options = walkthecounty_get_settings();
	$state        = isset( $walkthecounty_options['base_state'] ) ? $walkthecounty_options['base_state'] : false;

	return apply_filters( 'walkthecounty_walkthecounty_state', $state );
}

/**
 * Get Site States
 *
 * @since 1.0
 *
 * @param null $country
 *
 * @return mixed  A list of states for the site's base country.
 */
function walkthecounty_get_states( $country = null ) {
	// If Country have no states return empty array.
	$states = array();

	// Check if Country Code is empty or not.
	if ( empty( $country ) ) {
		// Get default country code that is being set by the admin.
		$country = walkthecounty_get_country();
	}

	// Get all the list of the states in array key format where key is the country code and value is the states that it contain.
	$states_list = walkthecounty_states_list();

	// Check if $country code exists in the array key.
	if ( array_key_exists( $country, $states_list ) ) {
		$states = $states_list[ $country ];
	}

	/**
	 * Filter the query in case tables are non-standard.
	 *
	 * @param string $query Database count query
	 */
	return (array) apply_filters( 'walkthecounty_walkthecounty_states', $states );
}

/**
 * Get Country List
 *
 * @since 1.0
 * @return array $countries A list of the available countries.
 */
function walkthecounty_get_country_list() {
	$countries = array(
		''   => '',
		'US' => esc_html__( 'United States', 'walkthecounty' ),
		'CA' => esc_html__( 'Canada', 'walkthecounty' ),
		'GB' => esc_html__( 'United Kingdom', 'walkthecounty' ),
		'AF' => esc_html__( 'Afghanistan', 'walkthecounty' ),
		'AL' => esc_html__( 'Albania', 'walkthecounty' ),
		'DZ' => esc_html__( 'Algeria', 'walkthecounty' ),
		'AS' => esc_html__( 'American Samoa', 'walkthecounty' ),
		'AD' => esc_html__( 'Andorra', 'walkthecounty' ),
		'AO' => esc_html__( 'Angola', 'walkthecounty' ),
		'AI' => esc_html__( 'Anguilla', 'walkthecounty' ),
		'AQ' => esc_html__( 'Antarctica', 'walkthecounty' ),
		'AG' => esc_html__( 'Antigua and Barbuda', 'walkthecounty' ),
		'AR' => esc_html__( 'Argentina', 'walkthecounty' ),
		'AM' => esc_html__( 'Armenia', 'walkthecounty' ),
		'AW' => esc_html__( 'Aruba', 'walkthecounty' ),
		'AU' => esc_html__( 'Australia', 'walkthecounty' ),
		'AT' => esc_html__( 'Austria', 'walkthecounty' ),
		'AZ' => esc_html__( 'Azerbaijan', 'walkthecounty' ),
		'BS' => esc_html__( 'Bahamas', 'walkthecounty' ),
		'BH' => esc_html__( 'Bahrain', 'walkthecounty' ),
		'BD' => esc_html__( 'Bangladesh', 'walkthecounty' ),
		'BB' => esc_html__( 'Barbados', 'walkthecounty' ),
		'BY' => esc_html__( 'Belarus', 'walkthecounty' ),
		'BE' => esc_html__( 'Belgium', 'walkthecounty' ),
		'BZ' => esc_html__( 'Belize', 'walkthecounty' ),
		'BJ' => esc_html__( 'Benin', 'walkthecounty' ),
		'BM' => esc_html__( 'Bermuda', 'walkthecounty' ),
		'BT' => esc_html__( 'Bhutan', 'walkthecounty' ),
		'BO' => esc_html__( 'Bolivia', 'walkthecounty' ),
		'BA' => esc_html__( 'Bosnia and Herzegovina', 'walkthecounty' ),
		'BW' => esc_html__( 'Botswana', 'walkthecounty' ),
		'BV' => esc_html__( 'Bouvet Island', 'walkthecounty' ),
		'BR' => esc_html__( 'Brazil', 'walkthecounty' ),
		'IO' => esc_html__( 'British Indian Ocean Territory', 'walkthecounty' ),
		'BN' => esc_html__( 'Brunei Darrussalam', 'walkthecounty' ),
		'BG' => esc_html__( 'Bulgaria', 'walkthecounty' ),
		'BF' => esc_html__( 'Burkina Faso', 'walkthecounty' ),
		'BI' => esc_html__( 'Burundi', 'walkthecounty' ),
		'KH' => esc_html__( 'Cambodia', 'walkthecounty' ),
		'CM' => esc_html__( 'Cameroon', 'walkthecounty' ),
		'CV' => esc_html__( 'Cape Verde', 'walkthecounty' ),
		'KY' => esc_html__( 'Cayman Islands', 'walkthecounty' ),
		'CF' => esc_html__( 'Central African Republic', 'walkthecounty' ),
		'TD' => esc_html__( 'Chad', 'walkthecounty' ),
		'CL' => esc_html__( 'Chile', 'walkthecounty' ),
		'CN' => esc_html__( 'China', 'walkthecounty' ),
		'CX' => esc_html__( 'Christmas Island', 'walkthecounty' ),
		'CC' => esc_html__( 'Cocos Islands', 'walkthecounty' ),
		'CO' => esc_html__( 'Colombia', 'walkthecounty' ),
		'KM' => esc_html__( 'Comoros', 'walkthecounty' ),
		'CD' => esc_html__( 'Congo, Democratic People\'s Republic', 'walkthecounty' ),
		'CG' => esc_html__( 'Congo, Republic of', 'walkthecounty' ),
		'CK' => esc_html__( 'Cook Islands', 'walkthecounty' ),
		'CR' => esc_html__( 'Costa Rica', 'walkthecounty' ),
		'CI' => esc_html__( 'Cote d\'Ivoire', 'walkthecounty' ),
		'HR' => esc_html__( 'Croatia/Hrvatska', 'walkthecounty' ),
		'CU' => esc_html__( 'Cuba', 'walkthecounty' ),
		'CY' => esc_html__( 'Cyprus Island', 'walkthecounty' ),
		'CZ' => esc_html__( 'Czech Republic', 'walkthecounty' ),
		'DK' => esc_html__( 'Denmark', 'walkthecounty' ),
		'DJ' => esc_html__( 'Djibouti', 'walkthecounty' ),
		'DM' => esc_html__( 'Dominica', 'walkthecounty' ),
		'DO' => esc_html__( 'Dominican Republic', 'walkthecounty' ),
		'TP' => esc_html__( 'East Timor', 'walkthecounty' ),
		'EC' => esc_html__( 'Ecuador', 'walkthecounty' ),
		'EG' => esc_html__( 'Egypt', 'walkthecounty' ),
		'GQ' => esc_html__( 'Equatorial Guinea', 'walkthecounty' ),
		'SV' => esc_html__( 'El Salvador', 'walkthecounty' ),
		'ER' => esc_html__( 'Eritrea', 'walkthecounty' ),
		'EE' => esc_html__( 'Estonia', 'walkthecounty' ),
		'ET' => esc_html__( 'Ethiopia', 'walkthecounty' ),
		'FK' => esc_html__( 'Falkland Islands', 'walkthecounty' ),
		'FO' => esc_html__( 'Faroe Islands', 'walkthecounty' ),
		'FJ' => esc_html__( 'Fiji', 'walkthecounty' ),
		'FI' => esc_html__( 'Finland', 'walkthecounty' ),
		'FR' => esc_html__( 'France', 'walkthecounty' ),
		'GF' => esc_html__( 'French Guiana', 'walkthecounty' ),
		'PF' => esc_html__( 'French Polynesia', 'walkthecounty' ),
		'TF' => esc_html__( 'French Southern Territories', 'walkthecounty' ),
		'GA' => esc_html__( 'Gabon', 'walkthecounty' ),
		'GM' => esc_html__( 'Gambia', 'walkthecounty' ),
		'GE' => esc_html__( 'Georgia', 'walkthecounty' ),
		'DE' => esc_html__( 'Germany', 'walkthecounty' ),
		'GR' => esc_html__( 'Greece', 'walkthecounty' ),
		'GH' => esc_html__( 'Ghana', 'walkthecounty' ),
		'GI' => esc_html__( 'Gibraltar', 'walkthecounty' ),
		'GL' => esc_html__( 'Greenland', 'walkthecounty' ),
		'GD' => esc_html__( 'Grenada', 'walkthecounty' ),
		'GP' => esc_html__( 'Guadeloupe', 'walkthecounty' ),
		'GU' => esc_html__( 'Guam', 'walkthecounty' ),
		'GT' => esc_html__( 'Guatemala', 'walkthecounty' ),
		'GG' => esc_html__( 'Guernsey', 'walkthecounty' ),
		'GN' => esc_html__( 'Guinea', 'walkthecounty' ),
		'GW' => esc_html__( 'Guinea-Bissau', 'walkthecounty' ),
		'GY' => esc_html__( 'Guyana', 'walkthecounty' ),
		'HT' => esc_html__( 'Haiti', 'walkthecounty' ),
		'HM' => esc_html__( 'Heard and McDonald Islands', 'walkthecounty' ),
		'VA' => esc_html__( 'Holy See (City Vatican State)', 'walkthecounty' ),
		'HN' => esc_html__( 'Honduras', 'walkthecounty' ),
		'HK' => esc_html__( 'Hong Kong', 'walkthecounty' ),
		'HU' => esc_html__( 'Hungary', 'walkthecounty' ),
		'IS' => esc_html__( 'Iceland', 'walkthecounty' ),
		'IN' => esc_html__( 'India', 'walkthecounty' ),
		'ID' => esc_html__( 'Indonesia', 'walkthecounty' ),
		'IR' => esc_html__( 'Iran', 'walkthecounty' ),
		'IQ' => esc_html__( 'Iraq', 'walkthecounty' ),
		'IE' => esc_html__( 'Ireland', 'walkthecounty' ),
		'IM' => esc_html__( 'Isle of Man', 'walkthecounty' ),
		'IL' => esc_html__( 'Israel', 'walkthecounty' ),
		'IT' => esc_html__( 'Italy', 'walkthecounty' ),
		'JM' => esc_html__( 'Jamaica', 'walkthecounty' ),
		'JP' => esc_html__( 'Japan', 'walkthecounty' ),
		'JE' => esc_html__( 'Jersey', 'walkthecounty' ),
		'JO' => esc_html__( 'Jordan', 'walkthecounty' ),
		'KZ' => esc_html__( 'Kazakhstan', 'walkthecounty' ),
		'KE' => esc_html__( 'Kenya', 'walkthecounty' ),
		'KI' => esc_html__( 'Kiribati', 'walkthecounty' ),
		'KW' => esc_html__( 'Kuwait', 'walkthecounty' ),
		'KG' => esc_html__( 'Kyrgyzstan', 'walkthecounty' ),
		'LA' => esc_html__( 'Lao People\'s Democratic Republic', 'walkthecounty' ),
		'LV' => esc_html__( 'Latvia', 'walkthecounty' ),
		'LB' => esc_html__( 'Lebanon', 'walkthecounty' ),
		'LS' => esc_html__( 'Lesotho', 'walkthecounty' ),
		'LR' => esc_html__( 'Liberia', 'walkthecounty' ),
		'LY' => esc_html__( 'Libyan Arab Jamahiriya', 'walkthecounty' ),
		'LI' => esc_html__( 'Liechtenstein', 'walkthecounty' ),
		'LT' => esc_html__( 'Lithuania', 'walkthecounty' ),
		'LU' => esc_html__( 'Luxembourg', 'walkthecounty' ),
		'MO' => esc_html__( 'Macau', 'walkthecounty' ),
		'MK' => esc_html__( 'Macedonia', 'walkthecounty' ),
		'MG' => esc_html__( 'Madagascar', 'walkthecounty' ),
		'MW' => esc_html__( 'Malawi', 'walkthecounty' ),
		'MY' => esc_html__( 'Malaysia', 'walkthecounty' ),
		'MV' => esc_html__( 'Maldives', 'walkthecounty' ),
		'ML' => esc_html__( 'Mali', 'walkthecounty' ),
		'MT' => esc_html__( 'Malta', 'walkthecounty' ),
		'MH' => esc_html__( 'Marshall Islands', 'walkthecounty' ),
		'MQ' => esc_html__( 'Martinique', 'walkthecounty' ),
		'MR' => esc_html__( 'Mauritania', 'walkthecounty' ),
		'MU' => esc_html__( 'Mauritius', 'walkthecounty' ),
		'YT' => esc_html__( 'Mayotte', 'walkthecounty' ),
		'MX' => esc_html__( 'Mexico', 'walkthecounty' ),
		'FM' => esc_html__( 'Micronesia', 'walkthecounty' ),
		'MD' => esc_html__( 'Moldova, Republic of', 'walkthecounty' ),
		'MC' => esc_html__( 'Monaco', 'walkthecounty' ),
		'MN' => esc_html__( 'Mongolia', 'walkthecounty' ),
		'ME' => esc_html__( 'Montenegro', 'walkthecounty' ),
		'MS' => esc_html__( 'Montserrat', 'walkthecounty' ),
		'MA' => esc_html__( 'Morocco', 'walkthecounty' ),
		'MZ' => esc_html__( 'Mozambique', 'walkthecounty' ),
		'MM' => esc_html__( 'Myanmar', 'walkthecounty' ),
		'NA' => esc_html__( 'Namibia', 'walkthecounty' ),
		'NR' => esc_html__( 'Nauru', 'walkthecounty' ),
		'NP' => esc_html__( 'Nepal', 'walkthecounty' ),
		'NL' => esc_html__( 'Netherlands', 'walkthecounty' ),
		'AN' => esc_html__( 'Netherlands Antilles', 'walkthecounty' ),
		'NC' => esc_html__( 'New Caledonia', 'walkthecounty' ),
		'NZ' => esc_html__( 'New Zealand', 'walkthecounty' ),
		'NI' => esc_html__( 'Nicaragua', 'walkthecounty' ),
		'NE' => esc_html__( 'Niger', 'walkthecounty' ),
		'NG' => esc_html__( 'Nigeria', 'walkthecounty' ),
		'NU' => esc_html__( 'Niue', 'walkthecounty' ),
		'NF' => esc_html__( 'Norfolk Island', 'walkthecounty' ),
		'KP' => esc_html__( 'North Korea', 'walkthecounty' ),
		'MP' => esc_html__( 'Northern Mariana Islands', 'walkthecounty' ),
		'NO' => esc_html__( 'Norway', 'walkthecounty' ),
		'OM' => esc_html__( 'Oman', 'walkthecounty' ),
		'PK' => esc_html__( 'Pakistan', 'walkthecounty' ),
		'PW' => esc_html__( 'Palau', 'walkthecounty' ),
		'PS' => esc_html__( 'Palestinian Territories', 'walkthecounty' ),
		'PA' => esc_html__( 'Panama', 'walkthecounty' ),
		'PG' => esc_html__( 'Papua New Guinea', 'walkthecounty' ),
		'PY' => esc_html__( 'Paraguay', 'walkthecounty' ),
		'PE' => esc_html__( 'Peru', 'walkthecounty' ),
		'PH' => esc_html__( 'Philippines', 'walkthecounty' ),
		'PN' => esc_html__( 'Pitcairn Island', 'walkthecounty' ),
		'PL' => esc_html__( 'Poland', 'walkthecounty' ),
		'PT' => esc_html__( 'Portugal', 'walkthecounty' ),
		'PR' => esc_html__( 'Puerto Rico', 'walkthecounty' ),
		'QA' => esc_html__( 'Qatar', 'walkthecounty' ),
		'RE' => esc_html__( 'Reunion Island', 'walkthecounty' ),
		'RO' => esc_html__( 'Romania', 'walkthecounty' ),
		'RU' => esc_html__( 'Russian Federation', 'walkthecounty' ),
		'RW' => esc_html__( 'Rwanda', 'walkthecounty' ),
		'SH' => esc_html__( 'Saint Helena', 'walkthecounty' ),
		'KN' => esc_html__( 'Saint Kitts and Nevis', 'walkthecounty' ),
		'LC' => esc_html__( 'Saint Lucia', 'walkthecounty' ),
		'PM' => esc_html__( 'Saint Pierre and Miquelon', 'walkthecounty' ),
		'VC' => esc_html__( 'Saint Vincent and the Grenadines', 'walkthecounty' ),
		'SM' => esc_html__( 'San Marino', 'walkthecounty' ),
		'ST' => esc_html__( 'Sao Tome and Principe', 'walkthecounty' ),
		'SA' => esc_html__( 'Saudi Arabia', 'walkthecounty' ),
		'SN' => esc_html__( 'Senegal', 'walkthecounty' ),
		'RS' => esc_html__( 'Serbia', 'walkthecounty' ),
		'SC' => esc_html__( 'Seychelles', 'walkthecounty' ),
		'SL' => esc_html__( 'Sierra Leone', 'walkthecounty' ),
		'SG' => esc_html__( 'Singapore', 'walkthecounty' ),
		'SK' => esc_html__( 'Slovak Republic', 'walkthecounty' ),
		'SI' => esc_html__( 'Slovenia', 'walkthecounty' ),
		'SB' => esc_html__( 'Solomon Islands', 'walkthecounty' ),
		'SO' => esc_html__( 'Somalia', 'walkthecounty' ),
		'ZA' => esc_html__( 'South Africa', 'walkthecounty' ),
		'GS' => esc_html__( 'South Georgia', 'walkthecounty' ),
		'KR' => esc_html__( 'South Korea', 'walkthecounty' ),
		'ES' => esc_html__( 'Spain', 'walkthecounty' ),
		'LK' => esc_html__( 'Sri Lanka', 'walkthecounty' ),
		'SD' => esc_html__( 'Sudan', 'walkthecounty' ),
		'SR' => esc_html__( 'Suriname', 'walkthecounty' ),
		'SJ' => esc_html__( 'Svalbard and Jan Mayen Islands', 'walkthecounty' ),
		'SZ' => esc_html__( 'Eswatini', 'walkthecounty' ),
		'SE' => esc_html__( 'Sweden', 'walkthecounty' ),
		'CH' => esc_html__( 'Switzerland', 'walkthecounty' ),
		'SY' => esc_html__( 'Syrian Arab Republic', 'walkthecounty' ),
		'TW' => esc_html__( 'Taiwan', 'walkthecounty' ),
		'TJ' => esc_html__( 'Tajikistan', 'walkthecounty' ),
		'TZ' => esc_html__( 'Tanzania', 'walkthecounty' ),
		'TG' => esc_html__( 'Togo', 'walkthecounty' ),
		'TK' => esc_html__( 'Tokelau', 'walkthecounty' ),
		'TO' => esc_html__( 'Tonga', 'walkthecounty' ),
		'TH' => esc_html__( 'Thailand', 'walkthecounty' ),
		'TT' => esc_html__( 'Trinidad and Tobago', 'walkthecounty' ),
		'TN' => esc_html__( 'Tunisia', 'walkthecounty' ),
		'TR' => esc_html__( 'Turkey', 'walkthecounty' ),
		'TM' => esc_html__( 'Turkmenistan', 'walkthecounty' ),
		'TC' => esc_html__( 'Turks and Caicos Islands', 'walkthecounty' ),
		'TV' => esc_html__( 'Tuvalu', 'walkthecounty' ),
		'UG' => esc_html__( 'Uganda', 'walkthecounty' ),
		'UA' => esc_html__( 'Ukraine', 'walkthecounty' ),
		'AE' => esc_html__( 'United Arab Emirates', 'walkthecounty' ),
		'UY' => esc_html__( 'Uruguay', 'walkthecounty' ),
		'UM' => esc_html__( 'US Minor Outlying Islands', 'walkthecounty' ),
		'UZ' => esc_html__( 'Uzbekistan', 'walkthecounty' ),
		'VU' => esc_html__( 'Vanuatu', 'walkthecounty' ),
		'VE' => esc_html__( 'Venezuela', 'walkthecounty' ),
		'VN' => esc_html__( 'Vietnam', 'walkthecounty' ),
		'VG' => esc_html__( 'Virgin Islands (British)', 'walkthecounty' ),
		'VI' => esc_html__( 'Virgin Islands (USA)', 'walkthecounty' ),
		'WF' => esc_html__( 'Wallis and Futuna Islands', 'walkthecounty' ),
		'EH' => esc_html__( 'Western Sahara', 'walkthecounty' ),
		'WS' => esc_html__( 'Western Samoa', 'walkthecounty' ),
		'YE' => esc_html__( 'Yemen', 'walkthecounty' ),
		'YU' => esc_html__( 'Yugoslavia', 'walkthecounty' ),
		'ZM' => esc_html__( 'Zambia', 'walkthecounty' ),
		'ZW' => esc_html__( 'Zimbabwe', 'walkthecounty' ),
	);

	return (array) apply_filters( 'walkthecounty_countries', $countries );
}

/**
 * Get States List.
 *
 * @since 1.8.11
 *
 * @return array $states A list of the available states as in array key format.
 */
function walkthecounty_states_list() {
	$states = array(
		'US' => walkthecounty_get_states_list(),
		'CA' => walkthecounty_get_provinces_list(),
		'AU' => walkthecounty_get_australian_states_list(),
		'BR' => walkthecounty_get_brazil_states_list(),
		'CN' => walkthecounty_get_chinese_states_list(),
		'HK' => walkthecounty_get_hong_kong_states_list(),
		'HU' => walkthecounty_get_hungary_states_list(),
		'ID' => walkthecounty_get_indonesian_states_list(),
		'IN' => walkthecounty_get_indian_states_list(),
		'MY' => walkthecounty_get_malaysian_states_list(),
		'NZ' => walkthecounty_get_new_zealand_states_list(),
		'TH' => walkthecounty_get_thailand_states_list(),
		'ZA' => walkthecounty_get_south_african_states_list(),
		'ES' => walkthecounty_get_spain_states_list(),
		'TR' => walkthecounty_get_turkey_states_list(),
		'RO' => walkthecounty_get_romania_states_list(),
		'PK' => walkthecounty_get_pakistan_states_list(),
		'PH' => walkthecounty_get_philippines_states_list(),
		'PE' => walkthecounty_get_peru_states_list(),
		'NP' => walkthecounty_get_nepal_states_list(),
		'NG' => walkthecounty_get_nigerian_states_list(),
		'MX' => walkthecounty_get_mexico_states_list(),
		'JP' => walkthecounty_get_japan_states_list(),
		'IT' => walkthecounty_get_italy_states_list(),
		'IR' => walkthecounty_get_iran_states_list(),
		'IE' => walkthecounty_get_ireland_states_list(),
		'GR' => walkthecounty_get_greek_states_list(),
		'BO' => walkthecounty_get_bolivian_states_list(),
		'BG' => walkthecounty_get_bulgarian_states_list(),
		'BD' => walkthecounty_get_bangladeshi_states_list(),
		'AR' => walkthecounty_get_argentina_states_list(),
	);

	/**
	 * Filter can be used to add or remove the States from the Country.
	 *
	 * Filters can be use to add states inside the country all the states will be in array format ans the array key will be country code.
	 *
	 * @since 1.8.11
	 *
	 * @param array $states Contain the list of states in array key format where key of the array is there respected country code.
	 */
	return (array) apply_filters( 'walkthecounty_states_list', $states );
}

/**
 * List of Country that have no states init.
 *
 * There are some country which does not have states init Example: germany.
 *
 * @since 1.8.11
 *
 * $$country array $country_code.
 */
function walkthecounty_no_states_country_list() {
	$country_list = array();
	$locale       = walkthecounty_get_country_locale();
	foreach ( $locale as $key => $value ) {
		if ( ! empty( $value['state'] ) && isset( $value['state']['hidden'] ) && true === $value['state']['hidden'] ) {
			$country_list[ $key ] = $value['state'];
		}
	}

	/**
	 * Filter can be used to add or remove the Country that does not have states init.
	 *
	 * @since 1.8.11
	 *
	 * @param array $country Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'walkthecounty_no_states_country_list', $country_list );
}

/**
 * List of Country in which states fields is not required.
 *
 * There are some country in which states fields is not required Example: United Kingdom ( uk ).
 *
 * @since 1.8.11
 *
 * $country array $country_code.
 */
function walkthecounty_states_not_required_country_list() {
	$country_list = array();
	$locale       = walkthecounty_get_country_locale();
	foreach ( $locale as $key => $value ) {
		if ( ! empty( $value['state'] ) && isset( $value['state']['required'] ) && false === $value['state']['required'] ) {
			$country_list[ $key ] = $value['state'];
		}
	}

	/**
	 * Filter can be used to add or remove the Country in which states fields is not required.
	 *
	 * @since 1.8.11
	 *
	 * @param array $country Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'walkthecounty_states_not_required_country_list', $country_list );
}

/**
 * List of Country in which city fields is not required.
 *
 * There are some country in which city fields is not required Example: Singapore ( sk ).
 *
 * @since 2.3.0
 *
 * $country array $country_list.
 */
function walkthecounty_city_not_required_country_list() {
	$country_list = array();
	$locale       = walkthecounty_get_country_locale();
	foreach ( $locale as $key => $value ) {
		if ( ! empty( $value['city'] ) && isset( $value['city']['required'] ) && false === $value['city']['required'] ) {
			$country_list[ $key ] = $value['city'];
		}
	}

	/**
	 * Filter can be used to add or remove the Country in which city fields is not required.
	 *
	 * @since 2.3.0
	 *
	 * @param array $country_list Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'walkthecounty_city_not_required_country_list', $country_list );
}

/**
 * Get the country name by list key.
 *
 * @since 1.8.12
 *
 * @param string $key
 *
 * @return string|bool
 */
function walkthecounty_get_country_name_by_key( $key ) {
	$country_list = walkthecounty_get_country_list();

	if ( array_key_exists( $key, $country_list ) ) {
		return $country_list[ $key ];
	}

	return false;
}

/**
 * Get the label that need to show as an placeholder.
 *
 * @ since 1.8.12
 *
 * @return array $country_states_label
 */
function walkthecounty_get_states_label() {
	$country_states_label = array();
	$default_label        = __( 'State', 'walkthecounty' );
	$locale               = walkthecounty_get_country_locale();
	foreach ( $locale as $key => $value ) {
		$label = $default_label;
		if ( ! empty( $value['state'] ) && ! empty( $value['state']['label'] ) ) {
			$label = $value['state']['label'];
		}
		$country_states_label[ $key ] = $label;
	}

	/**
	 * Filter can be used to add or remove the Country that does not have states init.
	 *
	 * @since 1.8.11
	 *
	 * @param array $country Contain key as there country code & value as there country name.
	 */
	return (array) apply_filters( 'walkthecounty_get_states_label', $country_states_label );
}

/**
 * Get country locale settings.
 *
 * @since 1.8.12
 *
 * @return array
 */
function walkthecounty_get_country_locale() {
	return (array) apply_filters( 'walkthecounty_get_country_locale', array(
		'AE' => array(
			'state' => array(
				'required' => false,
			),
		),
		'AF' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'AT' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'AU' => array(
			'state' => array(
				'label' => __( 'State', 'walkthecounty' ),
			),
		),
		'AX' => array(
			'state' => array(
				'required' => false,
			),
		),
		'BD' => array(
			'state' => array(
				'label' => __( 'District', 'walkthecounty' ),
			),
		),
		'BE' => array(
			'state' => array(
				'required' => false,
				'label'    => __( 'Province', 'walkthecounty' ),
				'hidden'   => true,
			),
		),
		'BI' => array(
			'state' => array(
				'required' => false,
			),
		),
		'CA' => array(
			'state' => array(
				'label' => __( 'Province', 'walkthecounty' ),
			),
		),
		'CH' => array(
			'state' => array(
				'label'    => __( 'Canton', 'walkthecounty' ),
				'required' => false,
				'hidden'   => true,
			),
		),
		'CL' => array(
			'state' => array(
				'label' => __( 'Region', 'walkthecounty' ),
			),
		),
		'CN' => array(
			'state' => array(
				'label' => __( 'Province', 'walkthecounty' ),
			),
		),
		'CZ' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'DE' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'DK' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'EE' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'FI' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'FR' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'GP' => array(
			'state' => array(
				'required' => false,
			),
		),
		'GF' => array(
			'state' => array(
				'required' => false,
			),
		),
		'HK' => array(
			'state' => array(
				'label' => __( 'Region', 'walkthecounty' ),
			),
		),
		'HU' => array(
			'state' => array(
				'label'  => __( 'County', 'walkthecounty' ),
				'hidden' => true,
			),
		),
		'ID' => array(
			'state' => array(
				'label' => __( 'Province', 'walkthecounty' ),
			),
		),
		'IE' => array(
			'state' => array(
				'label' => __( 'County', 'walkthecounty' ),
			),
		),
		'IS' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'IL' => array(
			'state' => array(
				'required' => false,
			),
		),
		'IT' => array(
			'state' => array(
				'required' => true,
				'label'    => __( 'Province', 'walkthecounty' ),
			),
		),
		'JP' => array(
			'state' => array(
				'label' => __( 'Prefecture', 'walkthecounty' ),
			),
		),
		'KR' => array(
			'state' => array(
				'required' => false,
			),
		),
		'KW' => array(
			'state' => array(
				'required' => false,
			),
		),
		'LB' => array(
			'state' => array(
				'required' => false,
			),
		),
		'MQ' => array(
			'state' => array(
				'required' => false,
			),
		),
		'NL' => array(
			'state' => array(
				'required' => false,
				'label'    => __( 'Province', 'walkthecounty' ),
				'hidden'   => true,
			),
		),
		'NZ' => array(
			'state' => array(
				'label' => __( 'Region', 'walkthecounty' ),
			),
		),
		'NO' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'NP' => array(
			'state' => array(
				'label' => __( 'State / Zone', 'walkthecounty' ),
			),
		),
		'PL' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'PT' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'RE' => array(
			'state' => array(
				'required' => false,
			),
		),
		'RO' => array(
			'state' => array(
				'required' => false,
			),
		),
		'SG' => array(
			'state' => array(
				'required' => false,
			),
			'city'  => array(
				'required' => false,
			)
		),
		'SK' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'SI' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'ES' => array(
			'state' => array(
				'label' => __( 'Province', 'walkthecounty' ),
			),
		),
		'LI' => array(
			'state' => array(
				'label'    => __( 'Municipality', 'walkthecounty' ),
				'required' => false,
				'hidden'   => true,
			),
		),
		'LK' => array(
			'state' => array(
				'required' => false,
			),
		),
		'SE' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'TR' => array(
			'state' => array(
				'label' => __( 'Province', 'walkthecounty' ),
			),
		),
		'US' => array(
			'state' => array(
				'label' => __( 'State', 'walkthecounty' ),
			),
		),
		'GB' => array(
			'state' => array(
				'label'    => __( 'County', 'walkthecounty' ),
				'required' => false,
			),
		),
		'VN' => array(
			'state' => array(
				'required' => false,
				'hidden'   => true,
			),
		),
		'YT' => array(
			'state' => array(
				'required' => false,
			),
		),
		'ZA' => array(
			'state' => array(
				'label' => __( 'Province', 'walkthecounty' ),
			),
		),
		'PA' => array(
			'state' => array(
				'required' => true,
			),
		),
	) );
}

/**
 * Get Turkey States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_turkey_states_list() {
	$states = array(
		''     => '',
		'TR01' => __( 'Adana', 'walkthecounty' ),
		'TR02' => __( 'Ad&#305;yaman', 'walkthecounty' ),
		'TR03' => __( 'Afyon', 'walkthecounty' ),
		'TR04' => __( 'A&#287;r&#305;', 'walkthecounty' ),
		'TR05' => __( 'Amasya', 'walkthecounty' ),
		'TR06' => __( 'Ankara', 'walkthecounty' ),
		'TR07' => __( 'Antalya', 'walkthecounty' ),
		'TR08' => __( 'Artvin', 'walkthecounty' ),
		'TR09' => __( 'Ayd&#305;n', 'walkthecounty' ),
		'TR10' => __( 'Bal&#305;kesir', 'walkthecounty' ),
		'TR11' => __( 'Bilecik', 'walkthecounty' ),
		'TR12' => __( 'Bing&#246;l', 'walkthecounty' ),
		'TR13' => __( 'Bitlis', 'walkthecounty' ),
		'TR14' => __( 'Bolu', 'walkthecounty' ),
		'TR15' => __( 'Burdur', 'walkthecounty' ),
		'TR16' => __( 'Bursa', 'walkthecounty' ),
		'TR17' => __( '&#199;anakkale', 'walkthecounty' ),
		'TR18' => __( '&#199;ank&#305;r&#305;', 'walkthecounty' ),
		'TR19' => __( '&#199;orum', 'walkthecounty' ),
		'TR20' => __( 'Denizli', 'walkthecounty' ),
		'TR21' => __( 'Diyarbak&#305;r', 'walkthecounty' ),
		'TR22' => __( 'Edirne', 'walkthecounty' ),
		'TR23' => __( 'Elaz&#305;&#287;', 'walkthecounty' ),
		'TR24' => __( 'Erzincan', 'walkthecounty' ),
		'TR25' => __( 'Erzurum', 'walkthecounty' ),
		'TR26' => __( 'Eski&#351;ehir', 'walkthecounty' ),
		'TR27' => __( 'Gaziantep', 'walkthecounty' ),
		'TR28' => __( 'Giresun', 'walkthecounty' ),
		'TR29' => __( 'G&#252;m&#252;&#351;hane', 'walkthecounty' ),
		'TR30' => __( 'Hakkari', 'walkthecounty' ),
		'TR31' => __( 'Hatay', 'walkthecounty' ),
		'TR32' => __( 'Isparta', 'walkthecounty' ),
		'TR33' => __( '&#304;&#231;el', 'walkthecounty' ),
		'TR34' => __( '&#304;stanbul', 'walkthecounty' ),
		'TR35' => __( '&#304;zmir', 'walkthecounty' ),
		'TR36' => __( 'Kars', 'walkthecounty' ),
		'TR37' => __( 'Kastamonu', 'walkthecounty' ),
		'TR38' => __( 'Kayseri', 'walkthecounty' ),
		'TR39' => __( 'K&#305;rklareli', 'walkthecounty' ),
		'TR40' => __( 'K&#305;r&#351;ehir', 'walkthecounty' ),
		'TR41' => __( 'Kocaeli', 'walkthecounty' ),
		'TR42' => __( 'Konya', 'walkthecounty' ),
		'TR43' => __( 'K&#252;tahya', 'walkthecounty' ),
		'TR44' => __( 'Malatya', 'walkthecounty' ),
		'TR45' => __( 'Manisa', 'walkthecounty' ),
		'TR46' => __( 'Kahramanmara&#351;', 'walkthecounty' ),
		'TR47' => __( 'Mardin', 'walkthecounty' ),
		'TR48' => __( 'Mu&#287;la', 'walkthecounty' ),
		'TR49' => __( 'Mu&#351;', 'walkthecounty' ),
		'TR50' => __( 'Nev&#351;ehir', 'walkthecounty' ),
		'TR51' => __( 'Ni&#287;de', 'walkthecounty' ),
		'TR52' => __( 'Ordu', 'walkthecounty' ),
		'TR53' => __( 'Rize', 'walkthecounty' ),
		'TR54' => __( 'Sakarya', 'walkthecounty' ),
		'TR55' => __( 'Samsun', 'walkthecounty' ),
		'TR56' => __( 'Siirt', 'walkthecounty' ),
		'TR57' => __( 'Sinop', 'walkthecounty' ),
		'TR58' => __( 'Sivas', 'walkthecounty' ),
		'TR59' => __( 'Tekirda&#287;', 'walkthecounty' ),
		'TR60' => __( 'Tokat', 'walkthecounty' ),
		'TR61' => __( 'Trabzon', 'walkthecounty' ),
		'TR62' => __( 'Tunceli', 'walkthecounty' ),
		'TR63' => __( '&#350;anl&#305;urfa', 'walkthecounty' ),
		'TR64' => __( 'U&#351;ak', 'walkthecounty' ),
		'TR65' => __( 'Van', 'walkthecounty' ),
		'TR66' => __( 'Yozgat', 'walkthecounty' ),
		'TR67' => __( 'Zonguldak', 'walkthecounty' ),
		'TR68' => __( 'Aksaray', 'walkthecounty' ),
		'TR69' => __( 'Bayburt', 'walkthecounty' ),
		'TR70' => __( 'Karaman', 'walkthecounty' ),
		'TR71' => __( 'K&#305;r&#305;kkale', 'walkthecounty' ),
		'TR72' => __( 'Batman', 'walkthecounty' ),
		'TR73' => __( '&#350;&#305;rnak', 'walkthecounty' ),
		'TR74' => __( 'Bart&#305;n', 'walkthecounty' ),
		'TR75' => __( 'Ardahan', 'walkthecounty' ),
		'TR76' => __( 'I&#287;d&#305;r', 'walkthecounty' ),
		'TR77' => __( 'Yalova', 'walkthecounty' ),
		'TR78' => __( 'Karab&#252;k', 'walkthecounty' ),
		'TR79' => __( 'Kilis', 'walkthecounty' ),
		'TR80' => __( 'Osmaniye', 'walkthecounty' ),
		'TR81' => __( 'D&#252;zce', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_turkey_states', $states );
}

/**
 * Get Romania States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_romania_states_list() {
	$states = array(
		''   => '',
		'AB' => __( 'Alba', 'walkthecounty' ),
		'AR' => __( 'Arad', 'walkthecounty' ),
		'AG' => __( 'Arges', 'walkthecounty' ),
		'BC' => __( 'Bacau', 'walkthecounty' ),
		'BH' => __( 'Bihor', 'walkthecounty' ),
		'BN' => __( 'Bistrita-Nasaud', 'walkthecounty' ),
		'BT' => __( 'Botosani', 'walkthecounty' ),
		'BR' => __( 'Braila', 'walkthecounty' ),
		'BV' => __( 'Brasov', 'walkthecounty' ),
		'B'  => __( 'Bucuresti', 'walkthecounty' ),
		'BZ' => __( 'Buzau', 'walkthecounty' ),
		'CL' => __( 'Calarasi', 'walkthecounty' ),
		'CS' => __( 'Caras-Severin', 'walkthecounty' ),
		'CJ' => __( 'Cluj', 'walkthecounty' ),
		'CT' => __( 'Constanta', 'walkthecounty' ),
		'CV' => __( 'Covasna', 'walkthecounty' ),
		'DB' => __( 'Dambovita', 'walkthecounty' ),
		'DJ' => __( 'Dolj', 'walkthecounty' ),
		'GL' => __( 'Galati', 'walkthecounty' ),
		'GR' => __( 'Giurgiu', 'walkthecounty' ),
		'GJ' => __( 'Gorj', 'walkthecounty' ),
		'HR' => __( 'Harghita', 'walkthecounty' ),
		'HD' => __( 'Hunedoara', 'walkthecounty' ),
		'IL' => __( 'Ialomita', 'walkthecounty' ),
		'IS' => __( 'Iasi', 'walkthecounty' ),
		'IF' => __( 'Ilfov', 'walkthecounty' ),
		'MM' => __( 'Maramures', 'walkthecounty' ),
		'MH' => __( 'Mehedinti', 'walkthecounty' ),
		'MS' => __( 'Mures', 'walkthecounty' ),
		'NT' => __( 'Neamt', 'walkthecounty' ),
		'OT' => __( 'Olt', 'walkthecounty' ),
		'PH' => __( 'Prahova', 'walkthecounty' ),
		'SJ' => __( 'Salaj', 'walkthecounty' ),
		'SM' => __( 'Satu Mare', 'walkthecounty' ),
		'SB' => __( 'Sibiu', 'walkthecounty' ),
		'SV' => __( 'Suceava', 'walkthecounty' ),
		'TR' => __( 'Teleorman', 'walkthecounty' ),
		'TM' => __( 'Timis', 'walkthecounty' ),
		'TL' => __( 'Tulcea', 'walkthecounty' ),
		'VL' => __( 'Valcea', 'walkthecounty' ),
		'VS' => __( 'Vaslui', 'walkthecounty' ),
		'VN' => __( 'Vrancea', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_romania_states', $states );
}

/**
 * Get Pakistan States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_pakistan_states_list() {
	$states = array(
		''   => '',
		'JK' => __( 'Azad Kashmir', 'walkthecounty' ),
		'BA' => __( 'Balochistan', 'walkthecounty' ),
		'TA' => __( 'FATA', 'walkthecounty' ),
		'GB' => __( 'Gilgit Baltistan', 'walkthecounty' ),
		'IS' => __( 'Islamabad Capital Territory', 'walkthecounty' ),
		'KP' => __( 'Khyber Pakhtunkhwa', 'walkthecounty' ),
		'PB' => __( 'Punjab', 'walkthecounty' ),
		'SD' => __( 'Sindh', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_pakistan_states', $states );
}

/**
 * Get Philippines States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_philippines_states_list() {
	$states = array(
		''    => '',
		'ABR' => __( 'Abra', 'walkthecounty' ),
		'AGN' => __( 'Agusan del Norte', 'walkthecounty' ),
		'AGS' => __( 'Agusan del Sur', 'walkthecounty' ),
		'AKL' => __( 'Aklan', 'walkthecounty' ),
		'ALB' => __( 'Albay', 'walkthecounty' ),
		'ANT' => __( 'Antique', 'walkthecounty' ),
		'APA' => __( 'Apayao', 'walkthecounty' ),
		'AUR' => __( 'Aurora', 'walkthecounty' ),
		'BAS' => __( 'Basilan', 'walkthecounty' ),
		'BAN' => __( 'Bataan', 'walkthecounty' ),
		'BTN' => __( 'Batanes', 'walkthecounty' ),
		'BTG' => __( 'Batangas', 'walkthecounty' ),
		'BEN' => __( 'Benguet', 'walkthecounty' ),
		'BIL' => __( 'Biliran', 'walkthecounty' ),
		'BOH' => __( 'Bohol', 'walkthecounty' ),
		'BUK' => __( 'Bukidnon', 'walkthecounty' ),
		'BUL' => __( 'Bulacan', 'walkthecounty' ),
		'CAG' => __( 'Cagayan', 'walkthecounty' ),
		'CAN' => __( 'Camarines Norte', 'walkthecounty' ),
		'CAS' => __( 'Camarines Sur', 'walkthecounty' ),
		'CAM' => __( 'Camiguin', 'walkthecounty' ),
		'CAP' => __( 'Capiz', 'walkthecounty' ),
		'CAT' => __( 'Catanduanes', 'walkthecounty' ),
		'CAV' => __( 'Cavite', 'walkthecounty' ),
		'CEB' => __( 'Cebu', 'walkthecounty' ),
		'COM' => __( 'Compostela Valley', 'walkthecounty' ),
		'NCO' => __( 'Cotabato', 'walkthecounty' ),
		'DAV' => __( 'Davao del Norte', 'walkthecounty' ),
		'DAS' => __( 'Davao del Sur', 'walkthecounty' ),
		'DAC' => __( 'Davao Occidental', 'walkthecounty' ), // TODO: Needs to be updated when ISO code is assigned
		'DAO' => __( 'Davao Oriental', 'walkthecounty' ),
		'DIN' => __( 'Dinagat Islands', 'walkthecounty' ),
		'EAS' => __( 'Eastern Samar', 'walkthecounty' ),
		'GUI' => __( 'Guimaras', 'walkthecounty' ),
		'IFU' => __( 'Ifugao', 'walkthecounty' ),
		'ILN' => __( 'Ilocos Norte', 'walkthecounty' ),
		'ILS' => __( 'Ilocos Sur', 'walkthecounty' ),
		'ILI' => __( 'Iloilo', 'walkthecounty' ),
		'ISA' => __( 'Isabela', 'walkthecounty' ),
		'KAL' => __( 'Kalinga', 'walkthecounty' ),
		'LUN' => __( 'La Union', 'walkthecounty' ),
		'LAG' => __( 'Laguna', 'walkthecounty' ),
		'LAN' => __( 'Lanao del Norte', 'walkthecounty' ),
		'LAS' => __( 'Lanao del Sur', 'walkthecounty' ),
		'LEY' => __( 'Leyte', 'walkthecounty' ),
		'MAG' => __( 'Maguindanao', 'walkthecounty' ),
		'MAD' => __( 'Marinduque', 'walkthecounty' ),
		'MAS' => __( 'Masbate', 'walkthecounty' ),
		'MSC' => __( 'Misamis Occidental', 'walkthecounty' ),
		'MSR' => __( 'Misamis Oriental', 'walkthecounty' ),
		'MOU' => __( 'Mountain Province', 'walkthecounty' ),
		'NEC' => __( 'Negros Occidental', 'walkthecounty' ),
		'NER' => __( 'Negros Oriental', 'walkthecounty' ),
		'NSA' => __( 'Northern Samar', 'walkthecounty' ),
		'NUE' => __( 'Nueva Ecija', 'walkthecounty' ),
		'NUV' => __( 'Nueva Vizcaya', 'walkthecounty' ),
		'MDC' => __( 'Occidental Mindoro', 'walkthecounty' ),
		'MDR' => __( 'Oriental Mindoro', 'walkthecounty' ),
		'PLW' => __( 'Palawan', 'walkthecounty' ),
		'PAM' => __( 'Pampanga', 'walkthecounty' ),
		'PAN' => __( 'Pangasinan', 'walkthecounty' ),
		'QUE' => __( 'Quezon', 'walkthecounty' ),
		'QUI' => __( 'Quirino', 'walkthecounty' ),
		'RIZ' => __( 'Rizal', 'walkthecounty' ),
		'ROM' => __( 'Romblon', 'walkthecounty' ),
		'WSA' => __( 'Samar', 'walkthecounty' ),
		'SAR' => __( 'Sarangani', 'walkthecounty' ),
		'SIQ' => __( 'Siquijor', 'walkthecounty' ),
		'SOR' => __( 'Sorsogon', 'walkthecounty' ),
		'SCO' => __( 'South Cotabato', 'walkthecounty' ),
		'SLE' => __( 'Southern Leyte', 'walkthecounty' ),
		'SUK' => __( 'Sultan Kudarat', 'walkthecounty' ),
		'SLU' => __( 'Sulu', 'walkthecounty' ),
		'SUN' => __( 'Surigao del Norte', 'walkthecounty' ),
		'SUR' => __( 'Surigao del Sur', 'walkthecounty' ),
		'TAR' => __( 'Tarlac', 'walkthecounty' ),
		'TAW' => __( 'Tawi-Tawi', 'walkthecounty' ),
		'ZMB' => __( 'Zambales', 'walkthecounty' ),
		'ZAN' => __( 'Zamboanga del Norte', 'walkthecounty' ),
		'ZAS' => __( 'Zamboanga del Sur', 'walkthecounty' ),
		'ZSI' => __( 'Zamboanga Sibugay', 'walkthecounty' ),
		'00'  => __( 'Metro Manila', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_philippines_states', $states );
}

/**
 * Get Peru States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_peru_states_list() {
	$states = array(
		''    => '',
		'CAL' => __( 'El Callao', 'walkthecounty' ),
		'LMA' => __( 'Municipalidad Metropolitana de Lima', 'walkthecounty' ),
		'AMA' => __( 'Amazonas', 'walkthecounty' ),
		'ANC' => __( 'Ancash', 'walkthecounty' ),
		'APU' => __( 'Apur&iacute;mac', 'walkthecounty' ),
		'ARE' => __( 'Arequipa', 'walkthecounty' ),
		'AYA' => __( 'Ayacucho', 'walkthecounty' ),
		'CAJ' => __( 'Cajamarca', 'walkthecounty' ),
		'CUS' => __( 'Cusco', 'walkthecounty' ),
		'HUV' => __( 'Huancavelica', 'walkthecounty' ),
		'HUC' => __( 'Hu&aacute;nuco', 'walkthecounty' ),
		'ICA' => __( 'Ica', 'walkthecounty' ),
		'JUN' => __( 'Jun&iacute;n', 'walkthecounty' ),
		'LAL' => __( 'La Libertad', 'walkthecounty' ),
		'LAM' => __( 'Lambayeque', 'walkthecounty' ),
		'LIM' => __( 'Lima', 'walkthecounty' ),
		'LOR' => __( 'Loreto', 'walkthecounty' ),
		'MDD' => __( 'Madre de Dios', 'walkthecounty' ),
		'MOQ' => __( 'Moquegua', 'walkthecounty' ),
		'PAS' => __( 'Pasco', 'walkthecounty' ),
		'PIU' => __( 'Piura', 'walkthecounty' ),
		'PUN' => __( 'Puno', 'walkthecounty' ),
		'SAM' => __( 'San Mart&iacute;n', 'walkthecounty' ),
		'TAC' => __( 'Tacna', 'walkthecounty' ),
		'TUM' => __( 'Tumbes', 'walkthecounty' ),
		'UCA' => __( 'Ucayali', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_peru_states', $states );
}

/**
 * Get Nepal States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_nepal_states_list() {
	$states = array(
		''    => '',
		'BAG' => __( 'Bagmati', 'walkthecounty' ),
		'BHE' => __( 'Bheri', 'walkthecounty' ),
		'DHA' => __( 'Dhaulagiri', 'walkthecounty' ),
		'GAN' => __( 'Gandaki', 'walkthecounty' ),
		'JAN' => __( 'Janakpur', 'walkthecounty' ),
		'KAR' => __( 'Karnali', 'walkthecounty' ),
		'KOS' => __( 'Koshi', 'walkthecounty' ),
		'LUM' => __( 'Lumbini', 'walkthecounty' ),
		'MAH' => __( 'Mahakali', 'walkthecounty' ),
		'MEC' => __( 'Mechi', 'walkthecounty' ),
		'NAR' => __( 'Narayani', 'walkthecounty' ),
		'RAP' => __( 'Rapti', 'walkthecounty' ),
		'SAG' => __( 'Sagarmatha', 'walkthecounty' ),
		'SET' => __( 'Seti', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_nepal_states', $states );
}

/**
 * Get Nigerian States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_nigerian_states_list() {
	$states = array(
		''   => '',
		'AB' => __( 'Abia', 'walkthecounty' ),
		'FC' => __( 'Abuja', 'walkthecounty' ),
		'AD' => __( 'Adamawa', 'walkthecounty' ),
		'AK' => __( 'Akwa Ibom', 'walkthecounty' ),
		'AN' => __( 'Anambra', 'walkthecounty' ),
		'BA' => __( 'Bauchi', 'walkthecounty' ),
		'BY' => __( 'Bayelsa', 'walkthecounty' ),
		'BE' => __( 'Benue', 'walkthecounty' ),
		'BO' => __( 'Borno', 'walkthecounty' ),
		'CR' => __( 'Cross River', 'walkthecounty' ),
		'DE' => __( 'Delta', 'walkthecounty' ),
		'EB' => __( 'Ebonyi', 'walkthecounty' ),
		'ED' => __( 'Edo', 'walkthecounty' ),
		'EK' => __( 'Ekiti', 'walkthecounty' ),
		'EN' => __( 'Enugu', 'walkthecounty' ),
		'GO' => __( 'Gombe', 'walkthecounty' ),
		'IM' => __( 'Imo', 'walkthecounty' ),
		'JI' => __( 'Jigawa', 'walkthecounty' ),
		'KD' => __( 'Kaduna', 'walkthecounty' ),
		'KN' => __( 'Kano', 'walkthecounty' ),
		'KT' => __( 'Katsina', 'walkthecounty' ),
		'KE' => __( 'Kebbi', 'walkthecounty' ),
		'KO' => __( 'Kogi', 'walkthecounty' ),
		'KW' => __( 'Kwara', 'walkthecounty' ),
		'LA' => __( 'Lagos', 'walkthecounty' ),
		'NA' => __( 'Nasarawa', 'walkthecounty' ),
		'NI' => __( 'Niger', 'walkthecounty' ),
		'OG' => __( 'Ogun', 'walkthecounty' ),
		'ON' => __( 'Ondo', 'walkthecounty' ),
		'OS' => __( 'Osun', 'walkthecounty' ),
		'OY' => __( 'Oyo', 'walkthecounty' ),
		'PL' => __( 'Plateau', 'walkthecounty' ),
		'RI' => __( 'Rivers', 'walkthecounty' ),
		'SO' => __( 'Sokoto', 'walkthecounty' ),
		'TA' => __( 'Taraba', 'walkthecounty' ),
		'YO' => __( 'Yobe', 'walkthecounty' ),
		'ZA' => __( 'Zamfara', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_nigerian_states', $states );
}

/**
 * Get Mexico States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_mexico_states_list() {
	$states = array(
		''                    => '',
		'Distrito Federal'    => __( 'Distrito Federal', 'walkthecounty' ),
		'Jalisco'             => __( 'Jalisco', 'walkthecounty' ),
		'Nuevo Leon'          => __( 'Nuevo León', 'walkthecounty' ),
		'Aguascalientes'      => __( 'Aguascalientes', 'walkthecounty' ),
		'Baja California'     => __( 'Baja California', 'walkthecounty' ),
		'Baja California Sur' => __( 'Baja California Sur', 'walkthecounty' ),
		'Campeche'            => __( 'Campeche', 'walkthecounty' ),
		'Chiapas'             => __( 'Chiapas', 'walkthecounty' ),
		'Chihuahua'           => __( 'Chihuahua', 'walkthecounty' ),
		'Coahuila'            => __( 'Coahuila', 'walkthecounty' ),
		'Colima'              => __( 'Colima', 'walkthecounty' ),
		'Durango'             => __( 'Durango', 'walkthecounty' ),
		'Guanajuato'          => __( 'Guanajuato', 'walkthecounty' ),
		'Guerrero'            => __( 'Guerrero', 'walkthecounty' ),
		'Hidalgo'             => __( 'Hidalgo', 'walkthecounty' ),
		'Estado de Mexico'    => __( 'Edo. de México', 'walkthecounty' ),
		'Michoacan'           => __( 'Michoacán', 'walkthecounty' ),
		'Morelos'             => __( 'Morelos', 'walkthecounty' ),
		'Nayarit'             => __( 'Nayarit', 'walkthecounty' ),
		'Oaxaca'              => __( 'Oaxaca', 'walkthecounty' ),
		'Puebla'              => __( 'Puebla', 'walkthecounty' ),
		'Queretaro'           => __( 'Querétaro', 'walkthecounty' ),
		'Quintana Roo'        => __( 'Quintana Roo', 'walkthecounty' ),
		'San Luis Potosi'     => __( 'San Luis Potosí', 'walkthecounty' ),
		'Sinaloa'             => __( 'Sinaloa', 'walkthecounty' ),
		'Sonora'              => __( 'Sonora', 'walkthecounty' ),
		'Tabasco'             => __( 'Tabasco', 'walkthecounty' ),
		'Tamaulipas'          => __( 'Tamaulipas', 'walkthecounty' ),
		'Tlaxcala'            => __( 'Tlaxcala', 'walkthecounty' ),
		'Veracruz'            => __( 'Veracruz', 'walkthecounty' ),
		'Yucatan'             => __( 'Yucatán', 'walkthecounty' ),
		'Zacatecas'           => __( 'Zacatecas', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_mexico_states', $states );
}

/**
 * Get Japan States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_japan_states_list() {
	$states = array(
		''     => '',
		'JP01' => __( 'Hokkaido', 'walkthecounty' ),
		'JP02' => __( 'Aomori', 'walkthecounty' ),
		'JP03' => __( 'Iwate', 'walkthecounty' ),
		'JP04' => __( 'Miyagi', 'walkthecounty' ),
		'JP05' => __( 'Akita', 'walkthecounty' ),
		'JP06' => __( 'Yamagata', 'walkthecounty' ),
		'JP07' => __( 'Fukushima', 'walkthecounty' ),
		'JP08' => __( 'Ibaraki', 'walkthecounty' ),
		'JP09' => __( 'Tochigi', 'walkthecounty' ),
		'JP10' => __( 'Gunma', 'walkthecounty' ),
		'JP11' => __( 'Saitama', 'walkthecounty' ),
		'JP12' => __( 'Chiba', 'walkthecounty' ),
		'JP13' => __( 'Tokyo', 'walkthecounty' ),
		'JP14' => __( 'Kanagawa', 'walkthecounty' ),
		'JP15' => __( 'Niigata', 'walkthecounty' ),
		'JP16' => __( 'Toyama', 'walkthecounty' ),
		'JP17' => __( 'Ishikawa', 'walkthecounty' ),
		'JP18' => __( 'Fukui', 'walkthecounty' ),
		'JP19' => __( 'Yamanashi', 'walkthecounty' ),
		'JP20' => __( 'Nagano', 'walkthecounty' ),
		'JP21' => __( 'Gifu', 'walkthecounty' ),
		'JP22' => __( 'Shizuoka', 'walkthecounty' ),
		'JP23' => __( 'Aichi', 'walkthecounty' ),
		'JP24' => __( 'Mie', 'walkthecounty' ),
		'JP25' => __( 'Shiga', 'walkthecounty' ),
		'JP26' => __( 'Kyoto', 'walkthecounty' ),
		'JP27' => __( 'Osaka', 'walkthecounty' ),
		'JP28' => __( 'Hyogo', 'walkthecounty' ),
		'JP29' => __( 'Nara', 'walkthecounty' ),
		'JP30' => __( 'Wakayama', 'walkthecounty' ),
		'JP31' => __( 'Tottori', 'walkthecounty' ),
		'JP32' => __( 'Shimane', 'walkthecounty' ),
		'JP33' => __( 'Okayama', 'walkthecounty' ),
		'JP34' => __( 'Hiroshima', 'walkthecounty' ),
		'JP35' => __( 'Yamaguchi', 'walkthecounty' ),
		'JP36' => __( 'Tokushima', 'walkthecounty' ),
		'JP37' => __( 'Kagawa', 'walkthecounty' ),
		'JP38' => __( 'Ehime', 'walkthecounty' ),
		'JP39' => __( 'Kochi', 'walkthecounty' ),
		'JP40' => __( 'Fukuoka', 'walkthecounty' ),
		'JP41' => __( 'Saga', 'walkthecounty' ),
		'JP42' => __( 'Nagasaki', 'walkthecounty' ),
		'JP43' => __( 'Kumamoto', 'walkthecounty' ),
		'JP44' => __( 'Oita', 'walkthecounty' ),
		'JP45' => __( 'Miyazaki', 'walkthecounty' ),
		'JP46' => __( 'Kagoshima', 'walkthecounty' ),
		'JP47' => __( 'Okinawa', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_japan_states', $states );
}

/**
 * Get Italy States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_italy_states_list() {
	$states = array(
		''   => '',
		'AG' => __( 'Agrigento', 'walkthecounty' ),
		'AL' => __( 'Alessandria', 'walkthecounty' ),
		'AN' => __( 'Ancona', 'walkthecounty' ),
		'AO' => __( 'Aosta', 'walkthecounty' ),
		'AR' => __( 'Arezzo', 'walkthecounty' ),
		'AP' => __( 'Ascoli Piceno', 'walkthecounty' ),
		'AT' => __( 'Asti', 'walkthecounty' ),
		'AV' => __( 'Avellino', 'walkthecounty' ),
		'BA' => __( 'Bari', 'walkthecounty' ),
		'BT' => __( 'Barletta-Andria-Trani', 'walkthecounty' ),
		'BL' => __( 'Belluno', 'walkthecounty' ),
		'BN' => __( 'Benevento', 'walkthecounty' ),
		'BG' => __( 'Bergamo', 'walkthecounty' ),
		'BI' => __( 'Biella', 'walkthecounty' ),
		'BO' => __( 'Bologna', 'walkthecounty' ),
		'BZ' => __( 'Bolzano', 'walkthecounty' ),
		'BS' => __( 'Brescia', 'walkthecounty' ),
		'BR' => __( 'Brindisi', 'walkthecounty' ),
		'CA' => __( 'Cagliari', 'walkthecounty' ),
		'CL' => __( 'Caltanissetta', 'walkthecounty' ),
		'CB' => __( 'Campobasso', 'walkthecounty' ),
		'CI' => __( 'Carbonia-Iglesias', 'walkthecounty' ),
		'CE' => __( 'Caserta', 'walkthecounty' ),
		'CT' => __( 'Catania', 'walkthecounty' ),
		'CZ' => __( 'Catanzaro', 'walkthecounty' ),
		'CH' => __( 'Chieti', 'walkthecounty' ),
		'CO' => __( 'Como', 'walkthecounty' ),
		'CS' => __( 'Cosenza', 'walkthecounty' ),
		'CR' => __( 'Cremona', 'walkthecounty' ),
		'KR' => __( 'Crotone', 'walkthecounty' ),
		'CN' => __( 'Cuneo', 'walkthecounty' ),
		'EN' => __( 'Enna', 'walkthecounty' ),
		'FM' => __( 'Fermo', 'walkthecounty' ),
		'FE' => __( 'Ferrara', 'walkthecounty' ),
		'FI' => __( 'Firenze', 'walkthecounty' ),
		'FG' => __( 'Foggia', 'walkthecounty' ),
		'FC' => __( 'Forlì-Cesena', 'walkthecounty' ),
		'FR' => __( 'Frosinone', 'walkthecounty' ),
		'GE' => __( 'Genova', 'walkthecounty' ),
		'GO' => __( 'Gorizia', 'walkthecounty' ),
		'GR' => __( 'Grosseto', 'walkthecounty' ),
		'IM' => __( 'Imperia', 'walkthecounty' ),
		'IS' => __( 'Isernia', 'walkthecounty' ),
		'SP' => __( 'La Spezia', 'walkthecounty' ),
		'AQ' => __( "L'Aquila", 'walkthecounty' ),
		'LT' => __( 'Latina', 'walkthecounty' ),
		'LE' => __( 'Lecce', 'walkthecounty' ),
		'LC' => __( 'Lecco', 'walkthecounty' ),
		'LI' => __( 'Livorno', 'walkthecounty' ),
		'LO' => __( 'Lodi', 'walkthecounty' ),
		'LU' => __( 'Lucca', 'walkthecounty' ),
		'MC' => __( 'Macerata', 'walkthecounty' ),
		'MN' => __( 'Mantova', 'walkthecounty' ),
		'MS' => __( 'Massa-Carrara', 'walkthecounty' ),
		'MT' => __( 'Matera', 'walkthecounty' ),
		'ME' => __( 'Messina', 'walkthecounty' ),
		'MI' => __( 'Milano', 'walkthecounty' ),
		'MO' => __( 'Modena', 'walkthecounty' ),
		'MB' => __( 'Monza e della Brianza', 'walkthecounty' ),
		'NA' => __( 'Napoli', 'walkthecounty' ),
		'NO' => __( 'Novara', 'walkthecounty' ),
		'NU' => __( 'Nuoro', 'walkthecounty' ),
		'OT' => __( 'Olbia-Tempio', 'walkthecounty' ),
		'OR' => __( 'Oristano', 'walkthecounty' ),
		'PD' => __( 'Padova', 'walkthecounty' ),
		'PA' => __( 'Palermo', 'walkthecounty' ),
		'PR' => __( 'Parma', 'walkthecounty' ),
		'PV' => __( 'Pavia', 'walkthecounty' ),
		'PG' => __( 'Perugia', 'walkthecounty' ),
		'PU' => __( 'Pesaro e Urbino', 'walkthecounty' ),
		'PE' => __( 'Pescara', 'walkthecounty' ),
		'PC' => __( 'Piacenza', 'walkthecounty' ),
		'PI' => __( 'Pisa', 'walkthecounty' ),
		'PT' => __( 'Pistoia', 'walkthecounty' ),
		'PN' => __( 'Pordenone', 'walkthecounty' ),
		'PZ' => __( 'Potenza', 'walkthecounty' ),
		'PO' => __( 'Prato', 'walkthecounty' ),
		'RG' => __( 'Ragusa', 'walkthecounty' ),
		'RA' => __( 'Ravenna', 'walkthecounty' ),
		'RC' => __( 'Reggio Calabria', 'walkthecounty' ),
		'RE' => __( 'Reggio Emilia', 'walkthecounty' ),
		'RI' => __( 'Rieti', 'walkthecounty' ),
		'RN' => __( 'Rimini', 'walkthecounty' ),
		'RM' => __( 'Roma', 'walkthecounty' ),
		'RO' => __( 'Rovigo', 'walkthecounty' ),
		'SA' => __( 'Salerno', 'walkthecounty' ),
		'VS' => __( 'Medio Campidano', 'walkthecounty' ),
		'SS' => __( 'Sassari', 'walkthecounty' ),
		'SV' => __( 'Savona', 'walkthecounty' ),
		'SI' => __( 'Siena', 'walkthecounty' ),
		'SR' => __( 'Siracusa', 'walkthecounty' ),
		'SO' => __( 'Sondrio', 'walkthecounty' ),
		'TA' => __( 'Taranto', 'walkthecounty' ),
		'TE' => __( 'Teramo', 'walkthecounty' ),
		'TR' => __( 'Terni', 'walkthecounty' ),
		'TO' => __( 'Torino', 'walkthecounty' ),
		'OG' => __( 'Ogliastra', 'walkthecounty' ),
		'TP' => __( 'Trapani', 'walkthecounty' ),
		'TN' => __( 'Trento', 'walkthecounty' ),
		'TV' => __( 'Treviso', 'walkthecounty' ),
		'TS' => __( 'Trieste', 'walkthecounty' ),
		'UD' => __( 'Udine', 'walkthecounty' ),
		'VA' => __( 'Varese', 'walkthecounty' ),
		'VE' => __( 'Venezia', 'walkthecounty' ),
		'VB' => __( 'Verbano-Cusio-Ossola', 'walkthecounty' ),
		'VC' => __( 'Vercelli', 'walkthecounty' ),
		'VR' => __( 'Verona', 'walkthecounty' ),
		'VV' => __( 'Vibo Valentia', 'walkthecounty' ),
		'VI' => __( 'Vicenza', 'walkthecounty' ),
		'VT' => __( 'Viterbo', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_italy_states', $states );
}

/**
 * Get Iran States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_iran_states_list() {
	$states = array(
		''    => '',
		'KHZ' => __( 'Khuzestan  (خوزستان)', 'walkthecounty' ),
		'THR' => __( 'Tehran  (تهران)', 'walkthecounty' ),
		'ILM' => __( 'Ilaam (ایلام)', 'walkthecounty' ),
		'BHR' => __( 'Bushehr (بوشهر)', 'walkthecounty' ),
		'ADL' => __( 'Ardabil (اردبیل)', 'walkthecounty' ),
		'ESF' => __( 'Isfahan (اصفهان)', 'walkthecounty' ),
		'YZD' => __( 'Yazd (یزد)', 'walkthecounty' ),
		'KRH' => __( 'Kermanshah (کرمانشاه)', 'walkthecounty' ),
		'KRN' => __( 'Kerman (کرمان)', 'walkthecounty' ),
		'HDN' => __( 'Hamadan (همدان)', 'walkthecounty' ),
		'GZN' => __( 'Ghazvin (قزوین)', 'walkthecounty' ),
		'ZJN' => __( 'Zanjan (زنجان)', 'walkthecounty' ),
		'LRS' => __( 'Luristan (لرستان)', 'walkthecounty' ),
		'ABZ' => __( 'Alborz (البرز)', 'walkthecounty' ),
		'EAZ' => __( 'East Azarbaijan (آذربایجان شرقی)', 'walkthecounty' ),
		'WAZ' => __( 'West Azarbaijan (آذربایجان غربی)', 'walkthecounty' ),
		'CHB' => __( 'Chaharmahal and Bakhtiari (چهارمحال و بختیاری)', 'walkthecounty' ),
		'SKH' => __( 'South Khorasan (خراسان جنوبی)', 'walkthecounty' ),
		'RKH' => __( 'Razavi Khorasan (خراسان رضوی)', 'walkthecounty' ),
		'NKH' => __( 'North Khorasan (خراسان جنوبی)', 'walkthecounty' ),
		'SMN' => __( 'Semnan (سمنان)', 'walkthecounty' ),
		'FRS' => __( 'Fars (فارس)', 'walkthecounty' ),
		'QHM' => __( 'Qom (قم)', 'walkthecounty' ),
		'KRD' => __( 'Kurdistan / کردستان)', 'walkthecounty' ),
		'KBD' => __( 'Kohgiluyeh and BoyerAhmad (کهگیلوییه و بویراحمد)', 'walkthecounty' ),
		'GLS' => __( 'Golestan (گلستان)', 'walkthecounty' ),
		'GIL' => __( 'Gilan (گیلان)', 'walkthecounty' ),
		'MZN' => __( 'Mazandaran (مازندران)', 'walkthecounty' ),
		'MKZ' => __( 'Markazi (مرکزی)', 'walkthecounty' ),
		'HRZ' => __( 'Hormozgan (هرمزگان)', 'walkthecounty' ),
		'SBN' => __( 'Sistan and Baluchestan (سیستان و بلوچستان)', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_iran_states', $states );
}

/**
 * Get Ireland States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_ireland_states_list() {
	$states = array(
		''   => '',
		'AN' => __( 'Antrim', 'walkthecounty' ),
		'AR' => __( 'Armagh', 'walkthecounty' ),
		'CE' => __( 'Clare', 'walkthecounty' ),
		'CK' => __( 'Cork', 'walkthecounty' ),
		'CN' => __( 'Cavan', 'walkthecounty' ),
		'CW' => __( 'Carlow', 'walkthecounty' ),
		'DL' => __( 'Donegal', 'walkthecounty' ),
		'DN' => __( 'Dublin', 'walkthecounty' ),
		'DO' => __( 'Down', 'walkthecounty' ),
		'DY' => __( 'Derry', 'walkthecounty' ),
		'FM' => __( 'Fermanagh', 'walkthecounty' ),
		'GY' => __( 'Galway', 'walkthecounty' ),
		'KE' => __( 'Kildare', 'walkthecounty' ),
		'KK' => __( 'Kilkenny', 'walkthecounty' ),
		'KY' => __( 'Kerry', 'walkthecounty' ),
		'LD' => __( 'Longford', 'walkthecounty' ),
		'LH' => __( 'Louth', 'walkthecounty' ),
		'LK' => __( 'Limerick', 'walkthecounty' ),
		'LM' => __( 'Leitrim', 'walkthecounty' ),
		'LS' => __( 'Laois', 'walkthecounty' ),
		'MH' => __( 'Meath', 'walkthecounty' ),
		'MN' => __( 'Monaghan', 'walkthecounty' ),
		'MO' => __( 'Mayo', 'walkthecounty' ),
		'OY' => __( 'Offaly', 'walkthecounty' ),
		'RN' => __( 'Roscommon', 'walkthecounty' ),
		'SO' => __( 'Sligo', 'walkthecounty' ),
		'TR' => __( 'Tyrone', 'walkthecounty' ),
		'TY' => __( 'Tipperary', 'walkthecounty' ),
		'WD' => __( 'Waterford', 'walkthecounty' ),
		'WH' => __( 'Westmeath', 'walkthecounty' ),
		'WW' => __( 'Wicklow', 'walkthecounty' ),
		'WX' => __( 'Wexford', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_ireland_states', $states );
}

/**
 * Get Greek States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_greek_states_list() {
	$states = array(
		''  => '',
		'I' => __( 'Αττική', 'walkthecounty' ),
		'A' => __( 'Ανατολική Μακεδονία και Θράκη', 'walkthecounty' ),
		'B' => __( 'Κεντρική Μακεδονία', 'walkthecounty' ),
		'C' => __( 'Δυτική Μακεδονία', 'walkthecounty' ),
		'D' => __( 'Ήπειρος', 'walkthecounty' ),
		'E' => __( 'Θεσσαλία', 'walkthecounty' ),
		'F' => __( 'Ιόνιοι Νήσοι', 'walkthecounty' ),
		'G' => __( 'Δυτική Ελλάδα', 'walkthecounty' ),
		'H' => __( 'Στερεά Ελλάδα', 'walkthecounty' ),
		'J' => __( 'Πελοπόννησος', 'walkthecounty' ),
		'K' => __( 'Βόρειο Αιγαίο', 'walkthecounty' ),
		'L' => __( 'Νότιο Αιγαίο', 'walkthecounty' ),
		'M' => __( 'Κρήτη', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_greek_states', $states );
}

/**
 * Get bolivian States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_bolivian_states_list() {
	$states = array(
		''  => '',
		'B' => __( 'Chuquisaca', 'walkthecounty' ),
		'H' => __( 'Beni', 'walkthecounty' ),
		'C' => __( 'Cochabamba', 'walkthecounty' ),
		'L' => __( 'La Paz', 'walkthecounty' ),
		'O' => __( 'Oruro', 'walkthecounty' ),
		'N' => __( 'Pando', 'walkthecounty' ),
		'P' => __( 'Potosí', 'walkthecounty' ),
		'S' => __( 'Santa Cruz', 'walkthecounty' ),
		'T' => __( 'Tarija', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_bolivian_states', $states );
}

/**
 * Get Bulgarian States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_bulgarian_states_list() {
	$states = array(
		''      => '',
		'BG-01' => __( 'Blagoevgrad', 'walkthecounty' ),
		'BG-02' => __( 'Burgas', 'walkthecounty' ),
		'BG-08' => __( 'Dobrich', 'walkthecounty' ),
		'BG-07' => __( 'Gabrovo', 'walkthecounty' ),
		'BG-26' => __( 'Haskovo', 'walkthecounty' ),
		'BG-09' => __( 'Kardzhali', 'walkthecounty' ),
		'BG-10' => __( 'Kyustendil', 'walkthecounty' ),
		'BG-11' => __( 'Lovech', 'walkthecounty' ),
		'BG-12' => __( 'Montana', 'walkthecounty' ),
		'BG-13' => __( 'Pazardzhik', 'walkthecounty' ),
		'BG-14' => __( 'Pernik', 'walkthecounty' ),
		'BG-15' => __( 'Pleven', 'walkthecounty' ),
		'BG-16' => __( 'Plovdiv', 'walkthecounty' ),
		'BG-17' => __( 'Razgrad', 'walkthecounty' ),
		'BG-18' => __( 'Ruse', 'walkthecounty' ),
		'BG-27' => __( 'Shumen', 'walkthecounty' ),
		'BG-19' => __( 'Silistra', 'walkthecounty' ),
		'BG-20' => __( 'Sliven', 'walkthecounty' ),
		'BG-21' => __( 'Smolyan', 'walkthecounty' ),
		'BG-23' => __( 'Sofia', 'walkthecounty' ),
		'BG-22' => __( 'Sofia-Grad', 'walkthecounty' ),
		'BG-24' => __( 'Stara Zagora', 'walkthecounty' ),
		'BG-25' => __( 'Targovishte', 'walkthecounty' ),
		'BG-03' => __( 'Varna', 'walkthecounty' ),
		'BG-04' => __( 'Veliko Tarnovo', 'walkthecounty' ),
		'BG-05' => __( 'Vidin', 'walkthecounty' ),
		'BG-06' => __( 'Vratsa', 'walkthecounty' ),
		'BG-28' => __( 'Yambol', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_bulgarian_states', $states );
}

/**
 * Get Bangladeshi States
 *
 * @since 1.8.12.
 * @return array $states A list of states
 */
function walkthecounty_get_bangladeshi_states_list() {
	$states = array(
		''     => '',
		'BAG'  => __( 'Bagerhat', 'walkthecounty' ),
		'BAN'  => __( 'Bandarban', 'walkthecounty' ),
		'BAR'  => __( 'Barguna', 'walkthecounty' ),
		'BARI' => __( 'Barisal', 'walkthecounty' ),
		'BHO'  => __( 'Bhola', 'walkthecounty' ),
		'BOG'  => __( 'Bogra', 'walkthecounty' ),
		'BRA'  => __( 'Brahmanbaria', 'walkthecounty' ),
		'CHA'  => __( 'Chandpur', 'walkthecounty' ),
		'CHI'  => __( 'Chittagong', 'walkthecounty' ),
		'CHU'  => __( 'Chuadanga', 'walkthecounty' ),
		'COM'  => __( 'Comilla', 'walkthecounty' ),
		'COX'  => __( "Cox's Bazar", 'walkthecounty' ),
		'DHA'  => __( 'Dhaka', 'walkthecounty' ),
		'DIN'  => __( 'Dinajpur', 'walkthecounty' ),
		'FAR'  => __( 'Faridpur ', 'walkthecounty' ),
		'FEN'  => __( 'Feni', 'walkthecounty' ),
		'GAI'  => __( 'Gaibandha', 'walkthecounty' ),
		'GAZI' => __( 'Gazipur', 'walkthecounty' ),
		'GOP'  => __( 'Gopalganj', 'walkthecounty' ),
		'HAB'  => __( 'Habiganj', 'walkthecounty' ),
		'JAM'  => __( 'Jamalpur', 'walkthecounty' ),
		'JES'  => __( 'Jessore', 'walkthecounty' ),
		'JHA'  => __( 'Jhalokati', 'walkthecounty' ),
		'JHE'  => __( 'Jhenaidah', 'walkthecounty' ),
		'JOY'  => __( 'Joypurhat', 'walkthecounty' ),
		'KHA'  => __( 'Khagrachhari', 'walkthecounty' ),
		'KHU'  => __( 'Khulna', 'walkthecounty' ),
		'KIS'  => __( 'Kishoreganj', 'walkthecounty' ),
		'KUR'  => __( 'Kurigram', 'walkthecounty' ),
		'KUS'  => __( 'Kushtia', 'walkthecounty' ),
		'LAK'  => __( 'Lakshmipur', 'walkthecounty' ),
		'LAL'  => __( 'Lalmonirhat', 'walkthecounty' ),
		'MAD'  => __( 'Madaripur', 'walkthecounty' ),
		'MAG'  => __( 'Magura', 'walkthecounty' ),
		'MAN'  => __( 'Manikganj ', 'walkthecounty' ),
		'MEH'  => __( 'Meherpur', 'walkthecounty' ),
		'MOU'  => __( 'Moulvibazar', 'walkthecounty' ),
		'MUN'  => __( 'Munshiganj', 'walkthecounty' ),
		'MYM'  => __( 'Mymensingh', 'walkthecounty' ),
		'NAO'  => __( 'Naogaon', 'walkthecounty' ),
		'NAR'  => __( 'Narail', 'walkthecounty' ),
		'NARG' => __( 'Narayanganj', 'walkthecounty' ),
		'NARD' => __( 'Narsingdi', 'walkthecounty' ),
		'NAT'  => __( 'Natore', 'walkthecounty' ),
		'NAW'  => __( 'Nawabganj', 'walkthecounty' ),
		'NET'  => __( 'Netrakona', 'walkthecounty' ),
		'NIL'  => __( 'Nilphamari', 'walkthecounty' ),
		'NOA'  => __( 'Noakhali', 'walkthecounty' ),
		'PAB'  => __( 'Pabna', 'walkthecounty' ),
		'PAN'  => __( 'Panchagarh', 'walkthecounty' ),
		'PAT'  => __( 'Patuakhali', 'walkthecounty' ),
		'PIR'  => __( 'Pirojpur', 'walkthecounty' ),
		'RAJB' => __( 'Rajbari', 'walkthecounty' ),
		'RAJ'  => __( 'Rajshahi', 'walkthecounty' ),
		'RAN'  => __( 'Rangamati', 'walkthecounty' ),
		'RANP' => __( 'Rangpur', 'walkthecounty' ),
		'SAT'  => __( 'Satkhira', 'walkthecounty' ),
		'SHA'  => __( 'Shariatpur', 'walkthecounty' ),
		'SHE'  => __( 'Sherpur', 'walkthecounty' ),
		'SIR'  => __( 'Sirajganj', 'walkthecounty' ),
		'SUN'  => __( 'Sunamganj', 'walkthecounty' ),
		'SYL'  => __( 'Sylhet', 'walkthecounty' ),
		'TAN'  => __( 'Tangail', 'walkthecounty' ),
		'THA'  => __( 'Thakurgaon', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_bangladeshi_states', $states );
}

/**
 * Get Argentina States
 *
 * @since 1.8.12
 * @return array $states A list of states
 */
function walkthecounty_get_argentina_states_list() {
	$states = array(
		''  => '',
		'C' => __( 'Ciudad Aut&oacute;noma de Buenos Aires', 'walkthecounty' ),
		'B' => __( 'Buenos Aires', 'walkthecounty' ),
		'K' => __( 'Catamarca', 'walkthecounty' ),
		'H' => __( 'Chaco', 'walkthecounty' ),
		'U' => __( 'Chubut', 'walkthecounty' ),
		'X' => __( 'C&oacute;rdoba', 'walkthecounty' ),
		'W' => __( 'Corrientes', 'walkthecounty' ),
		'E' => __( 'Entre R&iacute;os', 'walkthecounty' ),
		'P' => __( 'Formosa', 'walkthecounty' ),
		'Y' => __( 'Jujuy', 'walkthecounty' ),
		'L' => __( 'La Pampa', 'walkthecounty' ),
		'F' => __( 'La Rioja', 'walkthecounty' ),
		'M' => __( 'Mendoza', 'walkthecounty' ),
		'N' => __( 'Misiones', 'walkthecounty' ),
		'Q' => __( 'Neuqu&eacute;n', 'walkthecounty' ),
		'R' => __( 'R&iacute;o Negro', 'walkthecounty' ),
		'A' => __( 'Salta', 'walkthecounty' ),
		'J' => __( 'San Juan', 'walkthecounty' ),
		'D' => __( 'San Luis', 'walkthecounty' ),
		'Z' => __( 'Santa Cruz', 'walkthecounty' ),
		'S' => __( 'Santa Fe', 'walkthecounty' ),
		'G' => __( 'Santiago del Estero', 'walkthecounty' ),
		'V' => __( 'Tierra del Fuego', 'walkthecounty' ),
		'T' => __( 'Tucum&aacute;n', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_argentina_states', $states );
}

/**
 * Get States List
 *
 * @access      public
 * @since       1.2
 * @return      array
 */
function walkthecounty_get_states_list() {
	$states = array(
		''   => '',
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming',
		'AS' => 'American Samoa',
		'CZ' => 'Canal Zone',
		'CM' => 'Commonwealth of the Northern Mariana Islands',
		'FM' => 'Federated States of Micronesia',
		'GU' => 'Guam',
		'MH' => 'Marshall Islands',
		'MP' => 'Northern Mariana Islands',
		'PW' => 'Palau',
		'PI' => 'Philippine Islands',
		'PR' => 'Puerto Rico',
		'TT' => 'Trust Territory of the Pacific Islands',
		'VI' => 'Virgin Islands',
		'AA' => 'Armed Forces - Americas',
		'AE' => 'Armed Forces - Europe, Canada, Middle East, Africa',
		'AP' => 'Armed Forces - Pacific',
	);

	return apply_filters( 'walkthecounty_us_states', $states );
}

/**
 * Get Provinces List
 *
 * @access      public
 * @since       1.0
 * @return      array
 */
function walkthecounty_get_provinces_list() {
	$provinces = array(
		''   => '',
		'AB' => esc_html__( 'Alberta', 'walkthecounty' ),
		'BC' => esc_html__( 'British Columbia', 'walkthecounty' ),
		'MB' => esc_html__( 'Manitoba', 'walkthecounty' ),
		'NB' => esc_html__( 'New Brunswick', 'walkthecounty' ),
		'NL' => esc_html__( 'Newfoundland and Labrador', 'walkthecounty' ),
		'NS' => esc_html__( 'Nova Scotia', 'walkthecounty' ),
		'NT' => esc_html__( 'Northwest Territories', 'walkthecounty' ),
		'NU' => esc_html__( 'Nunavut', 'walkthecounty' ),
		'ON' => esc_html__( 'Ontario', 'walkthecounty' ),
		'PE' => esc_html__( 'Prince Edward Island', 'walkthecounty' ),
		'QC' => esc_html__( 'Quebec', 'walkthecounty' ),
		'SK' => esc_html__( 'Saskatchewan', 'walkthecounty' ),
		'YT' => esc_html__( 'Yukon', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_canada_provinces', $provinces );
}

/**
 * Get Australian States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_australian_states_list() {
	$states = array(
		''    => '',
		'ACT' => 'Australian Capital Territory',
		'NSW' => 'New South Wales',
		'NT'  => 'Northern Territory',
		'QLD' => 'Queensland',
		'SA'  => 'South Australia',
		'TAS' => 'Tasmania',
		'VIC' => 'Victoria',
		'WA'  => 'Western Australia',
	);

	return apply_filters( 'walkthecounty_australian_states', $states );
}

/**
 * Get Brazil States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_brazil_states_list() {
	$states = array(
		''   => '',
		'AC' => 'Acre',
		'AL' => 'Alagoas',
		'AP' => 'Amap&aacute;',
		'AM' => 'Amazonas',
		'BA' => 'Bahia',
		'CE' => 'Cear&aacute;',
		'DF' => 'Distrito Federal',
		'ES' => 'Esp&iacute;rito Santo',
		'GO' => 'Goi&aacute;s',
		'MA' => 'Maranh&atilde;o',
		'MT' => 'Mato Grosso',
		'MS' => 'Mato Grosso do Sul',
		'MG' => 'Minas Gerais',
		'PA' => 'Par&aacute;',
		'PB' => 'Para&iacute;ba',
		'PR' => 'Paran&aacute;',
		'PE' => 'Pernambuco',
		'PI' => 'Piau&iacute;',
		'RJ' => 'Rio de Janeiro',
		'RN' => 'Rio Grande do Norte',
		'RS' => 'Rio Grande do Sul',
		'RO' => 'Rond&ocirc;nia',
		'RR' => 'Roraima',
		'SC' => 'Santa Catarina',
		'SP' => 'S&atilde;o Paulo',
		'SE' => 'Sergipe',
		'TO' => 'Tocantins',
	);

	return apply_filters( 'walkthecounty_brazil_states', $states );
}

/**
 * Get Hong Kong States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_hong_kong_states_list() {
	$states = array(
		''                => '',
		'HONG KONG'       => 'Hong Kong Island',
		'KOWLOON'         => 'Kowloon',
		'NEW TERRITORIES' => 'New Territories',
	);

	return apply_filters( 'walkthecounty_hong_kong_states', $states );
}

/**
 * Get Hungary States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_hungary_states_list() {
	$states = array(
		''   => '',
		'BK' => 'Bács-Kiskun',
		'BE' => 'Békés',
		'BA' => 'Baranya',
		'BZ' => 'Borsod-Abaúj-Zemplén',
		'BU' => 'Budapest',
		'CS' => 'Csongrád',
		'FE' => 'Fejér',
		'GS' => 'Győr-Moson-Sopron',
		'HB' => 'Hajdú-Bihar',
		'HE' => 'Heves',
		'JN' => 'Jász-Nagykun-Szolnok',
		'KE' => 'Komárom-Esztergom',
		'NO' => 'Nógrád',
		'PE' => 'Pest',
		'SO' => 'Somogy',
		'SZ' => 'Szabolcs-Szatmár-Bereg',
		'TO' => 'Tolna',
		'VA' => 'Vas',
		'VE' => 'Veszprém',
		'ZA' => 'Zala',
	);

	return apply_filters( 'walkthecounty_hungary_states', $states );
}

/**
 * Get Chinese States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_chinese_states_list() {
	$states = array(
		''     => '',
		'CN1'  => 'Yunnan / &#20113;&#21335;',
		'CN2'  => 'Beijing / &#21271;&#20140;',
		'CN3'  => 'Tianjin / &#22825;&#27941;',
		'CN4'  => 'Hebei / &#27827;&#21271;',
		'CN5'  => 'Shanxi / &#23665;&#35199;',
		'CN6'  => 'Inner Mongolia / &#20839;&#33945;&#21476;',
		'CN7'  => 'Liaoning / &#36797;&#23425;',
		'CN8'  => 'Jilin / &#21513;&#26519;',
		'CN9'  => 'Heilongjiang / &#40657;&#40857;&#27743;',
		'CN10' => 'Shanghai / &#19978;&#28023;',
		'CN11' => 'Jiangsu / &#27743;&#33487;',
		'CN12' => 'Zhejiang / &#27993;&#27743;',
		'CN13' => 'Anhui / &#23433;&#24509;',
		'CN14' => 'Fujian / &#31119;&#24314;',
		'CN15' => 'Jiangxi / &#27743;&#35199;',
		'CN16' => 'Shandong / &#23665;&#19996;',
		'CN17' => 'Henan / &#27827;&#21335;',
		'CN18' => 'Hubei / &#28246;&#21271;',
		'CN19' => 'Hunan / &#28246;&#21335;',
		'CN20' => 'Guangdong / &#24191;&#19996;',
		'CN21' => 'Guangxi Zhuang / &#24191;&#35199;&#22766;&#26063;',
		'CN22' => 'Hainan / &#28023;&#21335;',
		'CN23' => 'Chongqing / &#37325;&#24198;',
		'CN24' => 'Sichuan / &#22235;&#24029;',
		'CN25' => 'Guizhou / &#36149;&#24030;',
		'CN26' => 'Shaanxi / &#38485;&#35199;',
		'CN27' => 'Gansu / &#29976;&#32899;',
		'CN28' => 'Qinghai / &#38738;&#28023;',
		'CN29' => 'Ningxia Hui / &#23425;&#22799;',
		'CN30' => 'Macau / &#28595;&#38376;',
		'CN31' => 'Tibet / &#35199;&#34255;',
		'CN32' => 'Xinjiang / &#26032;&#30086;',
	);

	return apply_filters( 'walkthecounty_chinese_states', $states );
}

/**
 * Get New Zealand States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_new_zealand_states_list() {
	$states = array(
		''   => '',
		'AK' => 'Auckland',
		'BP' => 'Bay of Plenty',
		'CT' => 'Canterbury',
		'HB' => 'Hawke&rsquo;s Bay',
		'MW' => 'Manawatu-Wanganui',
		'MB' => 'Marlborough',
		'NS' => 'Nelson',
		'NL' => 'Northland',
		'OT' => 'Otago',
		'SL' => 'Southland',
		'TK' => 'Taranaki',
		'TM' => 'Tasman',
		'WA' => 'Waikato',
		'WE' => 'Wellington',
		'WC' => 'West Coast',
	);

	return apply_filters( 'walkthecounty_new_zealand_states', $states );
}

/**
 * Get Indonesian States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_indonesian_states_list() {
	$states = array(
		''   => '',
		'AC' => 'Daerah Istimewa Aceh',
		'SU' => 'Sumatera Utara',
		'SB' => 'Sumatera Barat',
		'RI' => 'Riau',
		'KR' => 'Kepulauan Riau',
		'JA' => 'Jambi',
		'SS' => 'Sumatera Selatan',
		'BB' => 'Bangka Belitung',
		'BE' => 'Bengkulu',
		'LA' => 'Lampung',
		'JK' => 'DKI Jakarta',
		'JB' => 'Jawa Barat',
		'BT' => 'Banten',
		'JT' => 'Jawa Tengah',
		'JI' => 'Jawa Timur',
		'YO' => 'Daerah Istimewa Yogyakarta',
		'BA' => 'Bali',
		'NB' => 'Nusa Tenggara Barat',
		'NT' => 'Nusa Tenggara Timur',
		'KB' => 'Kalimantan Barat',
		'KT' => 'Kalimantan Tengah',
		'KI' => 'Kalimantan Timur',
		'KS' => 'Kalimantan Selatan',
		'KU' => 'Kalimantan Utara',
		'SA' => 'Sulawesi Utara',
		'ST' => 'Sulawesi Tengah',
		'SG' => 'Sulawesi Tenggara',
		'SR' => 'Sulawesi Barat',
		'SN' => 'Sulawesi Selatan',
		'GO' => 'Gorontalo',
		'MA' => 'Maluku',
		'MU' => 'Maluku Utara',
		'PA' => 'Papua',
		'PB' => 'Papua Barat',
	);

	return apply_filters( 'walkthecounty_indonesia_states', $states );
}

/**
 * Get Indian States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_indian_states_list() {
	$states = array(
		''   => '',
		'AP' => 'Andhra Pradesh',
		'AR' => 'Arunachal Pradesh',
		'AS' => 'Assam',
		'BR' => 'Bihar',
		'CT' => 'Chhattisgarh',
		'GA' => 'Goa',
		'GJ' => 'Gujarat',
		'HR' => 'Haryana',
		'HP' => 'Himachal Pradesh',
		'JK' => 'Jammu and Kashmir',
		'JH' => 'Jharkhand',
		'KA' => 'Karnataka',
		'KL' => 'Kerala',
		'MP' => 'Madhya Pradesh',
		'MH' => 'Maharashtra',
		'MN' => 'Manipur',
		'ML' => 'Meghalaya',
		'MZ' => 'Mizoram',
		'NL' => 'Nagaland',
		'OR' => 'Orissa',
		'PB' => 'Punjab',
		'RJ' => 'Rajasthan',
		'SK' => 'Sikkim',
		'TN' => 'Tamil Nadu',
		'TG' => 'Telangana',
		'TR' => 'Tripura',
		'UT' => 'Uttarakhand',
		'UP' => 'Uttar Pradesh',
		'WB' => 'West Bengal',
		'AN' => 'Andaman and Nicobar Islands',
		'CH' => 'Chandigarh',
		'DN' => 'Dadar and Nagar Haveli',
		'DD' => 'Daman and Diu',
		'DL' => 'Delhi',
		'LD' => 'Lakshadweep',
		'PY' => 'Pondicherry (Puducherry)',
	);

	return apply_filters( 'walkthecounty_indian_states', $states );
}

/**
 * Get Malaysian States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function walkthecounty_get_malaysian_states_list() {
	$states = array(
		''    => '',
		'JHR' => 'Johor',
		'KDH' => 'Kedah',
		'KTN' => 'Kelantan',
		'MLK' => 'Melaka',
		'NSN' => 'Negeri Sembilan',
		'PHG' => 'Pahang',
		'PRK' => 'Perak',
		'PLS' => 'Perlis',
		'PNG' => 'Pulau Pinang',
		'SBH' => 'Sabah',
		'SWK' => 'Sarawak',
		'SGR' => 'Selangor',
		'TRG' => 'Terengganu',
		'KUL' => 'W.P. Kuala Lumpur',
		'LBN' => 'W.P. Labuan',
		'PJY' => 'W.P. Putrajaya',
	);

	return apply_filters( 'walkthecounty_malaysian_states', $states );
}

/**
 * Get South African States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function walkthecounty_get_south_african_states_list() {
	$states = array(
		''    => '',
		'EC'  => 'Eastern Cape',
		'FS'  => 'Free State',
		'GP'  => 'Gauteng',
		'KZN' => 'KwaZulu-Natal',
		'LP'  => 'Limpopo',
		'MP'  => 'Mpumalanga',
		'NC'  => 'Northern Cape',
		'NW'  => 'North West',
		'WC'  => 'Western Cape',
	);

	return apply_filters( 'walkthecounty_south_african_states', $states );
}

/**
 * Get Thailand States
 *
 * @since 1.6
 * @return array $states A list of states
 */
function walkthecounty_get_thailand_states_list() {
	$states = array(
		''      => '',
		'TH-37' => 'Amnat Charoen (&#3629;&#3635;&#3609;&#3634;&#3592;&#3648;&#3592;&#3619;&#3636;&#3597;)',
		'TH-15' => 'Ang Thong (&#3629;&#3656;&#3634;&#3591;&#3607;&#3629;&#3591;)',
		'TH-14' => 'Ayutthaya (&#3614;&#3619;&#3632;&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3629;&#3618;&#3640;&#3608;&#3618;&#3634;)',
		'TH-10' => 'Bangkok (&#3585;&#3619;&#3640;&#3591;&#3648;&#3607;&#3614;&#3617;&#3627;&#3634;&#3609;&#3588;&#3619;)',
		'TH-38' => 'Bueng Kan (&#3610;&#3638;&#3591;&#3585;&#3634;&#3628;)',
		'TH-31' => 'Buri Ram (&#3610;&#3640;&#3619;&#3637;&#3619;&#3633;&#3617;&#3618;&#3660;)',
		'TH-24' => 'Chachoengsao (&#3593;&#3632;&#3648;&#3594;&#3636;&#3591;&#3648;&#3607;&#3619;&#3634;)',
		'TH-18' => 'Chai Nat (&#3594;&#3633;&#3618;&#3609;&#3634;&#3607;)',
		'TH-36' => 'Chaiyaphum (&#3594;&#3633;&#3618;&#3616;&#3641;&#3617;&#3636;)',
		'TH-22' => 'Chanthaburi (&#3592;&#3633;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
		'TH-50' => 'Chiang Mai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3651;&#3627;&#3617;&#3656;)',
		'TH-57' => 'Chiang Rai (&#3648;&#3594;&#3637;&#3618;&#3591;&#3619;&#3634;&#3618;)',
		'TH-20' => 'Chonburi (&#3594;&#3621;&#3610;&#3640;&#3619;&#3637;)',
		'TH-86' => 'Chumphon (&#3594;&#3640;&#3617;&#3614;&#3619;)',
		'TH-46' => 'Kalasin (&#3585;&#3634;&#3628;&#3626;&#3636;&#3609;&#3608;&#3640;&#3660;)',
		'TH-62' => 'Kamphaeng Phet (&#3585;&#3635;&#3649;&#3614;&#3591;&#3648;&#3614;&#3594;&#3619;)',
		'TH-71' => 'Kanchanaburi (&#3585;&#3634;&#3597;&#3592;&#3609;&#3610;&#3640;&#3619;&#3637;)',
		'TH-40' => 'Khon Kaen (&#3586;&#3629;&#3609;&#3649;&#3585;&#3656;&#3609;)',
		'TH-81' => 'Krabi (&#3585;&#3619;&#3632;&#3610;&#3637;&#3656;)',
		'TH-52' => 'Lampang (&#3621;&#3635;&#3611;&#3634;&#3591;)',
		'TH-51' => 'Lamphun (&#3621;&#3635;&#3614;&#3641;&#3609;)',
		'TH-42' => 'Loei (&#3648;&#3621;&#3618;)',
		'TH-16' => 'Lopburi (&#3621;&#3614;&#3610;&#3640;&#3619;&#3637;)',
		'TH-58' => 'Mae Hong Son (&#3649;&#3617;&#3656;&#3630;&#3656;&#3629;&#3591;&#3626;&#3629;&#3609;)',
		'TH-44' => 'Maha Sarakham (&#3617;&#3627;&#3634;&#3626;&#3634;&#3619;&#3588;&#3634;&#3617;)',
		'TH-49' => 'Mukdahan (&#3617;&#3640;&#3585;&#3604;&#3634;&#3627;&#3634;&#3619;)',
		'TH-26' => 'Nakhon Nayok (&#3609;&#3588;&#3619;&#3609;&#3634;&#3618;&#3585;)',
		'TH-73' => 'Nakhon Pathom (&#3609;&#3588;&#3619;&#3611;&#3600;&#3617;)',
		'TH-48' => 'Nakhon Phanom (&#3609;&#3588;&#3619;&#3614;&#3609;&#3617;)',
		'TH-30' => 'Nakhon Ratchasima (&#3609;&#3588;&#3619;&#3619;&#3634;&#3594;&#3626;&#3637;&#3617;&#3634;)',
		'TH-60' => 'Nakhon Sawan (&#3609;&#3588;&#3619;&#3626;&#3623;&#3619;&#3619;&#3588;&#3660;)',
		'TH-80' => 'Nakhon Si Thammarat (&#3609;&#3588;&#3619;&#3624;&#3619;&#3637;&#3608;&#3619;&#3619;&#3617;&#3619;&#3634;&#3594;)',
		'TH-55' => 'Nan (&#3609;&#3656;&#3634;&#3609;)',
		'TH-96' => 'Narathiwat (&#3609;&#3619;&#3634;&#3608;&#3636;&#3623;&#3634;&#3626;)',
		'TH-39' => 'Nong Bua Lam Phu (&#3627;&#3609;&#3629;&#3591;&#3610;&#3633;&#3623;&#3621;&#3635;&#3616;&#3641;)',
		'TH-43' => 'Nong Khai (&#3627;&#3609;&#3629;&#3591;&#3588;&#3634;&#3618;)',
		'TH-12' => 'Nonthaburi (&#3609;&#3609;&#3607;&#3610;&#3640;&#3619;&#3637;)',
		'TH-13' => 'Pathum Thani (&#3611;&#3607;&#3640;&#3617;&#3608;&#3634;&#3609;&#3637;)',
		'TH-94' => 'Pattani (&#3611;&#3633;&#3605;&#3605;&#3634;&#3609;&#3637;)',
		'TH-82' => 'Phang Nga (&#3614;&#3633;&#3591;&#3591;&#3634;)',
		'TH-93' => 'Phatthalung (&#3614;&#3633;&#3607;&#3621;&#3640;&#3591;)',
		'TH-56' => 'Phayao (&#3614;&#3632;&#3648;&#3618;&#3634;)',
		'TH-67' => 'Phetchabun (&#3648;&#3614;&#3594;&#3619;&#3610;&#3641;&#3619;&#3603;&#3660;)',
		'TH-76' => 'Phetchaburi (&#3648;&#3614;&#3594;&#3619;&#3610;&#3640;&#3619;&#3637;)',
		'TH-66' => 'Phichit (&#3614;&#3636;&#3592;&#3636;&#3605;&#3619;)',
		'TH-65' => 'Phitsanulok (&#3614;&#3636;&#3625;&#3603;&#3640;&#3650;&#3621;&#3585;)',
		'TH-54' => 'Phrae (&#3649;&#3614;&#3619;&#3656;)',
		'TH-83' => 'Phuket (&#3616;&#3641;&#3648;&#3585;&#3655;&#3605;)',
		'TH-25' => 'Prachin Buri (&#3611;&#3619;&#3634;&#3592;&#3637;&#3609;&#3610;&#3640;&#3619;&#3637;)',
		'TH-77' => 'Prachuap Khiri Khan (&#3611;&#3619;&#3632;&#3592;&#3623;&#3610;&#3588;&#3637;&#3619;&#3637;&#3586;&#3633;&#3609;&#3608;&#3660;)',
		'TH-85' => 'Ranong (&#3619;&#3632;&#3609;&#3629;&#3591;)',
		'TH-70' => 'Ratchaburi (&#3619;&#3634;&#3594;&#3610;&#3640;&#3619;&#3637;)',
		'TH-21' => 'Rayong (&#3619;&#3632;&#3618;&#3629;&#3591;)',
		'TH-45' => 'Roi Et (&#3619;&#3657;&#3629;&#3618;&#3648;&#3629;&#3655;&#3604;)',
		'TH-27' => 'Sa Kaeo (&#3626;&#3619;&#3632;&#3649;&#3585;&#3657;&#3623;)',
		'TH-47' => 'Sakon Nakhon (&#3626;&#3585;&#3621;&#3609;&#3588;&#3619;)',
		'TH-11' => 'Samut Prakan (&#3626;&#3617;&#3640;&#3607;&#3619;&#3611;&#3619;&#3634;&#3585;&#3634;&#3619;)',
		'TH-74' => 'Samut Sakhon (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3634;&#3588;&#3619;)',
		'TH-75' => 'Samut Songkhram (&#3626;&#3617;&#3640;&#3607;&#3619;&#3626;&#3591;&#3588;&#3619;&#3634;&#3617;)',
		'TH-19' => 'Saraburi (&#3626;&#3619;&#3632;&#3610;&#3640;&#3619;&#3637;)',
		'TH-91' => 'Satun (&#3626;&#3605;&#3641;&#3621;)',
		'TH-17' => 'Sing Buri (&#3626;&#3636;&#3591;&#3627;&#3660;&#3610;&#3640;&#3619;&#3637;)',
		'TH-33' => 'Sisaket (&#3624;&#3619;&#3637;&#3626;&#3632;&#3648;&#3585;&#3625;)',
		'TH-90' => 'Songkhla (&#3626;&#3591;&#3586;&#3621;&#3634;)',
		'TH-64' => 'Sukhothai (&#3626;&#3640;&#3650;&#3586;&#3607;&#3633;&#3618;)',
		'TH-72' => 'Suphan Buri (&#3626;&#3640;&#3614;&#3619;&#3619;&#3603;&#3610;&#3640;&#3619;&#3637;)',
		'TH-84' => 'Surat Thani (&#3626;&#3640;&#3619;&#3634;&#3625;&#3598;&#3619;&#3660;&#3608;&#3634;&#3609;&#3637;)',
		'TH-32' => 'Surin (&#3626;&#3640;&#3619;&#3636;&#3609;&#3607;&#3619;&#3660;)',
		'TH-63' => 'Tak (&#3605;&#3634;&#3585;)',
		'TH-92' => 'Trang (&#3605;&#3619;&#3633;&#3591;)',
		'TH-23' => 'Trat (&#3605;&#3619;&#3634;&#3604;)',
		'TH-34' => 'Ubon Ratchathani (&#3629;&#3640;&#3610;&#3621;&#3619;&#3634;&#3594;&#3608;&#3634;&#3609;&#3637;)',
		'TH-41' => 'Udon Thani (&#3629;&#3640;&#3604;&#3619;&#3608;&#3634;&#3609;&#3637;)',
		'TH-61' => 'Uthai Thani (&#3629;&#3640;&#3607;&#3633;&#3618;&#3608;&#3634;&#3609;&#3637;)',
		'TH-53' => 'Uttaradit (&#3629;&#3640;&#3605;&#3619;&#3604;&#3636;&#3605;&#3606;&#3660;)',
		'TH-95' => 'Yala (&#3618;&#3632;&#3621;&#3634;)',
		'TH-35' => 'Yasothon (&#3618;&#3650;&#3626;&#3608;&#3619;)',
	);

	return apply_filters( 'walkthecounty_thailand_states', $states );
}

/**
 * Get Spain States
 *
 * @since 1.0
 * @return array $states A list of states
 */
function walkthecounty_get_spain_states_list() {
	$states = array(
		''   => '',
		'C'  => esc_html__( 'A Coru&ntilde;a', 'walkthecounty' ),
		'VI' => esc_html__( 'Álava', 'walkthecounty' ),
		'AB' => esc_html__( 'Albacete', 'walkthecounty' ),
		'A'  => esc_html__( 'Alicante', 'walkthecounty' ),
		'AL' => esc_html__( 'Almer&iacute;a', 'walkthecounty' ),
		'O'  => esc_html__( 'Asturias', 'walkthecounty' ),
		'AV' => esc_html__( '&Aacute;vila', 'walkthecounty' ),
		'BA' => esc_html__( 'Badajoz', 'walkthecounty' ),
		'PM' => esc_html__( 'Baleares', 'walkthecounty' ),
		'B'  => esc_html__( 'Barcelona', 'walkthecounty' ),
		'BU' => esc_html__( 'Burgos', 'walkthecounty' ),
		'CC' => esc_html__( 'C&aacute;ceres', 'walkthecounty' ),
		'CA' => esc_html__( 'C&aacute;diz', 'walkthecounty' ),
		'S'  => esc_html__( 'Cantabria', 'walkthecounty' ),
		'CS' => esc_html__( 'Castell&oacute;n', 'walkthecounty' ),
		'CE' => esc_html__( 'Ceuta', 'walkthecounty' ),
		'CR' => esc_html__( 'Ciudad Real', 'walkthecounty' ),
		'CO' => esc_html__( 'C&oacute;rdoba', 'walkthecounty' ),
		'CU' => esc_html__( 'Cuenca', 'walkthecounty' ),
		'GI' => esc_html__( 'Girona', 'walkthecounty' ),
		'GR' => esc_html__( 'Granada', 'walkthecounty' ),
		'GU' => esc_html__( 'Guadalajara', 'walkthecounty' ),
		'SS' => esc_html__( 'Gipuzkoa', 'walkthecounty' ),
		'H'  => esc_html__( 'Huelva', 'walkthecounty' ),
		'HU' => esc_html__( 'Huesca', 'walkthecounty' ),
		'J'  => esc_html__( 'Ja&eacute;n', 'walkthecounty' ),
		'LO' => esc_html__( 'La Rioja', 'walkthecounty' ),
		'GC' => esc_html__( 'Las Palmas', 'walkthecounty' ),
		'LE' => esc_html__( 'Le&oacute;n', 'walkthecounty' ),
		'L'  => esc_html__( 'Lleida', 'walkthecounty' ),
		'LU' => esc_html__( 'Lugo', 'walkthecounty' ),
		'M'  => esc_html__( 'Madrid', 'walkthecounty' ),
		'MA' => esc_html__( 'M&aacute;laga', 'walkthecounty' ),
		'ML' => esc_html__( 'Melilla', 'walkthecounty' ),
		'MU' => esc_html__( 'Murcia', 'walkthecounty' ),
		'NA' => esc_html__( 'Navarra', 'walkthecounty' ),
		'OR' => esc_html__( 'Ourense', 'walkthecounty' ),
		'P'  => esc_html__( 'Palencia', 'walkthecounty' ),
		'PO' => esc_html__( 'Pontevedra', 'walkthecounty' ),
		'SA' => esc_html__( 'Salamanca', 'walkthecounty' ),
		'TF' => esc_html__( 'Santa Cruz de Tenerife', 'walkthecounty' ),
		'SG' => esc_html__( 'Segovia', 'walkthecounty' ),
		'SE' => esc_html__( 'Sevilla', 'walkthecounty' ),
		'SO' => esc_html__( 'Soria', 'walkthecounty' ),
		'T'  => esc_html__( 'Tarragona', 'walkthecounty' ),
		'TE' => esc_html__( 'Teruel', 'walkthecounty' ),
		'TO' => esc_html__( 'Toledo', 'walkthecounty' ),
		'V'  => esc_html__( 'Valencia', 'walkthecounty' ),
		'VA' => esc_html__( 'Valladolid', 'walkthecounty' ),
		'BI' => esc_html__( 'Bizkaia', 'walkthecounty' ),
		'ZA' => esc_html__( 'Zamora', 'walkthecounty' ),
		'Z'  => esc_html__( 'Zaragoza', 'walkthecounty' ),
	);

	return apply_filters( 'walkthecounty_spain_states', $states );
}
