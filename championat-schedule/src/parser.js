'use strict';
var page = require('webpage').create(),
fs = require('fs');

if (fs.exists('output.json')) {
    fs.remove('output.json');
}
phantom.injectJs('data.js');

var log = "[LOG ------->] ";
var Map ='';

var output = [];

page.settings.loadImages = false;
page.settings.resourceTimeout = 20000;
page.settings.userAgent = 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.71 Safari/537.36';



var recursiveRun = function(i, max, array) {

    if (i === max) {
        console.log(log + 'The End');
        var arr_all = JSON.stringify(output);
        fs.write('output.json', arr_all, 'a');
        phantom.exit();
        return;
    }

    page.open(array[i].link, function (status) {

      if (status !== "success") {
            console.log(log + "Error, can't access - " + array[i].link);
            recursiveRun(i + 1, max, array);
        } else {

            setTimeout(function() {
                var outputJson = page.evaluate(function() {
                    var arr = [];
                    $('tr[data-played=0]').each(function () {
                        $(this).find('.stat-results__link').remove();
                        $(this).find('.stat-results__fav _order_1').remove();
                        $(this).find('.stat-results__tour-num').remove();
                        $(this).find('.stat-results__title-date').remove();

                        $(this).find('.stat-results__count').remove();

                            arr.push({
                                'date': $(this).find('.stat-results__date-time').text().replace(/\s+/g, ' '),
                                'team1': $(this).find('.stat-results__title .stat-results__title-team:first-child').text().replace(/\s+/g, ' '),
                                'team2': $(this).find('.stat-results__title .stat-results__title-team:last-child').text().replace(/\s+/g, ' '),
                                'sport': $('.tabs__item._selected').filter(':first').text().replace(/\s+/g, ' '),
                                'tournament': $('.tournament-header__title-name').text().replace(/\s+/g, ' '),
                            });

                    });
                    return arr;
                });

                output = outputJson.reduceRight( function(coll,item){
                    coll.unshift( item );
                    return coll;
                }, output );

                    recursiveRun(i + 1, max, array);

                console.log(log + "running next, index -> " + i);
                console.log(log + "======================================= ");
            }, 2000);
        }
    });
};

recursiveRun(0, array.length, array);


