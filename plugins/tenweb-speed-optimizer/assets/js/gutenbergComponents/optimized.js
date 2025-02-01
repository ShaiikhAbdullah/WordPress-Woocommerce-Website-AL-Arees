import {Score} from "./score";
import {useEffect,useState} from "react";
import {set_scoreData} from "./set_scoreData";
import {ScoreReanalyzing} from "./ScoreReanalyzing";
import {Optimizing} from "./optimizing";
const { __ } = wp.i18n;

export const Optimized = ({metas, post_id}) => {
    let score_data = set_scoreData( {post_id, metas} );
    let scoreReanalyzingValues = ScoreReanalyzing( {post_id, score_data} );
    useEffect(() => {
        const elements = document.getElementsByClassName('two-score-circle');
        for (let i = 0; i < elements.length; i++) {
            two_draw_score_circle(elements[i]);
        }
    }, [score_data]);
    const [isLoading, setIsLoading] = useState(false);
    const optimize = (post_id) => {
        setIsLoading(true);
        fetch(two_speed.ajax_url + '?action=two_optimize_page&post_id=' + post_id + '&nonce=' + two_speed.nonce + '&initiator=gutenberg')
            .then(_res => {
            });
    }
    let date = 0;
    if ( score_data && score_data['current_score'] ) {
        if( (post_id in two_speed.critical_pages) && two_speed.critical_pages[post_id]['critical_date'] ) {
            date = two_speed.critical_pages[post_id]['critical_date'];
        } else if( score_data['current_score']['date'] ) {
            date = Date.parse(score_data['current_score']['date']);
        }
    }
    let modified_date='',re_optimize = false;
    modified_date = Date.parse(wp.data.select('core/editor').getEditedPostAttribute('modified') + 'Z')/1000;
    if ( modified_date > date && date != 0 ) {
        re_optimize = true;
    }

    let re_opt_button = <div className="two-score-container-reoptimize">
        <a onClick={() => optimize(post_id)}
           data-initiator="gutenberg"
           className="two-button-green ">{__('Re-optimize', 'tenweb-speed-optimizer')}</a>
    </div>;
    if ( scoreReanalyzingValues.reanalyzingStatus ) {
        re_opt_button = <div className="two-score-container-reoptimize">
            <a href='#'
               className="two-button-green two-deactivated-button">{__('Re-optimize', 'tenweb-speed-optimizer')}</a>
        </div>;
    }
    const title = wp.data.select("core/editor").getEditedPostAttribute( 'title' );

    if ( isLoading ) {
        let optimizingTitle = 'Re-optimizing...';
        return <Optimizing title={optimizingTitle} />;
    } else {
        return (
            <div className="two-score-section two-score-section-gutenberg">
                <p className="two-gutenberg-container-title">
                    <span><b>{title}</b></span>{__(' page is successfully optimized', 'tenweb-speed-optimizer')}</p>
                <div className="two-score-container-both">
                    <div className="two-score-container-old">
                        <div className="two-score-header">{__('Before optimization', 'tenweb-speed-optimizer')}</div>
                        <div className={"two-old-scores " + scoreReanalyzingValues.no_old_scores}>
                            <Score score={ score_data && score_data.previous_score && score_data.previous_score.mobile_score}
                                   tti={score_data && score_data.previous_score && score_data.previous_score.mobile_tti}
                                   device='Mobile'/>
                            <Score score={score_data && score_data.previous_score && score_data.previous_score.desktop_score}
                                   tti={score_data && score_data.previous_score && score_data.previous_score.desktop_tti}
                                   device='Desktop'/>
                            {scoreReanalyzingValues.score_check_link_before}
                        </div>
                    </div>
                    <div className="two-score-container-new">
                        <div className="two-score-header">{__('After optimization', 'tenweb-speed-optimizer')}</div>
                        <div className={"two-new-scores " + scoreReanalyzingValues.no_new_scores}>
                            <Score score={score_data && score_data.current_score && score_data.current_score.mobile_score}
                                   tti={score_data && score_data.current_score && score_data.current_score.mobile_tti}
                                   device='Mobile'/>
                            <Score score={score_data && score_data.current_score && score_data.current_score.desktop_score}
                                   tti={score_data && score_data.current_score && score_data.current_score.desktop_tti}
                                   device='Desktop'/>
                            {scoreReanalyzingValues.score_check_link_after}
                        </div>
                    </div>
                </div>
                {scoreReanalyzingValues.score_check_button}
                {re_optimize && re_opt_button}
            </div>
        );
    }
}