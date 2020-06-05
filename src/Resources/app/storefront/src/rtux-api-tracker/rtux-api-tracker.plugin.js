import Plugin from 'src/plugin-system/plugin.class';

import AddToCartEvent from './events/add-to-cart.event';
import AddToCartByNumberEvent from './events/add-to-cart-by-number.event';
import LoginEvent from './events/login.event';
import PurchaseEvent from './events/purchase.event';
import ViewItemEvent from './events/view-item.event';
import ViewSearchEvent from './events/view-search.event';
import ViewNavigationEvent from './events/view-navigation.event';

export default class RtuxApiTrackerPlugin extends Plugin
{
    init() {
        window.bxqCallback();

        this.controllerName = window.controllerName;
        this.actionName = window.actionName;
        this.events = [];

        this.registerDefaultEvents();
        this.handleEvents();
    }

    handleEvents() {
        this.events.forEach(event => {
            if (!event.supports(this.controllerName, this.actionName)) {
                return;
            }
            if(window.finalPriceSelector) {
                event.setFinalPriceSelector(window.finalPriceSelector);
            }
            event.execute();
        });
    }

    registerDefaultEvents() {
        this.registerEvent(AddToCartEvent);
        this.registerEvent(AddToCartByNumberEvent);
        this.registerEvent(LoginEvent);
        this.registerEvent(PurchaseEvent);
        this.registerEvent(ViewItemEvent);
        this.registerEvent(ViewSearchEvent);
        this.registerEvent(ViewNavigationEvent);
    }

    /**
     * @param { RtuxApiTrackerEvent } event
     */
    registerEvent(event) {
        this.events.push(new event());
    }

    disableEvents() {
        this.events.forEach(event => {
            event.disable();
        });
    }
}
