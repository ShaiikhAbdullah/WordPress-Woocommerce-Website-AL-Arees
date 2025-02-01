import { ToggleControl } from '@wordpress/components';
import SettingsBlock from './SettingsBlock';
import { Header, Title, Action, Description } from './SettingsBlockElements';

const ToggleSettingsBlock = ( { title, description, ...props } ) => (
	<SettingsBlock { ...props } className="ppcp-r-settings-block__toggle">
		<Action>
			<ToggleControl
				className="ppcp-r-settings-block__toggle-control"
				__nextHasNoMarginBottom={ true }
				checked={ props.actionProps?.value }
				onChange={ ( newValue ) =>
					props.actionProps?.callback(
						props.actionProps?.key,
						newValue
					)
				}
			/>
		</Action>
		<Header>
			{ title && <Title>{ title }</Title> }
			{ description && <Description>{ description }</Description> }
		</Header>
	</SettingsBlock>
);

export default ToggleSettingsBlock;
