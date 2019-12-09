/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;

/**
 * WordPress dependencies
 */
const { Fragment } = wp.element;
const { ServerSideRender } = wp.components;
const { withSelect } = wp.data;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const WalkTheCountyDonationFormGrid = ( props ) => {
	const {attributes} = props;

	return (
		<Fragment>
			<Inspector { ... { ...props } } />
			<ServerSideRender block="walkthecounty/donation-form-grid" attributes={ attributes } />
		</Fragment>
	);
};

export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'walkthecounty_forms' ),
	};
} )( WalkTheCountyDonationFormGrid );
