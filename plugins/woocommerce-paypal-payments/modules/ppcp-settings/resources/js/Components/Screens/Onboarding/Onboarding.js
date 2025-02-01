import Container from '../../ReusableComponents/Container';
import { OnboardingHooks } from '../../../data';

import { getSteps, getCurrentStep } from './availableSteps';
import Navigation from './Components/Navigation';

const Onboarding = () => {
	const { step, setStep, flags } = OnboardingHooks.useSteps();
	const Steps = getSteps( flags );
	const currentStep = getCurrentStep( step, Steps );

	const handleNext = () => setStep( currentStep.nextStep );
	const handlePrev = () => setStep( currentStep.prevStep );
	const handleExit = () => {
		window.location.href = window.ppcpSettings.wcPaymentsTabUrl;
	};

	return (
		<>
			<Navigation
				stepDetails={ currentStep }
				onNext={ handleNext }
				onPrev={ handlePrev }
				onExit={ handleExit }
			/>

			<Container page="onboarding">
				<div className="ppcp-r-card">
					<currentStep.StepComponent
						setStep={ setStep }
						currentStep={ step }
						stepperOrder={ Steps }
					/>
				</div>
			</Container>
		</>
	);
};

export default Onboarding;
