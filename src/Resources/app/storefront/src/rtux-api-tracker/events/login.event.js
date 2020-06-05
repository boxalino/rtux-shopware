import RtuxApiTrackerEvent from '../rtux-api-tracker-event';

export default class AccountEvent extends RtuxApiTrackerEvent
{
    supports(controllerName, actionName) {
        return (
            (controllerName === 'accountprofile' && actionName === 'index' && document.referrer.endsWith('/account/login')) ||
            (controllerName === 'checkout' && actionName =='confirmpage' && document.referrer.endsWith('/checkout/register'))
        );
    }

    execute() {
        if (!this.active) {
            return;
        }

        /*global bxq */
        bxq(['trackLogin', atob(this.accessAccountId())]);
    }

    /**
     * @returns {*}
     */
    accessAccountId() {
        return window.preferentialLog;
    }

}
