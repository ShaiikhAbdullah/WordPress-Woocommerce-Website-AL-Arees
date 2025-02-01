// phpcs:ignoreFile
import {Optimizing} from "./optimizing";
import {useState} from "react";
const { __ } = wp.i18n;

export const NotOptimized = ({post_id}) => {
    const [isLoading, setIsLoading] = useState(false);
    const optimize = (post_id) => {
        if (two_speed.optimize_entire_website != false) {
            window.open(two_speed.optimize_entire_website + '?two_comes_from=gutenbergAfterLimit', '_blank');
        }
        else {
            setIsLoading(true);
            fetch(two_speed.ajax_url + '?action=two_optimize_page&post_id=' + post_id + '&nonce=' + two_speed.nonce + '&initiator=gutenberg')
                .then(_res => {
                });
        }
    }

    if (isLoading) {
        let optimizingTitle = 'Optimizing...';
        return <Optimizing title={optimizingTitle} />;
    } else {
        let title='', desc='', button_text='';
        if (two_speed.optimize_entire_website != false) {
            title = 'You’ve reached the Free Plan limit';
            desc = 'Upgrade to 10Web Booster Pro to<br> optimize all pages and enable<br> Cloudflare Enterprise CDN.';
            button_text = 'Upgrade';
        } else {
            title = 'Optimize with 10Web Booster';
            desc = 'Get a 90+ PageSpeed score';
            button_text = 'Optimize now';
        }
        return (
            <span className="two-editor-page-speed two-notoptimized ">
      <b>{__(title, 'tenweb-speed-optimizer')}</b>
      <p dangerouslySetInnerHTML={{__html: desc}} />
      <a onClick={() => optimize(post_id)}
         data-initiator="gutenberg"
         className="two-button-green">{__(button_text, 'tenweb-speed-optimizer')}</a></span>
        );
    }
}