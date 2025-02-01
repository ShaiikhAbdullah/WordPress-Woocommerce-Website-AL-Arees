import Accordion from '../AccordionSection';
import SettingsBlock from './SettingsBlock';
import {
	Header,
	Title,
	Action,
	Description,
	TitleWrapper,
} from './SettingsBlockElements';

const SettingsAccordion = ( { title, description, children, ...props } ) => (
	<SettingsBlock { ...props } className="ppcp-r-settings-block__accordion">
		<Accordion
			title={ title }
			description={ description }
			Header={ Header }
			TitleWrapper={ TitleWrapper }
			Title={ Title }
			Action={ Action }
			Description={ Description }
		>
			{ children }
		</Accordion>
	</SettingsBlock>
);

export default SettingsAccordion;
