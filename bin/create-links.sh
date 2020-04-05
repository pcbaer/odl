#!/bin/sh

rm -f public/css/bootstrap.css
ln -s ../../vendor/twbs/bootstrap/dist/css/bootstrap.css public/css/bootstrap.css
rm -f public/css/bootstrap.css.map
ln -s ../../vendor/twbs/bootstrap/dist/css/bootstrap.css.map public/css/bootstrap.css.map

rm -f public/js/chart.bundle.min.js
ln -s ../../vendor/nnnick/chartjs/dist/Chart.bundle.min.js public/js/chart.bundle.min.js
rm -f public/js/jquery.slim.min.js
ln -s ../../vendor/components/jquery/jquery.slim.min.js public/js/jquery.slim.min.js
rm -f public/js/jquery.slim.min.map
ln -s ../../vendor/components/jquery/jquery.slim.min.map public/js/jquery.slim.min.map
rm -f public/js/bootstrap.bundle.min.js
ln -s ../../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js public/js/bootstrap.bundle.min.js
rm -f public/js/bootstrap.bundle.min.js.map
ln -s ../../vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js.map public/js/bootstrap.bundle.min.js.map
