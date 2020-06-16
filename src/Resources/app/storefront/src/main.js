import RtuxApiTrackerPlugin from './rtux-api-tracker/rtux-api-tracker.plugin';
import RtuxApiHelper from './rtux-api/rtux-api.helper';

const PluginManager = window.PluginManager;
PluginManager.register('RtuxApiHelper', RtuxApiHelper, document);
if (window.rtuxApiTrackerActive) {
    PluginManager.register('RtuxApiTrackerPlugin', RtuxApiTrackerPlugin);
}
