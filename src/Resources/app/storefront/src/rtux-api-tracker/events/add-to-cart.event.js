import EventAwareRtuxApiTrackerEvent from '../event-aware-rtux-api-tracker-event';

export default class AddToCartEvent extends EventAwareRtuxApiTrackerEvent
{
    supports() {
        return true;
    }

    getPluginName() {
        return 'AddToCart';
    }

    getEvents() {
        return {
            'beforeFormSubmit':  this._beforeFormSubmit.bind(this)
        };
    }

    _beforeFormSubmit(event) {
        if (!this.active) {
            return;
        }

        const formData = event.detail;
        let productId = null;

        formData.forEach((value, key) => {
            if (key.endsWith('[id]')) {
                productId = value;
            }
        });

        if (!productId) {
            console.warn('[Boxalino RTUX API Tracker Plugin] Product ID could not be fetched. Skipping.');
            return;
        }

        /*global bxq */
        bxq([
            'trackAddToBasket',
            productId,
            formData.get('lineItems[' + productId + '][quantity]'),
            formData.get('lineItems[' + productId + '][price]'),
            window.currentCurrency,
            {'name': formData.get('product-name')}
        ]);
    }
}
