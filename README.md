# Juno_Minify

# config 
* enable/disable: on off the module
* server: the external server that handle js/css minification.

# How it work: 

* Minified css and css without merging them. The minification does not happen on magento site, the external server (config) handle it and send the result back to magento site

* The original file will not be touch, the module create another file on same folder having same name with the original file, ending with junominify

e.g: 
orignal file: http://graftons.juno.is/js/lib/jquery/noconflict.js .
minified file: http://graftons.juno.is/js/lib/jquery/noconflict.junominify.js

* magento site will load the minified one if the module is enable (config) and the minified file exist.
* by default, it scan for css and js file under skin and js folder, recusive.

* It does not happen immediately after installing. It will be trigger nightly at 0AM by magento cron

contact me for any support: hieu@junowebdesign.com
