const { __ } = wp.i18n;

export const Score = ({score, tti, device}) => {
    let scoreClass = 'two-score-mobile';
    if (device == 'Desktop') {
        scoreClass = 'two-score-desktop';
    }
    return <div className={scoreClass}>
        <div className="two-score-circle two_circle_with_bg"
             data-id={device}
             data-thickness="2"
             data-size="40"
             data-score={score}
             data-loading-time={tti}>
            <span className="two-score-circle-animated"></span>
        </div>
        <div className="two-score-text">
            <span className="two-score-text-name">{__(device + ' score', 'tenweb-speed-optimizer')}</span>
            <span className="two-load-text-time">{__('Load time:', 'tenweb-speed-optimizer')} <span
                className="two-load-time"></span>s</span>
        </div>
    </div>
}