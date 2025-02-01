import {useState} from "react";
const { __ } = wp.i18n;

export const ScoreReanalyzing = ({post_id, score_data}) => {
    let reanalyze_button_status  = false, reanalyze_button_status_current = false, reanalyze_button_status_previous = false;
    if( score_data ) {
        if (score_data['current_score'] && score_data['current_score']['status']
            && score_data['current_score']['status'] == 'inprogress' ) {
            reanalyze_button_status = true;
            reanalyze_button_status_current = true;
        }
        if (score_data['previous_score'] && score_data['previous_score']['status']
            && score_data['previous_score']['status'] == 'inprogress' ) {
            reanalyze_button_status = true;
            reanalyze_button_status_previous = true;
        }
    }

    let no_old_scores = '', no_new_scores = '', reanalyze_score_for = 'new';
    let score_check_link_before = '', score_check_link_after ='', score_check_button ='';
    if ( !score_data || !score_data['previous_score'] || reanalyze_button_status_previous ) {
        no_old_scores = 'two-no-scores';
        reanalyze_score_for = 'old';
    }
    if ( !score_data || !score_data['current_score'] || reanalyze_button_status_current ) {
        no_new_scores = 'two-no-scores';
    }
    if ( no_old_scores != '' && no_new_scores != '' ) {
        reanalyze_score_for = 'both';
    }


    const [isReanalyzing, setIsReanalyzing] = useState(reanalyze_button_status);
    const [isReanalyzeButton, setIsReanalyzeButton] = useState(false);
    const [new_no_new_scores, setno_new_scores] = useState(no_new_scores);

    const reanalyze = (post_id,ReanalyzeButton) => {
        if ( !isReanalyzing ) {
            setIsReanalyzing(true);
            if (ReanalyzeButton == 'button') {
                setIsReanalyzeButton(true);
                setno_new_scores('two-no-scores');
            }

            const url = two_speed.ajax_url;
            var params = new FormData();

            params.append('action', 'two_recount_score');
            params.append('post_id', post_id);
            params.append('nonce', two_speed.nonce);
            params.append('initiator', 'gutenberg');
            params.append('reanalyze_score_for', reanalyze_score_for);

            fetch(url, {
                    method: "POST",
                    body: params,
                }
            )
                .then(response => response.json())
                .then(data => {
                    console.log(data)
                })
                .catch((err) => console.log(err));
        }
    }

    if ( isReanalyzing ) {
        score_check_button = <div className="two-gutenberg-reanalyze_container two_reanalyze_loading">
            <a href="#" className="two_reanalyze_button">{__("Reanalyzing...", "tenweb-speed-optimizer")}</a>
            <p className="two-description">{__("Reload in 2 minutes to see the new score","tenweb-speed-optimizer")}</p>
        </div>;
        if ( isReanalyzeButton ) {
            score_check_link_after = <span className="two-page-speed two-optimizing"></span>;
            if ( reanalyze_score_for == 'both') {
                score_check_link_before = <span className="two-page-speed two-optimizing"></span>;
            }
        }
        else {
            if ( reanalyze_score_for == 'both') {
                score_check_link_before = <span className="two-page-speed two-optimizing"></span>;
                score_check_link_after = <span className="two-page-speed two-optimizing"></span>;
            } else if (reanalyze_score_for == 'old'){
                score_check_link_before = <span className="two-page-speed two-optimizing"></span>;
            } else if (reanalyze_score_for == 'new') {
                score_check_link_after = <span className="two-page-speed two-optimizing"></span>;
            }
        }
    } else {
        score_check_button = <div className="two-gutenberg-reanalyze_container">
            <a onClick={() => reanalyze(post_id,'button')}
               className="two_reanalyze_button">{__("Reanalyze", "tenweb-speed-optimizer")}
            </a>
        </div>;
        let link = <a onClick={() => reanalyze(post_id,'')} data-post_id={post_id}
                      target="_blank" className="two_reanalyze_link"></a>;
        if ( no_old_scores != '' && no_new_scores != '') {
            score_check_link_before = link;
            score_check_link_after = link;
        } else if (no_old_scores != ''){
            score_check_link_before = link;
            score_check_link_after = '';

        } else if (no_new_scores != '') {
            score_check_link_before = '';
            score_check_link_after = link;
        }
    }

    return {
        'score_check_button': score_check_button,
        'score_check_link_before': score_check_link_before,
        'score_check_link_after': score_check_link_after,
        'no_new_scores': new_no_new_scores,
        'no_old_scores': no_old_scores,
        'reanalyzingStatus': isReanalyzing,
    }
}