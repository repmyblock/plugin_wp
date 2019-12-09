/**
 * WordPress dependencies
*/
const { __ } = wp.i18n;

/**
 * Options data for various form selects
 */
const walkthecountyFormOptions = {};

// Form Display Styles
walkthecountyFormOptions.displayStyles = [
	{ value: 'onpage', label: __( 'Full Form' ) },
	{ value: 'modal', label: __( 'Modal' ) },
	{ value: 'reveal', label: __( 'Reveal' ) },
	{ value: 'button', label: __( 'One Button Launch' ) },
];

// Form content Position
walkthecountyFormOptions.contentPosition = [
	{ value: 'above', label: __( 'Above' ) },
	{ value: 'below', label: __( 'Below' ) },
];

export default walkthecountyFormOptions;
