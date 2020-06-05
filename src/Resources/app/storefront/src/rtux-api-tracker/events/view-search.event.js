import RtuxApiTrackerEvent from '../rtux-api-tracker-event';
import DomAccessHelper from 'src/helper/dom-access.helper';

export default class ViewSearchEvent extends RtuxApiTrackerEvent
{
    supports(controllerName, actionName) {
        return controllerName === 'search' && actionName === 'search';
    }

    execute() {
        if (!this.active) {
            return;
        }

        const searchInput = DomAccessHelper.querySelector(document, '.header-search-input');

        /*global bxq */
        bxq(['trackSearch', searchInput.value]);
    }
}
