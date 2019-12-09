/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import blockAttributes from './data/attributes';
import WalkTheCountyLogo from '../components/logo';
import WalkTheCountyDonationFormGrid from './edit/block';

/**
 * Register Block
 */

export default registerBlockType( 'walkthecounty/donation-form-grid', {

	title: __( 'Donation Form Grid' ),
	description: __( 'The WalkTheCountyWP Donation Form Grid block insert an existing donation form into the page. Each form\'s presentation can be customized below.' ),
	category: 'walkthecounty',
	icon: <WalkTheCountyLogo color="grey" />,
	keywords: [
		__( 'donation' ),
		__( 'grid' ),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: WalkTheCountyDonationFormGrid,

	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
