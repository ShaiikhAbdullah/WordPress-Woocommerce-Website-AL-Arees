const { __ } = wp.i18n;

export const Optimizing = ({title}) => {
    let desc;
    desc = 'Reload in 2 minutes to see<br> the new score';
    return <span className="two-page-speed two-optimizing two-loading-bg">{__(title, 'tenweb-speed-optimizer')}
            <p className="two-description" dangerouslySetInnerHTML={{__html: desc}} />
    </span>;
}