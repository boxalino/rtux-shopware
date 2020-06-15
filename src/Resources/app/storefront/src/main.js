import RtuxApiTrackerPlugin from './rtux-api-tracker/rtux-api-tracker.plugin.js';
import RtuxApiHelper from './rtux-api/rtux-api.helper.js';

const PluginManager = window.PluginManager;
PluginManager.register('RtuxApiHelper', RtuxApiHelper);
if (window.rtuxApiTrackerActive) {
    PluginManager.register('RtuxApiTrackerPlugin', RtuxApiTrackerPlugin);
}
