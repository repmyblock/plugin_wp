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
import WalkTheCountyDonorWallGrid from './edit/block';

/**
 * Register Block
 */

export default registerBlockType( 'walkthecounty/donor-wall', {
	title: __( 'Donor Wall' ),
	description: __( 'The WalkTheCountyWP Donor Wall block inserts an existing donation form into the page. Each form\'s presentation can be customized below.' ),
	category: 'walkthecounty',
	icon: <WalkTheCountyLogo color="grey" />,
	keywords: [
		__( 'donation' ),
		__( 'wall' ),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: WalkTheCountyDonorWallGrid,

	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
