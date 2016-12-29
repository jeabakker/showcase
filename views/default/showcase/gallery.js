define(function(require) {

	var lightbox = require('elgg/lightbox');

	var options = {
      photo: true,
   };

   lightbox.bind('a[rel="showcase-gallery"]', options, false); // 3rd attribute ensures binding is done without proxies
});
