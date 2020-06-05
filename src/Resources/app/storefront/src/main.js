import RtuxApiTrackerPlugin from './rtux-api-tracker/rtux-api-tracker.plugin.js';

const PluginManager = window.PluginManager;
if (window.rtuxApiTrackerActive) {
    PluginManager.register('RtuxApiTrackerPlugin', RtuxApiTrackerPlugin);
}
