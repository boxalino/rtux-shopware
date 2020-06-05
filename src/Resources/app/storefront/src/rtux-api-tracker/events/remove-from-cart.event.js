import RtuxApiTrackerEvent from '../rtux-api-tracker-event';
import DomAccessHelper from 'src/helper/dom-access.helper';
import LineItemHelper from '../line-item.helper';

export default class RemoveFromCart extends RtuxApiTrackerEvent
{
    supports() {
        return true;
    }

    execute() {
        document.addEventListener('click', this._onRemoveFromCart.bind(this));
    }

    _onRemoveFromCart(event) {
        if (!this.active) {
            return;
        }

        const closest = event.target.closest('.cart-item-remove-button');
        if (!closest) {
            return;
        }
        const productId = DomAccessHelper.getDataAttribute(closest, 'product-id');
        const removedProduct = LineItemHelper.getLineItemById(productId);
        if(!removedProduct) {
            return;
        }

        /*global bxq */
        bxq([
            'trackAddToBasket',
            removedProduct['id'],
            parseInt(-removedProduct['quantity']),
            removedProduct['price'],
            window.currentCurrency
        ]);
    }
}
