/**
 * Reducer: Defines store structure and state updates for this module.
 *
 * Manages both transient (temporary) and persistent (saved) state.
 * The initial state must define all properties, as dynamic additions are not supported.
 *
 * @file
 */

import { createReducer, createSetters } from '../utils';
import ACTION_TYPES from './action-types';

// Store structure.

const defaultTransient = Object.freeze( {
	isReady: false,
	activities: new Map(),

	// Read only values, provided by the server via hydrate.
	merchant: Object.freeze( {
		isConnected: false,
		isSandbox: false,
		id: '',
		email: '',
	} ),

	wooSettings: Object.freeze( {
		storeCountry: '',
		storeCurrency: '',
	} ),
} );

const defaultPersistent = Object.freeze( {
	useSandbox: false,
	useManualConnection: false,
	clientId: '',
	clientSecret: '',
	webhooks: [],
} );

// Reducer logic.

const [ setTransient, setPersistent ] = createSetters(
	defaultTransient,
	defaultPersistent
);

const commonReducer = createReducer( defaultTransient, defaultPersistent, {
	[ ACTION_TYPES.SET_TRANSIENT ]: ( state, action ) =>
		setTransient( state, action ),

	[ ACTION_TYPES.SET_PERSISTENT ]: ( state, action ) =>
		setPersistent( state, action ),

	[ ACTION_TYPES.RESET ]: ( state ) => {
		const cleanState = setTransient(
			setPersistent( state, defaultPersistent ),
			defaultTransient
		);

		// Keep "read-only" details and initialization flags.
		cleanState.wooSettings = { ...state.wooSettings };
		cleanState.isReady = true;

		return cleanState;
	},

	[ ACTION_TYPES.START_ACTIVITY ]: ( state, payload ) => {
		return setTransient( state, {
			activities: new Map( state.activities ).set(
				payload.id,
				payload.description
			),
		} );
	},

	[ ACTION_TYPES.STOP_ACTIVITY ]: ( state, payload ) => {
		const newActivities = new Map( state.activities );
		newActivities.delete( payload.id );
		return setTransient( state, { activities: newActivities } );
	},

	[ ACTION_TYPES.DO_REFRESH_MERCHANT ]: ( state ) => ( {
		...state,
		merchant: Object.freeze( { ...defaultTransient.merchant } ),
	} ),

	[ ACTION_TYPES.HYDRATE ]: ( state, payload ) => {
		const newState = setPersistent( state, payload.data );

		// Populate read-only properties.
		[ 'wooSettings', 'merchant' ].forEach( ( key ) => {
			if ( ! payload[ key ] ) {
				return;
			}

			newState[ key ] = Object.freeze( {
				...newState[ key ],
				...payload[ key ],
			} );
		} );

		return newState;
	},
} );

export default commonReducer;
