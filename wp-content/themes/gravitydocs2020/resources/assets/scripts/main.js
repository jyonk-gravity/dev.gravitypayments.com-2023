// import external dependencies
import 'jquery';

// Import everything from autoload
import './autoload/**/*'

// Mixpanel
// import './vendor/mixpanel';

// import local dependencies
import Router from './util/Router';
import common from './routes/common';
import home from './routes/home';
import aboutUs from './routes/about';
import releaseNotes from './routes/release-notes';



/** Populate Router instance with DOM routes */
const routes = new Router({
  // All pages
  common,
  // Home page
  home,
  // About Us page, note the change from about-us to aboutUs.
  aboutUs,
  // Releas Notes page
  releaseNotes,
});

// Load Events
jQuery(document).ready(() => routes.loadEvents());
