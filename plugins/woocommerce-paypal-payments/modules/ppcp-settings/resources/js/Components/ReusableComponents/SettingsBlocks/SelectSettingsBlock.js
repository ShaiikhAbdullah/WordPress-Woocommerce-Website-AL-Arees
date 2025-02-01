import Select, { components } from 'react-select';
import data from '../../../utils/data';
import SettingsBlock from './SettingsBlock';
import { Title, Action, Description } from './SettingsBlockElements';

const DEFAULT_ELEMENT_ORDER = [ 'title', 'action', 'description' ];

const DropdownIndicator = ( props ) => (
	<components.DropdownIndicator { ...props }>
		{ data().getImage( 'icon-arrow-down.svg' ) }
	</components.DropdownIndicator>
);

const ELEMENT_RENDERERS = {
	title: ( { title } ) => <Title>{ title }</Title>,
	action: ( { actionProps } ) => (
		<Action>
			<Select
				className="ppcp-r-multiselect"
				classNamePrefix="ppcp-r"
				isMulti={ actionProps?.isMulti }
				options={ actionProps?.options }
				components={ { DropdownIndicator } }
			/>
		</Action>
	),
	description: ( { description } ) => (
		<Description>{ description }</Description>
	),
};

const SelectSettingsBlock = ( {
	title,
	description,
	order = DEFAULT_ELEMENT_ORDER,
	...props
} ) => (
	<SettingsBlock { ...props } className="ppcp-r-settings-block__select">
		{ order.map( ( elementKey ) => {
			const RenderElement = ELEMENT_RENDERERS[ elementKey ];
			return RenderElement ? (
				<RenderElement
					key={ elementKey }
					title={ title }
					description={ description }
					actionProps={ props.actionProps }
				/>
			) : null;
		} ) }
	</SettingsBlock>
);

export default SelectSettingsBlock;
