import { Content, ContentWrapper } from './SettingsBlocks';

const SettingsCard = ( {
	className: extraClassName,
	title,
	description,
	children,
	contentItems,
	contentContainer = true,
} ) => {
	const className = [ 'ppcp-r-settings-card', extraClassName ]
		.filter( Boolean )
		.join( ' ' );

	const renderContent = () => {
		// If contentItems array is provided, wrap each item in Content component
		if ( contentItems ) {
			return (
				<ContentWrapper>
					{ contentItems.map( ( item, index ) => (
						<Content key={ index }>{ item }</Content>
					) ) }
				</ContentWrapper>
			);
		}

		// Otherwise handle regular children with contentContainer prop
		if ( contentContainer ) {
			return <Content>{ children }</Content>;
		}

		return children;
	};

	return (
		<div className={ className }>
			<div className="ppcp-r-settings-card__header">
				<div className="ppcp-r-settings-card__content-inner">
					<span className="ppcp-r-settings-card__title">
						{ title }
					</span>
					<p className="ppcp-r-settings-card__description">
						{ description }
					</p>
				</div>
			</div>
			{ renderContent() }
		</div>
	);
};

export default SettingsCard;
