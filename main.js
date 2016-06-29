'use strict';


var apiai = require('apiai');

var app = apiai("3345970f25e64eaf9657ba5494ce0595");




var request = app.textRequest('hi');

request.on('response', function(response) {
    console.log(response);
});

request.on('error', function(error) {
    console.log(error);
});

request.end()
