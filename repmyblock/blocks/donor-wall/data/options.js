/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * Options data for various form selects
 */
const walkthecountyDonorWallOptions = {};

// Form Display Styles
walkthecountyDonorWallOptions.columns = [
	{ value: 'best-fit', label: __( 'Best Fit' ) },
	{ value: '1', label: '1' },
	{ value: '2', label: '2' },
	{ value: '3', label: '3' },
	{ value: '4', label: '4' },
];

// Order
walkthecountyDonorWallOptions.order = [
	{ value: 'DESC', label: __( 'Descending' ) },
	{ value: 'ASC', label: __( 'Ascending' ) },
];

// Order
walkthecountyDonorWallOptions.orderBy = [
	{ value: 'donation_amount', label: __( 'Donation Amount' ) },
	{ value: 'post_date', label: __( 'Date Created' ) },
];

export default walkthecountyDonorWallOptions;
