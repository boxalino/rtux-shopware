import RtuxApiTrackerEvent from './rtux-api-tracker-event';

export default class EventAwareRtuxApiTrackerEvent extends RtuxApiTrackerEvent
{
    execute() {
        const events = this.getEvents();
        const pluginRegistry = window.PluginManager;

        pluginRegistry.getPluginInstances(this.getPluginName()).forEach((pluginInstance) => {
            Object.keys(events).forEach((eventName) => {
                pluginInstance.$emitter.subscribe(eventName, events[eventName]);
            });
        });
    }

    /**
     * @return {Object}
     */
    getEvents() {
        console.warn('[Boxalino RTUX API Tracker Plugin] Method \'getEvents\' was not overridden by `' + this.constructor.name + '`.');
    }

    /**
     * @return string
     */
    getPluginName() {
        console.warn('[Boxalino RTUX API Tracker Plugin] Method \'getPluginName\' was not overridden by `' + this.constructor.name + '`.');
    }
}
