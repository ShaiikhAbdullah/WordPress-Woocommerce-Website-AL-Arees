/**
 * Hooks: Provide the main API for components to interact with the store.
 *
 * These encapsulate store interactions, offering a consistent interface.
 * Hooks simplify data access and manipulation for components.
 *
 * @file
 */

import { useDispatch, useSelect } from '@wordpress/data';
import { useCallback } from '@wordpress/element';
import { STORE_NAME } from './constants';

const useTransient = ( key ) =>
	useSelect(
		( select ) => select( STORE_NAME ).transientData()?.[ key ],
		[ key ]
	);

const usePersistent = ( key ) =>
	useSelect(
		( select ) => select( STORE_NAME ).persistentData()?.[ key ],
		[ key ]
	);

const useHooks = () => {
	const {
		persist,
		setSandboxMode,
		setManualConnectionMode,
		setClientId,
		setClientSecret,
		connectToSandbox,
		connectToProduction,
		connectViaIdAndSecret,
		startWebhookSimulation,
		checkWebhookSimulationState,
	} = useDispatch( STORE_NAME );

	// Transient accessors.
	const isReady = useTransient( 'isReady' );

	// Persistent accessors.
	const clientId = usePersistent( 'clientId' );
	const clientSecret = usePersistent( 'clientSecret' );
	const isSandboxMode = usePersistent( 'useSandbox' );
	const isManualConnectionMode = usePersistent( 'useManualConnection' );
	const webhooks = usePersistent( 'webhooks' );
	const merchant = useSelect(
		( select ) => select( STORE_NAME ).merchant(),
		[]
	);
	const wooSettings = useSelect(
		( select ) => select( STORE_NAME ).wooSettings(),
		[]
	);

	const savePersistent = async ( setter, value ) => {
		setter( value );
		await persist();
	};

	return {
		isReady,
		isSandboxMode,
		setSandboxMode: ( state ) => {
			return savePersistent( setSandboxMode, state );
		},
		isManualConnectionMode,
		setManualConnectionMode: ( state ) => {
			return savePersistent( setManualConnectionMode, state );
		},
		clientId,
		setClientId: ( value ) => {
			return savePersistent( setClientId, value );
		},
		clientSecret,
		setClientSecret: ( value ) => {
			return savePersistent( setClientSecret, value );
		},
		connectToSandbox,
		connectToProduction,
		connectViaIdAndSecret,
		merchant,
		wooSettings,
		webhooks,
		startWebhookSimulation,
		checkWebhookSimulationState,
	};
};

export const useSandbox = () => {
	const { isSandboxMode, setSandboxMode, connectToSandbox } = useHooks();

	return { isSandboxMode, setSandboxMode, connectToSandbox };
};

export const useProduction = () => {
	const { connectToProduction } = useHooks();

	return { connectToProduction };
};

export const useManualConnection = () => {
	const {
		isManualConnectionMode,
		setManualConnectionMode,
		clientId,
		setClientId,
		clientSecret,
		setClientSecret,
		connectViaIdAndSecret,
	} = useHooks();

	return {
		isManualConnectionMode,
		setManualConnectionMode,
		clientId,
		setClientId,
		clientSecret,
		setClientSecret,
		connectViaIdAndSecret,
	};
};

export const useWooSettings = () => {
	const { wooSettings } = useHooks();

	return wooSettings;
};

export const useWebhooks = () => {
	const {
		webhooks,
		setWebhooks,
		registerWebhooks,
		startWebhookSimulation,
		checkWebhookSimulationState,
	} = useHooks();
	return {
		webhooks,
		setWebhooks,
		registerWebhooks,
		startWebhookSimulation,
		checkWebhookSimulationState,
	};
};
export const useMerchantInfo = () => {
	const { merchant } = useHooks();
	const { refreshMerchantData } = useDispatch( STORE_NAME );

	const verifyLoginStatus = useCallback( async () => {
		const result = await refreshMerchantData();

		if ( ! result.success ) {
			throw new Error( result?.message || result?.error?.message );
		}

		// Verify if the server state is "connected" and we have a merchant ID.
		return merchant?.isConnected && merchant?.id;
	}, [ refreshMerchantData, merchant ] );

	return {
		merchant, // Merchant details
		verifyLoginStatus, // Callback
	};
};

// -- Not using the `useHooks()` data provider --

export const useBusyState = () => {
	const { startActivity, stopActivity } = useDispatch( STORE_NAME );

	// Resolved value (object), contains a list of all running actions.
	const activities = useSelect(
		( select ) => select( STORE_NAME ).getActivityList(),
		[]
	);

	// Derive isBusy state from activities
	const isBusy = Object.keys( activities ).length > 0;

	// HOC that starts and stops an activity while the callback is executed.
	const withActivity = useCallback(
		async ( id, description, asyncFn ) => {
			startActivity( id, description );
			try {
				return await asyncFn();
			} finally {
				stopActivity( id );
			}
		},
		[ startActivity, stopActivity ]
	);

	return {
		withActivity, // HOC
		isBusy, // Boolean.
		activities, // Object.
	};
};
