/**
 * External dependencies
 */
import { isUndefined } from 'lodash';

/**
 * WordPress dependencies
 */
const { __ } = wp.i18n;
const { withSelect } = wp.data;
const { SelectControl, Button, Placeholder, Spinner } = wp.components;

/**
 * Internal dependencies
 */
import { getSiteUrl } from '../../utils';
import WalkTheCountyBlankSlate from '../blank-slate';
import NoForms from '../no-form';
import ChosenSelect from '../chosen-select';

/**
 * Render form select UI
 */
const walkthecountyFormOptionsDefault = { value: '0', label: __( '-- Select Form --' ) };

const SelectForm = ( { forms, attributes, setAttributes } ) => {
	//Attributes
	const { prevId } = attributes;

	// Event(s)
	const getFormOptions = () => {
		// Add API Data To Select Options

		let formOptions = [];

		if ( ! isUndefined( forms ) ) {
			formOptions = forms.map(
				( { id, title: { rendered: title } } ) => {
					return {
						value: id,
						label: title === '' ? `${ id } : ${ __( 'No form title' ) }` : title,
					};
				}
			);
		}

		// Add Default option
		formOptions.unshift( walkthecountyFormOptionsDefault );

		return formOptions;
	};

	const setFormIdTo = id => {
		setAttributes( { id: Number( id ) } );
	};

	const resetFormIdTo = () => {
		setAttributes( { id: Number( prevId ) } );
		setAttributes( { prevId: undefined } );
	};

	// Render Component UI
	let componentUI;

	if ( ! forms ) {
		componentUI = <Placeholder><Spinner /></Placeholder>;
	} else if ( forms && forms.length === 0 ) {
		componentUI = <NoForms />;
	} else {
		componentUI = (
			<WalkTheCountyBlankSlate title={ __( 'Donation Form' ) }>
				<ChosenSelect
					className="walkthecounty-blank-slate__select"
					options={ getFormOptions() }
					onChange={ setFormIdTo }
				/>

				<Button isPrimary
					isLarge href={ `${ getSiteUrl() }/wp-admin/post-new.php?post_type=walkthecounty_forms` }>
					{ __( 'Add New Form' ) }
				</Button>&nbsp;&nbsp;

				{
					prevId &&
					<Button isLarge
						onClick={ resetFormIdTo }>
						{ __( 'Cancel' ) }
					</Button>
				}
			</WalkTheCountyBlankSlate>
		);
	}

	return componentUI;
};

/**
 * Export with forms data
 */
export default withSelect( ( select ) => {
	return {
		forms: select( 'core' ).getEntityRecords( 'postType', 'walkthecounty_forms', { per_page: 30 } ),
	};
} )( SelectForm );
