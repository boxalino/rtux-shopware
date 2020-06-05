import RtuxApiTrackerEvent from '../rtux-api-tracker-event';
import DomAccessHelper from 'src/helper/dom-access.helper';

/**
 * Add product to basket from cart page, by using the product ID
 * (feature in Shopware6)
 */
export default class AddToCartByNumberEvent extends RtuxApiTrackerEvent
{
    supports(controllerName, actionName) {
        return controllerName === 'checkout' && actionName === 'cartpage';
    }

    execute() {
        const addToCartForm = DomAccessHelper.querySelector(document, '.cart-add-product', false);
        if (!addToCartForm) {
            return;
        }

        addToCartForm.addEventListener('submit', this._formSubmit.bind(this));
    }

    _formSubmit(event) {
        if (!this.active) {
            return;
        }

        const input = DomAccessHelper.querySelector(event.currentTarget, '.form-control');

        /*global bxq */
        bxq([
            'trackAddToBasket',
            input.value,
            1
        ]);
    }
}
