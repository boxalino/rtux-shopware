import RtuxApiTrackerEvent from '../rtux-api-tracker-event';

export default class ViewNavigationEvent extends RtuxApiTrackerEvent
{
    supports(controllerName, actionName) {
        return controllerName === 'navigation' && actionName === 'index';
    }

    execute() {
        if (!this.active) {
            return;
        }

        const navigationId = window.activeNavigationId;
        if (!navigationId) {
            console.warn('[Boxalino RTUX API Tracker Plugin] Navigation ID could not be found.');
            return;
        }

        /*global bxq */
        bxq(['trackCategoryView', navigationId]);
    }

}
