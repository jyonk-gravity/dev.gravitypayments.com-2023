import mixpanel from 'mixpanel-browser';

if (typeof wp === 'undefined') {
  window.wp = {};
}
// eslint-disable-next-line no-unused-vars
(function($) {
    wp.mixpanel = {

    init: function() {
      mixpanel.init('f8ef711c035e4bf5e02dcea6201702c2');

      // Respect users privacy
      if (wp.mixpanel.getUserOptOut()) {
        return;
      }
    },
    getUserOptOut: function() {
      return mixpanel.has_opted_out_tracking();
    },
    setOptOutTracking: function(opt_out = true) {
      return opt_out ? mixpanel.opt_out_tracking() : mixpanel.opt_in_tracking();
    },
    track: function(event_name = '', properties = {}, options = {}, _callback) {
      // Respect users privacy
      if (wp.mixpanel.getUserOptOut()) {
        return;
      }
      // console.log('Track Event: ', event_name);
      return mixpanel.track(event_name, properties, options, _callback);
    },
    alias: function(alias = '', _callback) {
      return mixpanel.alias(alias, _callback);
    },
    get_distinct_id: function() {
      return mixpanel.get_distinct_id();
    },
    identify: function(unique_id = '', _callback) {
      return mixpanel.identify(unique_id, _callback);
    },
    people: {
      set: function(properties = {}) {
        return mixpanel.people.set(properties);
      },
    },
  };
})(jQuery);

wp.mixpanel.init();