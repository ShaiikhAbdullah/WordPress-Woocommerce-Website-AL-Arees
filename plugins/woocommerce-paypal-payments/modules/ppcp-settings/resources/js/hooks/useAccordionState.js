import { useEffect, useState } from '@wordpress/element';

const checkIfCurrentTab = ( id ) => {
	return id && window.location.hash === `#${ id }`;
};

const determineInitialState = ( id, initiallyOpen ) => {
	if ( initiallyOpen !== null ) {
		return initiallyOpen;
	}
	return checkIfCurrentTab( id );
};

export function useAccordionState( { id = '', initiallyOpen = null } ) {
	const [ isOpen, setIsOpen ] = useState(
		determineInitialState( id, initiallyOpen )
	);

	useEffect( () => {
		const handleHashChange = () => {
			if ( checkIfCurrentTab( id ) ) {
				setIsOpen( true );
			}
		};

		window.addEventListener( 'hashchange', handleHashChange );
		return () => {
			window.removeEventListener( 'hashchange', handleHashChange );
		};
	}, [ id ] );

	const toggleOpen = ( ev ) => {
		setIsOpen( ! isOpen );
		ev?.preventDefault();
		return false;
	};

	return { isOpen, toggleOpen };
}
