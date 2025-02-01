const SettingsBlock = ( { className, children } ) => {
	const blockClassName = [ 'ppcp-r-settings-block', className ].filter(
		Boolean
	);

	return <div className={ blockClassName.join( ' ' ) }>{ children }</div>;
};

export default SettingsBlock;
