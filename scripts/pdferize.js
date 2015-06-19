
var page = require('webpage').create(),
    system = require('system'),
    input, output;

if (system.args.length < 3 || system.args.length > 3) {
    console.log('Usage: pdferize.js input.html output.pdf');
    phantom.exit(1);
} else {
    input = system.args[1];
    output = system.args[2];
    page.paperSize = {format: 'A4', orientation: 'portrait', margin: '1cm' };

    page.open(input, function (status) {
        if (status !== 'success') {
            console.log('Unable to load the address!');
            phantom.exit(1);
        } else {
            // hack job for phantomjs 2.0 until they fix it
            /*page.evaluate(function(zoom) {
                    document.getElementsByTagName('body')[0].style.zoom=zoom
             },0.53);*/
            window.setTimeout(function () {
                page.render(output);
                phantom.exit();
            }, 200);
        }
    });
}