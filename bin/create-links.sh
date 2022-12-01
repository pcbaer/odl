#!/bin/sh

rm -f public/css/bootstrap.css
ln -s ../../vendor/twbs/bootstrap/dist/css/bootstrap.css public/css/bootstrap.css
rm -f public/css/bootstrap.css.map
ln -s ../../vendor/twbs/bootstrap/dist/css/bootstrap.css.map public/css/bootstrap.css.map

rm -f public/js/bootstrap.bundle.min.js
ln -s ../../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js public/js/bootstrap.bundle.min.js
rm -f public/js/bootstrap.bundle.min.js.map
ln -s ../../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js.map public/js/bootstrap.bundle.min.js.map
rm -f public/js/chart.min.js
ln -s ../../vendor/nnnick/chartjs/dist/Chart.min.js public/js/chart.min.js
rm -f public/js/moment-with-locales.min.js
ln -s ../../vendor/moment/moment/min/moment-with-locales.min.js public/js/moment-with-locales.min.js

rm -rf components
