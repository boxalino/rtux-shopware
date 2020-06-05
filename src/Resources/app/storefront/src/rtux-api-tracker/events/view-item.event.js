import RtuxApiTrackerEvent from '../rtux-api-tracker-event';
import DomAccessHelper from 'src/helper/dom-access.helper';

export default class ViewItemEvent extends RtuxApiTrackerEvent
{
    supports(controllerName, actionName) {
        return controllerName === 'product' && actionName === 'index';
    }

    execute() {
        if (!this.active) {
            return;
        }

        const form = DomAccessHelper.querySelector(document, '#productDetailPageBuyProductForm');
        const productId = this.findProductId(DomAccessHelper.querySelectorAll(form, 'input'));
        const productName = DomAccessHelper.querySelector(form, 'input[name=product-name]').value;

        if (!productId) {
            console.warn('[Boxalino RTUX API Tracker Plugin] Product ID could not be found.');
            return;
        }

        /*global bxq */
        bxq([
            'trackProductView',
            productId,
            {'name':productName}
        ]);
    }

    /**
     * @param { NodeList } inputs
     * @return ?string
     */
    findProductId(inputs) {
        let productId = null;

        inputs.forEach(item => {
            if (DomAccessHelper.getAttribute(item, 'name').endsWith('[id]')) {
                productId = item.value;
            }
        });

        return productId;
    }
}
