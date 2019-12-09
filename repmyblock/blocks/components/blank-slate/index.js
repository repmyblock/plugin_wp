/**
* WordPress dependencies
*/
const { __ } = wp.i18n;

/**
* Internal dependencies
*/
import { getSiteUrl } from '../../utils';
import WalkTheCountyHelpLink from '../help-link';
import PlaceholderAnimation from '../placeholder-animation';
import WalkTheCountyLogo from '../logo';

const WalkTheCountyBlankSlate = ( props ) => {
	const {
		noIcon,
		isLoader,
		title,
		description,
		children,
		helpLink,
	} = props;

	const blockLoading = (
		<PlaceholderAnimation />
	);

	const blockLoaded = (
		<div className="block-loaded">
			{ !! title && ( <h2 className="walkthecounty-blank-slate__heading">{ title }</h2> ) }
			{ !! description && ( <p className="walkthecounty-blank-slate__message">{ description }</p> ) }
			{ children }
			{ !! helpLink && ( <WalkTheCountyHelpLink /> ) }
		</div>
	);

	return (
		<div className="walkthecounty-blank-slate">
			{ ! noIcon && <WalkTheCountyLogo size="80" className="walkthecounty-blank-slate__image" /> }
			{ !! isLoader ? blockLoading : blockLoaded }
		</div>
	);
};

export default WalkTheCountyBlankSlate;
