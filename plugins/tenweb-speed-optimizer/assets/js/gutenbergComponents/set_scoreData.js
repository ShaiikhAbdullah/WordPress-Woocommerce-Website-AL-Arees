export const set_scoreData = ({post_id, metas}) => {
    let score_data;
    if ( post_id == 'front_page' ) {
        score_data = two_speed.two_front_page_speed;
    } else {
        score_data = metas.two_page_speed;
    }

    return score_data;
}