'use strict';

const https = require('https');

exports.handler = function (request, context) {
    if (request.directive.header.namespace === 'Alexa.Discovery' && request.directive.header.name === 'Discover') {
        log("Discover request", JSON.stringify(request));
        handleDiscovery(request, context);
    }
    else if (request.directive.header.namespace === 'Alexa.PowerController') {
        if (request.directive.header.name === 'TurnOn' || request.directive.header.name === 'TurnOff') {
            log("TurnOn or TurnOff Request", JSON.stringify(request));
            handlePowerControl(request, context);
        }
    }
    else if (request.directive.header.namespace === 'Alexa') {
        if (request.directive.header.name === 'ReportState') {
            handleReportState(request, context);
        }
    }
    else {
        handleError(request, context);
    }

    function handleDiscovery(request, context) {
        const fs = require('fs');
        let JSONFileName = 'endpoints.json';
        log("Reading Endpoints from ", JSONFileName);
        let payload = JSON.parse(fs.readFileSync(JSONFileName));
        let header = request.directive.header;

        header.name = "Discover.Response";
        log("Discovery Response: ", JSON.stringify({ header: header, payload: payload }));
        context.succeed({ event: { header: header, payload: payload } });
    }

    function log(message, data) {
        console.log("DEBUG: " +
            message + " DATA: " +
            data);
    }

    function handlePowerControl(request, context) {
        var requestMethod = request.directive.header.name;
        var powerResult = "OFF";

        log("requestMethod", requestMethod);

        if (requestMethod === "TurnOn") {
            var uri = request.directive.endpoint.cookie.key1;
            log("WOL on route: ", uri);
            https.get(uri, (response) => {
                var statusCode = response.statusCode;
                if (statusCode == 200) {
                    log("WOL broadcast succesfull", statusCode);
                    context.succeed(createResponse(request, powerResult, "Response"));
                }
                else {
                    log("WOL broadcast failed", statusCode)
                    handleError(request, context);
                }
            }).on("error", (err) => {
                log("error while trying to broadast", err.message)
                handleError(request, context);
            });
        }
        else if (requestMethod === "TurnOff") {
            var uri = request.directive.endpoint.cookie.key2;
            checkIfDeviceIsRunning(uri).then(function (powerResult) {
                    log("TurnOff has checked status of " +
                        request.directive.endpoint.endpointId +
                        "has status ", powerResult);
                    context.succeed(createResponse(request, powerResult, "Response"));
                },
                function (err) {
                    log("handlePowerControlError", err.message);
                    handleError(request, context);
                })
        }
    }

    function handleReportState(request, context) {
        var uri = request.directive.endpoint.cookie.key2;
        log("WOL on route", uri);
        checkIfDeviceIsRunning(uri).then(function (powerResult) {
                console.log(request.directive.endpoint.endpointId);
                log(request.directive.endpoint.endpointId + " has status", powerResult)
                context.succeed(createResponse(request, powerResult, "StateReport"));
            },
            function (err) {
                log("handleReportStateError", err.message);
                handleError(request, context);
            })
    }

    function handleError(request, context) {
        context.succeed(createResponse(request, "OFF", "ErrorResponse"));
    }

    function createResponse(request, powerResult, responseHeaderName, eventPayload) {
        if (!eventPayload) eventPayload = {};
        var responseHeader = request.directive.header;
        responseHeader.namespace = "Alexa";
        responseHeader.messageId = responseHeader.messageId + "-R";
        responseHeader.name = responseHeaderName;

        var requestEndpointId = request.directive.endpoint.endpointId;
        var requestToken = request.directive.endpoint.scope.token;

        var d = new Date();
        var isoD = d.toISOString();
        var contextResult = {
            "properties": [{
                "namespace": "Alexa.PowerController",
                "name": "powerState",
                "value": powerResult,
                "timeOfSample": isoD,
                "uncertaintyInMilliseconds": 50
            }]
        };

        var response = {
            context: contextResult,
            event: {
                header: responseHeader,
                endpoint: {
                    scope: {
                        type: "BearerToken",
                        token: requestToken
                    },
                    "endpointId": requestEndpointId
                },
                payload: eventPayload
            }
        };
        log("Builded response: ", JSON.stringify(response));

        return response;
    };

    function checkIfDeviceIsRunning(uri) {
        return new Promise(function (resolve, reject) {
            https.get(uri, (response) => {
                var body = '';
                response.on('data', function (chunk) {
                    body += chunk;
                }).on('end', function () {
                    resolve(uri.includes(body) ? "ON" : "OFF");
                })
            }).on("error", (err) => {
                reject(Error(err.message));
            })
        });
    }
};

