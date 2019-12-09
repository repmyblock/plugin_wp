/**
 * Wordpress dependencies
 */
const { Fragment } = wp.element;
 const { ServerSideRender } = wp.components;

/**
 * Internal dependencies
 */
import Inspector from './inspector';

/**
 * Render Block UI For Editor
 */

const WalkTheCountyDonorWall = ( props ) => {
	const { attributes } = props;

	return (
		<Fragment>
			<Inspector { ... { ...props } } />
			<ServerSideRender block="walkthecounty/donor-wall" attributes={ attributes } />
		</Fragment>
	);
};

export default WalkTheCountyDonorWall;

// @todo show no donor template if donor does not exist.

