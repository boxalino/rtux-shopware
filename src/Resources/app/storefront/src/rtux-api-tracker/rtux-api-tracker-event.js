export default class RtuxApiTrackerEvent
{
    active = true;
    currency = '';
    finalPriceSelector = '.checkout-aside-summary-value.checkout-aside-summary-total';

    /* eslint-disable no-unused-vars */
    /**
     * @param {string} controllerName
     * @param {string} actionName
     * @returns {boolean}
     */
    supports(controllerName, actionName) {
        console.warn('[Boxalino RTUX API Tracker Plugin] Method \'supports\' was not overridden by `' + this.constructor.name + '`. Default return set to false.');
        return false;
    }
    /* eslint-enable no-unused-vars */

    execute() {
        console.warn('[Boxalino RTUX API Tracker Plugin] Method \'execute\' was not overridden by `' + this.constructor.name + '`.');
    }

    disable() {
        this.active = false;
    }

    setFinalPriceSelector(selector) {
        this.finalPriceSelector = selector;
    }

}
