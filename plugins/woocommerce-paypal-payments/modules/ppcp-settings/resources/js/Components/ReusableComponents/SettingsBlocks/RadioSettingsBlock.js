import SettingsBlock from './SettingsBlock';
import { Header, Title, Description } from './SettingsBlockElements';
import { PayPalRdbWithContent } from '../Fields';

const RadioSettingsBlock = ( {
	title,
	description,
	options = [],
	...props
} ) => (
	<SettingsBlock
		{ ...props }
		className="ppcp-r-settings-block__radio ppcp-r-settings-block--expert-rdb"
	>
		<Header>
			<Title>{ title }</Title>
			<Description>{ description }</Description>
		</Header>
		{ options.map( ( option ) => (
			<PayPalRdbWithContent
				key={ option.id }
				id={ option.id }
				name={ props.actionProps?.name }
				value={ option.value }
				currentValue={ props.actionProps?.currentValue }
				handleRdbState={ ( newValue ) =>
					props.actionProps?.callback(
						props.actionProps?.key,
						newValue
					)
				}
				label={ option.label }
				description={ option.description }
				toggleAdditionalContent={ true }
			>
				{ option.additionalContent }
			</PayPalRdbWithContent>
		) ) }
	</SettingsBlock>
);

export default RadioSettingsBlock;
