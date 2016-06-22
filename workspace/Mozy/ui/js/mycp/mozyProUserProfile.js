/**
 * View refactorized
 * Display information about the user credentials and licenses 
 * @parent mozyProAccountUserProfile.html
 * @param {APS} load
 * @param {dojo/store/Memory} Memory // Contains data from xhr request
 * @param {aps/xhr} xhr // Calls custom method in resource
 */
require(["aps/load", "dijit/registry", "dojo/store/Memory", "aps/Message","aps/xhr", "aps/Output", "aps/ready!"],
        function (load,registry, Memory, Message,xhr, Output) {
            "use strict";
            
            // Default Backup Text - For translationing reasons is not included in a external file
            var backupDefaultText = _("<p>Welcome to Mozy Backup! If you haven't activated your Mozy Backup product key yet, make sure you follow the steps provided below to download and activate Mozy Backup on your computer :</p>" +
                        "<ol>" +
                            "<li>Download the Backup client for Windows or Mac using the link you received in the email</li>" +
                            "<li>Install the Mozy Backup : </li>"+
                            "<ol>" +
                                "<li>For Windows, double-click the Mozy Backup executable from the Downloads folder and follow the instructions in the in Setup Wizard</li>" +
                                "<li>For Mac, double-click the Mozy Backup dmg file from the Downloads folder and open the Setup executable then follow the instructions in the in Setup Wizard</li>" +
                            "</ol>" +
                            "<li>Enter the product key you wish to activate for this device as well as your username email address - The product key is ~20 characters alphanumeric string that may be found at the top of this page or in the initial email received from your administrator</li>"+
                            "<li>Follow the Mozy Backup Setup Wizards until the end and click Finish to start your initial backup - The wizard will guide you through selecting files and folders you wish to back-up, setting the back up schedules and other settings</li>"+
                        "</ol><br/>" +
                        "<p>Congratulations! You Mozy Backup is now fully setup so sit back and relax while Mozy Backup is automatically protecting you most critical data.</p>");
            // Default Sync Text - For translationing reasons is not included in a external file
            var syncDefaultText = _("<p>Mozy Sync comes in addition to your backup and offers personal sync capabilities to make sure you always have access to your most import files across the devices you have linked to your account and from anywhere. You can follow the few steps provided below to get started with Mozy Sync : </p>" +
                        "<ol>" +
                            "<li>Download the Sync client for Windows or Mac using the link you received in the email</li>" +
                            "<li>Install the Mozy Sync client : (again any action to trigger this we can readily point to) </li>"+
                            "<ol>" +
                                "<li>For Windows, double-click the Mozy Sync executable from the Downloads folder and follow the instructions in the in Setup Wizard</li>" +
                                "<li>For Mac, double-click the Mozy Sync dmg file from the Downloads folder and drag & drop the Mozy Sync app in the Applications folder then open the Mozy Sync app from the Applications folder</li>" +
                            "</ol>" +
                            "<li>Enter your username email address and the password you define during the activation of your Mozy Backup product key activation account and click Link Computer</li>"+
                            "<li>Follow the Mozy Sync Setup Wizards until the end and click Finish to start Syncing your data</li>"+
                        "</ol><br/>" +
                        "<p>That's it! You Mozy Sync is now setup, to start using it simply add some files to your Mozy Sync folder and they will automatically get sync'ed across all your devices you link to your account.</p>");
            
            /**
             * Returns aps/Output control, filled with parameter data
             * @param {string} label
             * @param {string} value
             * @returns {Array}
             */
            var getOutput = function (label, value) {
                var output =  ["aps/Output", {
                                    label: label,
                                    value: value
                              }];
                return output;
            };

            // Contexts
            var model = aps.context.vars.mozyProAccountUser;
            
            // XHR to getLicense custom method
            xhr("/aps/2/resources/" + aps.context.vars.mozyProAccountUser.aps.id + "/getLicenses").then(function (licenseData) {
                // XHR to get Branding customization
                xhr("/aps/2/resources/" + aps.context.vars.mozyProAccountUser.aps.id + "/getInstructions",{method: "GET",  handleAs: "text"}).then(function (dataInstructions) {
                    var dataObject = {};
                    try {
                        // Format server response as Object
                        dataObject = JSON.parse(JSON.parse(dataInstructions.replace(/\n/g, '')));
                    } catch (badReturn) {
                        console.log("badReturn", badReturn);
                    }

                /**
                 * Display messages in the page
                 * @param {string} message
                 * @param {string} msgType
                 * @returns {void}
                 */
                var displayMessage = function (message, msgType) {
                    var getMessageList = registry.byId("page").get("messageList");
                    getMessageList.removeAll();
                    getMessageList.addChild(new Message({description: message, type: msgType}));
                };
                /**
                 * Returns a string parameter to send with the url
                 * @returns {String}
                 */
                var getDialect = function () {
                    var param = "&dialect=";
                    switch(aps.context.locale) {
                        case "pt_BR": param += "pt-BR"; break;
                        case "en_US": param += "en"; break;
                        case "en_GB": param += "en-GB"; break;
                        case "it": param += "it"; break;
                        case "nl": param += "nl"; break;
                        case "de": param += "de"; break;
                        case "fr": param += "fr"; break;
                        case "es": param += "es-ES"; break;
                        case "ja": param += "ja-JP"; break;
                    }
                    return param;
                };

                // Prepare data for the grid
                var licensesMemory = new Memory({data: licenseData});
                // Is sync Available
                var enableSync = model.enableSync || false;
                // Prepare text for Backup Section. If no text was entered in Branding backup section, it will display default text
                var backupText = dataObject.backup || backupDefaultText;
                // Customization from Branding for Backup
                var backupIntructionsContainer = ["aps/Container", {id:"contbackup" ,title: _("Mozy Backup Instructions")}, [["aps/Output", {innerHTML:backupText}]]];
                // Prepare text for Sync Section. If no text was entered in Branding backup section, it will display default text
                var syncText = dataObject.sync || syncDefaultText;
                // Customization from Branding for Sync (only if Sync is activated)
                var syncIntructionsContainer = ["aps/Container",
                    {
                        id: "contsync",
                        title: _("Mozy Sync Instructions"),
                        visible: (enableSync) ? true : false

                    }, [["aps/Output", {innerHTML: syncText}]]];
                //add button to get url to access portal.
                var urlAccess = ["aps/Button", {id: "accessUrl", title: _("Login"), style: "float:right"}];

                // First container - User credentials
                var fieldSet = ["aps/FieldSet", {title: _("User credentials"), style: "clear:right"},
                    [
                        getOutput(_("Username"), model.displayName),
                        getOutput(_("Login"), model.login)
                    ]
                ];

                /**
                 * Round a value in Bytes to a value in Gigas with two decimals
                 * @param {Number} valueInBytes
                 * @returns {Number}
                 */
                var toGigas = function (valueInBytes) {
                    for (var i = 0; i < 3; i++) {
                        valueInBytes = valueInBytes / 1024;
                    }
                    valueInBytes = Math.round(valueInBytes * 100);
                    return valueInBytes / 100;
                };

                /**
                 * It returns the time elapsed between the date passed by a parameter and the current date
                 * @param {string} date
                 * @returns {object}
                 */
                var timeTilNow = function (date) {
                    // Date Object with the last update
                    var lastDate = new Date(date);
                    // Date object with the actual time
                    var now = new Date();
                    // Difference between now and last update in seconds
                    var diff = (now.getTime() - lastDate.getTime()) / 1000;
                    // Steps to compare with the difference. In seconds: 1 minute, 1 hour, 1 day, 1 month, 1 year
                    var arrMagnitude = [60, 60 * 60, 60 * 60 * 24, 60 * 60 * 24 * 30, 60 * 60 * 24 * 30 * 12];
                    // Compare the difference with each order of magnitude
                    var response;
                    if (diff < arrMagnitude[0]) {
                        response = {diff: Math.floor(diff), message: _("${value} second(s) ago")}; } else
                    if (diff < arrMagnitude[1]) {
                        response = {diff: Math.floor(diff / arrMagnitude[0]), message: _("${value} minute(s) ago")}; } else
                    if (diff < arrMagnitude[2]) {
                        response = {diff: Math.floor(diff / arrMagnitude[1]), message: _("${value} hour(s) ago")}; } else
                    if (diff < arrMagnitude[3]) {
                        response = {diff: Math.floor(diff / arrMagnitude[2]), message: _("${value} day(s) ago")}; } else
                    if (diff < arrMagnitude[4]) {
                        response = {diff: Math.floor(diff / arrMagnitude[3]), message: _("${value} month(s) ago")}; }
                    else {
                        response = {diff: 1, message: _("more than a year ago")};
                    }
                    // Return formated the time until now and the message to show
                    return response;
                };

                /**
                 * Render Cell (licenses grid)
                 * Displays quota information in relation used / total
                 * @param {object} row license model
                 * @returns {String}
                 */
                var rcStorage = function (row) {
                    var usedQuota = row.quota_used_bytes;
                    var cell = row.quota + " GB";
                    if (usedQuota) {
                        cell = toGigas(usedQuota) + " / " + row.quota + " GB";
                    }
                    return cell;
                };
                /**
                 * Render Cell (licenses grid)
                 * Displays information about last backup date
                 * @info {format} date "2015-04-03T00:00:00-06:00"
                 * @param {object} row license model
                 * @returns {aps/Output}
                 */
                var rcLastUpdate = function (row) {
                    var lastUpdate = row.last_backup_at;
                    var cell = "";
                    if (lastUpdate) {
                        var jsonDate = timeTilNow(lastUpdate);
                        cell = new Output({value: jsonDate.diff, innerHTML: jsonDate.message});
                    }
                    return cell;
                };
               
                // Second container - Grid with data retrieved from XHR
                var grid = ["aps/Grid", {
                        id: "userGrid",
                        columns: [
                            {name: _("keyString"), field: "keyString"},
                            {name: _("License Type"), field: "licenseType"},
                            {name: _("Computer"), "field": "alias"},
                            {name: _("Storage"), "field": "quota_used_bytes", renderCell:rcStorage},
                            {name: _("Last Update"), "field": "last_backup_at", renderCell:rcLastUpdate}
                        ],
                        store: licensesMemory
                    }];
                var gridContainer = ["aps/Container", {title: _("User licenses")}, [ grid ]];

                    /**
                     * Render Cell (sync grid)
                     * Displays sync information in relation used / total
                     * @param {object} row user model
                     * @returns {String}
                     */
                    var rcSyncStorage = function (row) {
                        var response = row.syncQuotaUsedMozy + " / " + row.syncQuota + " GB";
                        return response;
                    };

                    /**
                     * Render Cell (sync grid)
                     * Displays information about last backup date
                     * @info {format} date "2015-04-03T00:00:00-06:00"
                     * @param {object} row user model
                     * @returns {aps/Output}
                     */
                    var rcSyncLastUpdate = function (row) {
                        var lastUpdate = row.syncLastUpdate;
                        var cell = "";
                        if (lastUpdate) {
                            var date = timeTilNow(lastUpdate);
                            cell = new Output({value: date.diff, innerHTML: date.message});
                        }
                        return cell;
                    };

                    // Third container - Grid with sync quota from model
                    var contUserSync = ["aps/Container",
                        {
                            id: "contSync_" + model.aps.id,
                            title: _("Sync"),
                            visible: (enableSync) ? true : false
                        },
                        [
                            ["aps/Grid", {
                                    id: "gridSync_" + model.aps.id,
                                    showPaging: false,
                                    columns: [
                                        {"name": _("Storage"), renderCell: rcSyncStorage},
                                        {"name": _("Last Update"), renderCell: rcSyncLastUpdate}
                                    ],
                                    store: new Memory({data: [model], idProperty: "aps.id"})
                                }
                            ]
                        ]];

                // Page Container - Adding containers to the page
                var pageContainer = ["aps/PageContainer", {id: "page"}, [urlAccess , fieldSet, gridContainer,contUserSync, backupIntructionsContainer,syncIntructionsContainer]];

                // on Page Display - Code Logic
                load(pageContainer).then(function () {
                    // Control Panel button - Click
                    registry.byId("accessUrl").on("click", function () {
                            // XHR to obtain Client Url
                            xhr("/aps/2/resources/" + aps.context.vars.mozyProAccountUser.aps.id + "/getUserUrl",
                                {method: "GET", query: "title=" + model.login + "&title2=", handleAs: "text"}
                            ).then(function (clientUrl) {
                                    var wellformedUrl = clientUrl.replace(/\s{1}|\"|\\/g, "");
                                    wellformedUrl += getDialect();
                                    // Opens URL in a new window
                                    window.open(wellformedUrl, "_blanck");
                                    // Stops the button displaying "please wait" message
                                    registry.byId("accessUrl").cancel();

                                    // Error in retrieving Clien Url
                                }).otherwise(function (xhrClientUrlError) {
                                    console.log("Error retrieving client url");
                                    console.dir(xhrClientUrlError);
                                    displayMessage(_("Can't retrieve Mozy login URL"), "error");
                                    aps.apsc.cancelProcessing();
                                });

                    });

                });


                }).otherwise(function(error) {
                    console.log(error);
                });

            });
           
        });
