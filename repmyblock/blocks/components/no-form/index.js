/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { Button } = wp.components;

/**
 * Internal dependencies
 */
import { getSiteUrl } from '../../utils';
import WalkTheCountyBlankSlate from '../blank-slate';

/**
 * Render No forms Found UI
*/

const NoForms = () => {
	return (
		<WalkTheCountyBlankSlate title={ __( 'No donation forms found.' ) }
			description={ __( 'The first step towards accepting online donations is to create a form.' ) }
			helpLink>
			<Button
				isPrimary
				isLarge
				className="walkthecounty-blank-slate__cta"
				href={ `${ getSiteUrl() }/wp-admin/post-new.php?post_type=walkthecounty_forms` }>
				{ __( 'Create Donation Form' ) }
			</Button>
		</WalkTheCountyBlankSlate>
	);
};

export default NoForms;
