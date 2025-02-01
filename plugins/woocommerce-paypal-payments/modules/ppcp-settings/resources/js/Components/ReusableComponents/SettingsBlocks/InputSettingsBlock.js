import { TextControl } from '@wordpress/components';
import SettingsBlock from './SettingsBlock';
import {
	Title,
	Action,
	Description,
	SupplementaryLabel,
} from './SettingsBlockElements';

const DEFAULT_ELEMENT_ORDER = [ 'title', 'action', 'description' ];

const ELEMENT_RENDERERS = {
	title: ( { title, supplementaryLabel } ) => (
		<Title>
			{ title }
			{ supplementaryLabel && (
				<SupplementaryLabel>{ supplementaryLabel }</SupplementaryLabel>
			) }
		</Title>
	),
	action: ( { actionProps } ) => (
		<Action>
			<TextControl
				className="ppcp-r-vertical-text-control"
				placeholder={ actionProps?.placeholder }
				value={ actionProps?.value }
				onChange={ ( newValue ) =>
					actionProps?.callback( actionProps?.key, newValue )
				}
			/>
		</Action>
	),
	description: ( { description } ) => (
		<Description>{ description }</Description>
	),
};

const InputSettingsBlock = ( {
	title,
	description,
	supplementaryLabel,
	order = DEFAULT_ELEMENT_ORDER,
	...props
} ) => (
	<SettingsBlock { ...props } className="ppcp-r-settings-block__input">
		{ order.map( ( elementKey ) => {
			const RenderElement = ELEMENT_RENDERERS[ elementKey ];
			return RenderElement ? (
				<RenderElement
					key={ elementKey }
					title={ title }
					description={ description }
					supplementaryLabel={ supplementaryLabel }
					actionProps={ props.actionProps }
				/>
			) : null;
		} ) }
	</SettingsBlock>
);

export default InputSettingsBlock;
