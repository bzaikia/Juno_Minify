# Juno_Minify

Minified css and css without merging them. The minification does not happen on magento site, the external server handle it and send the result back to magento site

The original file will not be touch, the module create another file on same folder having same name with the original file, ending with junominify

e.g: 
orignal file: http://graftons.juno.is/js/lib/jquery/noconflict.js
minified file: http://graftons.juno.is/js/lib/jquery/noconflict.junominify.js

magento site will load the minified one if the module is available and the minified file exist

It does not happen immediately after installing. It will be trigger nightly at 0AM by magento cron

contact me for any support: hieu@junowebdesign.com
