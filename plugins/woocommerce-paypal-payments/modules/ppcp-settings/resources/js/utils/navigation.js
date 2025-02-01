import { addQueryArgs } from '@wordpress/url';

const getLocation = () => window.location;

const pushHistory = ( path ) => window.history.pushState( { path }, '', path );

/**
 * Get the current path from the browser.
 *
 * @return {string} Current path.
 */
export const getPath = () => getLocation().pathname;

/**
 * Get the current query string, parsed into an object, from history.
 *
 * @return {Object} Current query object, defaults to empty object.
 */
export const getQuery = () =>
	Object.fromEntries( new URLSearchParams( getLocation().search ) );

/**
 * Updates the query parameters of the current page.
 *
 * @param {Object} query Object of params to be updated.
 * @throws {TypeError} If the query is not an object.
 */
export const updateQueryString = ( query ) =>
	pushHistory( getNewPath( query ) );

/**
 * Return a URL with set query parameters.
 *
 * @param {Object} query    Object of params to be updated.
 * @param {string} basePath Optional. Define the path for the new URL.
 * @return {string} Updated URL merging query params into existing params.
 */
export const getNewPath = ( query, basePath = getPath() ) =>
	addQueryArgs( basePath, { ...getQuery(), ...query } );
