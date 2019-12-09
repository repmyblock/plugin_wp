/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;

/**
 * Internal dependencies
 */
import './style.scss';
import WalkTheCountyLogo from '../components/logo';
import blockAttributes from './data/attributes';
import WalkTheCountyForm from './edit/block';

/**
 * Register Block
*/

export default registerBlockType( 'walkthecounty/donation-form', {

	title: __( 'Donation Form' ),
	description: __( 'The WalkTheCountyWP Donation Form block inserts an existing donation form into the page. Each donation form\'s presentation can be customized below.' ),
	category: 'walkthecounty',
	icon: <WalkTheCountyLogo color="grey" />,
	keywords: [
		__( 'donation' ),
	],
	supports: {
		html: false,
	},
	attributes: blockAttributes,
	edit: WalkTheCountyForm,

	save: () => {
		// Server side rendering via shortcode
		return null;
	},
} );
