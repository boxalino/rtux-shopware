import RtuxApiTrackerEvent from '../rtux-api-tracker-event';
import DomAccessHelper from 'src/helper/dom-access.helper';
import LineItemHelper from '../line-item.helper';

/**
 * @deprecated the event is sent server-side
 */
export default class PurchaseEvent extends RtuxApiTrackerEvent
{
    supports(controllerName, actionName) {
        return controllerName === 'checkout' && actionName === 'confirmpage';
    }

    execute() {
        const tosInput = DomAccessHelper.querySelector(document, '#tos');
        const totalValue = DomAccessHelper.querySelector(document, this.finalPriceSelector).innerText;
        const items = LineItemHelper.getLineItems();

        DomAccessHelper.querySelector(document, '#confirmFormSubmit').addEventListener(
            'click',
            this._onConfirm.bind(this, tosInput, this.getPurchaseValue(totalValue), this.getCurrency(totalValue), items)
        );
    }

    _onConfirm(tosInput, purchaseValue, currency, items) {
        if (!this.active) {
            return;
        }

        if (!tosInput.checked) {
            return;
        }

        /*global bxq */
        bxq([
            'trackPurchase',
            purchaseValue,
            currency,
            items,
            window.contextToken
        ]);
    }

    /**
     * @param totalValue
     * @returns {*}
     */
    getCurrency(totalValue){
        return totalValue.replace(/([a-z]+)/i, '$1').split(/[^a-z]+/ig).filter(Boolean)[0];
    }

    /**
     * Get final price as appearing in the invoice, after vouchers and taxes
     * @param totalValue
     * @returns {number}
     */
    getPurchaseValue(totalValue){
        /*eslint no-useless-escape: "error"*/
        let price = totalValue.match(/[\d.,/']+|\D+/g).filter(function(el) { return el.replace(/\D/g, '');})[0];
        if(price.indexOf('\'')>-1) { price = price.replace('\'', ''); }
        if (price.indexOf(',') < price.indexOf('.')) {
            return parseFloat(price.replace(',',''));
        }

        return parseFloat(price);
    }
}
