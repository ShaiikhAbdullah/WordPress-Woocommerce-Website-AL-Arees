import { Icon } from '@wordpress/components';
import { chevronDown, chevronUp } from '@wordpress/icons';
import classNames from 'classnames';
import { useAccordionState } from '../../hooks/useAccordionState';

// Provide defaults for all layout components so the generic version just works.
const DefaultHeader = ( { children, className = '' } ) => (
	<div className={ `ppcp-r-accordion__header ${ className }`.trim() }>
		{ children }
	</div>
);
const DefaultTitleWrapper = ( { children } ) => (
	<div className="ppcp-r-accordion__title-wrapper">{ children }</div>
);
const DefaultTitle = ( { children } ) => (
	<span className="ppcp-r-accordion__title">{ children }</span>
);
const DefaultAction = ( { children } ) => (
	<span className="ppcp-r-accordion__action">{ children }</span>
);
const DefaultDescription = ( { children } ) => (
	<div className="ppcp-r-accordion__description">{ children }</div>
);

const AccordionContent = ( { isOpen, children } ) => {
	if ( ! isOpen || ! children ) {
		return null;
	}
	return <div className="ppcp-r-accordion__content">{ children }</div>;
};

const Accordion = ( {
	title,
	id = '',
	initiallyOpen = null,
	description = '',
	children = null,
	className = '',

	// Layout components can be overridden by the caller
	Header = DefaultHeader,
	TitleWrapper = DefaultTitleWrapper,
	Title = DefaultTitle,
	Action = DefaultAction,
	Description = DefaultDescription,
} ) => {
	const { isOpen, toggleOpen } = useAccordionState( { id, initiallyOpen } );
	const wrapperClasses = classNames( 'ppcp-r-accordion', className, {
		'ppcp--is-open': isOpen,
	} );

	const icon = isOpen ? chevronUp : chevronDown;

	return (
		<div className={ wrapperClasses } { ...( id && { id } ) }>
			<button
				type="button"
				className="ppcp-r-accordion__toggler"
				onClick={ toggleOpen }
			>
				<Header>
					<TitleWrapper>
						<Title>{ title }</Title>
						<Action>
							<Icon icon={ icon } />
						</Action>
					</TitleWrapper>
					{ description && (
						<Description>{ description }</Description>
					) }
				</Header>
			</button>
			<AccordionContent isOpen={ isOpen }>{ children }</AccordionContent>
		</div>
	);
};

export default Accordion;
