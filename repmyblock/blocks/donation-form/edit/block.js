/**
 * WordPress dependencies
 */
const { ServerSideRender } = wp.components;

/**
 * Internal dependencies
 */
import SelectForm from '../../components/select-form';
import Inspector from './inspector';
import Controls from './controls';

/**
 * Render Block UI For Editor
 */

const WalkTheCountyForm = ( props ) => {
	const { attributes, isSelected, className } = props;
	const { id } = attributes;

	// Render block UI
	let blockUI;

	if ( ! id ) {
		blockUI = <SelectForm { ... { ...props } } />;
	} else {
		blockUI = (
			<div className={ !! isSelected ? `${ className } isSelected` : className } >
				<Inspector { ... { ...props } } />
				<Controls { ... { ...props } } />
				<ServerSideRender block="walkthecounty/donation-form" attributes={ attributes } />
			</div>
		);
	}

	return blockUI;
};

export default WalkTheCountyForm;
