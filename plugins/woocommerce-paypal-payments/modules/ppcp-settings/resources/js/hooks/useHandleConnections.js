import { __ } from '@wordpress/i18n';
import { useDispatch } from '@wordpress/data';
import { store as noticesStore } from '@wordpress/notices';

import { CommonHooks, OnboardingHooks } from '../data';
import { openPopup } from '../utils/window';

const MESSAGES = {
	CONNECTED: __( 'Connected to PayPal', 'woocommerce-paypal-payments' ),
	POPUP_BLOCKED: __(
		'Popup blocked. Please allow popups for this site to connect to PayPal.',
		'woocommerce-paypal-payments'
	),
	SANDBOX_ERROR: __(
		'Could not generate a Sandbox login link.',
		'woocommerce-paypal-payments'
	),
	PRODUCTION_ERROR: __(
		'Could not generate a login link.',
		'woocommerce-paypal-payments'
	),
	MANUAL_ERROR: __(
		'Could not connect to PayPal. Please make sure your Client ID and Secret Key are correct.',
		'woocommerce-paypal-payments'
	),
	LOGIN_FAILED: __(
		'Login was not successful. Please try again.',
		'woocommerce-paypal-payments'
	),
};

const ACTIVITIES = {
	CONNECT_SANDBOX: 'ISU_LOGIN_SANDBOX',
	CONNECT_PRODUCTION: 'ISU_LOGIN_PRODUCTION',
	CONNECT_MANUAL: 'MANUAL_LOGIN',
};

const handlePopupWithCompletion = ( url, onError ) => {
	return new Promise( ( resolve ) => {
		const popup = openPopup( url );

		if ( ! popup ) {
			onError( MESSAGES.POPUP_BLOCKED );
			resolve( false );
			return;
		}

		// Check popup state every 500ms
		const checkPopup = setInterval( () => {
			if ( popup.closed ) {
				clearInterval( checkPopup );
				resolve( true );
			}
		}, 500 );

		return () => {
			clearInterval( checkPopup );

			if ( popup && ! popup.closed ) {
				popup.close();
			}
		};
	} );
};

const useConnectionBase = () => {
	const { setCompleted } = OnboardingHooks.useSteps();
	const { createSuccessNotice, createErrorNotice } =
		useDispatch( noticesStore );
	const { verifyLoginStatus } = CommonHooks.useMerchantInfo();

	return {
		handleFailed: ( res, genericMessage ) => {
			console.error( 'Connection error', res );
			createErrorNotice( res?.message ?? genericMessage );
		},
		handleCompleted: async () => {
			try {
				const loginSuccessful = await verifyLoginStatus();

				if ( loginSuccessful ) {
					createSuccessNotice( MESSAGES.CONNECTED );
					await setCompleted( true );
				} else {
					createErrorNotice( MESSAGES.LOGIN_FAILED );
				}
			} catch ( error ) {
				createErrorNotice( error.message ?? MESSAGES.LOGIN_FAILED );
			}
		},
		createErrorNotice,
	};
};

const useConnectionAttempt = ( connectFn, errorMessage ) => {
	const { handleFailed, createErrorNotice, handleCompleted } =
		useConnectionBase();

	return async ( ...args ) => {
		const res = await connectFn( ...args );

		if ( ! res.success || ! res.data ) {
			handleFailed( res, errorMessage );
			return false;
		}

		const popupClosed = await handlePopupWithCompletion(
			res.data,
			createErrorNotice
		);

		if ( popupClosed ) {
			await handleCompleted();
		}

		return popupClosed;
	};
};

export const useSandboxConnection = () => {
	const { connectToSandbox, isSandboxMode, setSandboxMode } =
		CommonHooks.useSandbox();
	const { withActivity } = CommonHooks.useBusyState();
	const connectionAttempt = useConnectionAttempt(
		connectToSandbox,
		MESSAGES.SANDBOX_ERROR
	);

	const handleSandboxConnect = async () => {
		return withActivity(
			ACTIVITIES.CONNECT_SANDBOX,
			'Connecting to sandbox account',
			connectionAttempt
		);
	};

	return {
		handleSandboxConnect,
		isSandboxMode,
		setSandboxMode,
	};
};

export const useProductionConnection = () => {
	const { connectToProduction } = CommonHooks.useProduction();
	const { withActivity } = CommonHooks.useBusyState();
	const products = OnboardingHooks.useDetermineProducts();
	const connectionAttempt = useConnectionAttempt(
		() => connectToProduction( products ),
		MESSAGES.PRODUCTION_ERROR
	);

	const handleProductionConnect = async () => {
		return withActivity(
			ACTIVITIES.CONNECT_PRODUCTION,
			'Connecting to production account',
			connectionAttempt
		);
	};

	return { handleProductionConnect };
};

export const useManualConnection = () => {
	const { handleFailed, handleCompleted, createErrorNotice } =
		useConnectionBase();
	const { withActivity } = CommonHooks.useBusyState();
	const {
		connectViaIdAndSecret,
		isManualConnectionMode,
		setManualConnectionMode,
		clientId,
		setClientId,
		clientSecret,
		setClientSecret,
	} = CommonHooks.useManualConnection();

	const handleConnectViaIdAndSecret = async ( { validation } = {} ) => {
		return withActivity(
			ACTIVITIES.CONNECT_MANUAL,
			'Connecting manually via Client ID and Secret',
			async () => {
				if ( 'function' === typeof validation ) {
					try {
						validation();
					} catch ( exception ) {
						createErrorNotice( exception.message );
						return;
					}
				}

				const res = await connectViaIdAndSecret();

				if ( res.success ) {
					await handleCompleted();
				} else {
					handleFailed( res, MESSAGES.MANUAL_ERROR );
				}

				return res.success;
			}
		);
	};

	return {
		handleConnectViaIdAndSecret,
		isManualConnectionMode,
		setManualConnectionMode,
		clientId,
		setClientId,
		clientSecret,
		setClientSecret,
	};
};
