import { Spinner } from '@wordpress/components';

const SpinnerOverlay = ( { message = '' } ) => {
	return (
		<div className="ppcp-r-spinner-overlay">
			{ message && (
				<span className="ppcp-r-spinner-overlay__message">
					{ message }
				</span>
			) }
			<Spinner />
		</div>
	);
};

export default SpinnerOverlay;
