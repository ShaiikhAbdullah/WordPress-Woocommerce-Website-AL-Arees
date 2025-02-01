import { useEffect, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import classNames from 'classnames';

import { OnboardingHooks } from '../../data';
import SpinnerOverlay from '../ReusableComponents/SpinnerOverlay';

import Onboarding from './Onboarding/Onboarding';
import SettingsScreen from './SettingsScreen';

const Settings = () => {
	const onboardingProgress = OnboardingHooks.useSteps();

	// Disable the "Changes you made might not be saved" browser warning.
	useEffect( () => {
		const suppressBeforeUnload = ( event ) => {
			event.stopImmediatePropagation();
			return undefined;
		};

		window.addEventListener( 'beforeunload', suppressBeforeUnload );

		return () => {
			window.removeEventListener( 'beforeunload', suppressBeforeUnload );
		};
	}, [] );

	const wrapperClass = classNames( 'ppcp-r-app', {
		loading: ! onboardingProgress.isReady,
	} );

	const Content = useMemo( () => {
		if ( ! onboardingProgress.isReady ) {
			return (
				<SpinnerOverlay
					message={ __( 'Loading…', 'woocommerce-paypal-payments' ) }
				/>
			);
		}

		if ( ! onboardingProgress.completed ) {
			return <Onboarding />;
		}

		return <SettingsScreen />;
	}, [ onboardingProgress ] );

	return <div className={ wrapperClass }>{ Content }</div>;
};

export default Settings;
