import DomAccessHelper from 'src/helper/dom-access.helper';

/**
 * Creates an array of products on the checkout page
 */
export default class LineItemHelper
{
    /**
     * @returns { Object[] }
     */
    static getLineItems() {
        const lineItemsContainer = DomAccessHelper.querySelector(document, '.hidden-line-items-information');
        const lineItemDataElements = DomAccessHelper.querySelectorAll(lineItemsContainer, '.hidden-line-item');
        const lineItems = [];

        lineItemDataElements.forEach(itemEl => {
            lineItems.push({
                'id': DomAccessHelper.getDataAttribute(itemEl, 'id'),
                'name': DomAccessHelper.getDataAttribute(itemEl, 'name'),
                'quantity': DomAccessHelper.getDataAttribute(itemEl, 'quantity'),
                'price': DomAccessHelper.getDataAttribute(itemEl, 'price').toFixed(2)
            });
        });

        return lineItems;
    }

    static getLineItemById(id) {
        const lineItemsContainer = DomAccessHelper.querySelector(document, '.hidden-line-items-information');
        const lineItemDataElements = DomAccessHelper.querySelectorAll(lineItemsContainer, '.hidden-line-item');
        let lineItem = null;

        lineItemDataElements.forEach(itemEl => {
            const elementId = DomAccessHelper.getDataAttribute(itemEl, 'id');
            if(elementId === id)
            {
                lineItem = {
                    'id': DomAccessHelper.getDataAttribute(itemEl, 'id'),
                    'name': DomAccessHelper.getDataAttribute(itemEl, 'name'),
                    'quantity': DomAccessHelper.getDataAttribute(itemEl, 'quantity'),
                    'price': DomAccessHelper.getDataAttribute(itemEl, 'price').toFixed(2)
                };
            }
        });

        return lineItem;
    }
}
