/**
 * View refactorized
 * Show information about licenses and quota of the Customer
 * button "Control Panel" open a new window to Mozy login
 * link "Hide/Show group tab" hides or show the Group tab in Mozy tab
 * @parent mozyProAccount.general.html
 * @param {aps/load} load
 * @param {dijit/registry} registry // Handles the button and link click event
 * @param {aps/ResourceStore} Store // Used for updating mozyAccount resource data
 * @param {aps/xhr} xhr // Retrieves Globals resource and Client URL
 * @param {aps/Message} Message // Display error messages
 */
require(["aps/load", "dijit/registry", "aps/ResourceStore", "aps/xhr", "aps/Message", "dojo/when", "dojo/Deferred", "aps/Memory", "aps/ready!"],
        function (load, registry, Store, xhr, Message, when, Deferred, Memory) {
            "use strict";

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
             * Returns aps/gauge control, filled with parameter data
             * @param {JSON} stringArgs // Strings for the legend
             * @param {Number} currentValue
             * @param {Number} maxValue
             * @returns {Array}
             */
            var getGauge = function (stringArgs, currentValue, maxValue) {
                var gauge = ["aps/Gauge", {
                        legend: stringArgs.label + ": " + currentValue + " / " + maxValue + " " + stringArgs.type,
                        minimum: 0,
                        maximum: maxValue,
                        value: currentValue
                    }];
                var classesMap = {};
                    classesMap[maxValue] = "warn";
                    gauge[1].classesMap = classesMap;
                return gauge;
            };

            /**
             * Round a value in Bytes to a value in Gigas with two decimals
             * @param {Number} valueInBytes
             * @returns {Number}
             */
            var toGigas = function (valueInBytes) {
                for (var i = 0; i < 3; i++) {
                    valueInBytes = valueInBytes/1024;
                }
                valueInBytes = Math.round(valueInBytes*100);
                return valueInBytes/100;
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
                    default: param += aps.context.locale.substring(0,2);
                }
                return param;
            };

            // Contexts
            var model = aps.context.vars.mozyProAccount;

            if(model.partnerId === null)
            {
                var GetStore = new Store({
		target : "/aps/2/resources/?implementing(http://parallels.com/aps/types/pa/admin-user/1.0)"
                });
                var getXhr = function(){
                var deferred = new Deferred();
                when(GetStore.query(), function(GetStore){
                        var item = null;
                        var json = JSON.parse(item);
                        var result = [];
                        var container = { };
                        // Extract to JSON
                        item = JSON.stringify(GetStore);
                        json = JSON.parse(item);
                        result = [];
                        container = [];
                        for (var i = 0; i < json.length; i++){
                                // Put Email into Array
                                result.push(json[i].email);
                                var jsonString = {"id":json[i].aps.id,"email":json[i].email,"displayname":json[i].displayName};
                                container.push(jsonString);
                        }
                        var resultA = JSON.stringify(result);
                        // Call to TENANT to veryfy emails
                        when(xhr("/aps/2/resources/" + model.aps.id + "/checkAvailabilityEmail", {method: "POST", handleAs: "text", query: {json: resultA}}),
                            function (res) {
                                var result = [];
                                if (res !== "500" && res !== 500 && res !== "513" && res !== 513){
                                    var xRes = JSON.parse(res);
                                    for (var i = 0; i < xRes.length; i++){
                                        for (var j = 0; j < container.length; j++){
                                            var tmp = JSON.stringify(container[j]);
                                            var index = tmp.indexOf(xRes[i]);
                                            if (index > -1) {
                                                    result.push(container[j]);
                                            }
                                        }
                                    }
                                }
                                deferred.resolve(result);
                            }
                        );
                }, function(error){
                        deferred.cancel(error);
                });
                return deferred.promise;
            };

            var handleEvents = function() {
                    var grid = registry.byId("mozy_grid");

                    var rowCount = registry.byId("mozy_grid").get("_totalItemCount");
                    if (rowCount < 10) {
                            grid.set("showPaging", false);
                    }
                    if (rowCount === 0){
                            registry.byId("assignEmail").set("visible",false);
                    }

            };

            when(getXhr(), function(str) {
                    var storeGrid = new Memory({data: str, idProperty: "id"});
                    var pageContainer = ["aps/PageContainer", {
                        id: "page"
                        },
                        [
                            ["aps/FieldSet", {
                                    escapeHTML: false,
                                    description: "<b>"+_("Error:")+"</b> "+_("The staff member's email used to create the subscription is already in use.")+" <br/><br/>"+_("To solve this error and create the subscription and Admin User correctly, you must:")+"<ol style='list-style-type:decimal;list-style-position:inside;margin:auto;padding-left:40px;'><li>"+_("Create a staff member (Account > Admins > Add New Admin) with an email that has not been used before.")+"</li><li>"+_("In the APS package tab, select this new staff member and associate it with the Service.")+"</li></ol>"
                            }],
                            ["aps/Grid", {
                                        id: "mozy_grid",
                                        selectionMode: "single",
                                        columns: [{
                                                field: "displayname",
                                                name: _("Name")
                                        }, {
                                                field: "email",
                                                name: _("Email Admin User")
                                        }],
                                        store: storeGrid
                                    }
                            ],
                            ["aps/Container", {
                                    showLabels: false,
                                    cols: 2,
                                    "class": "twocolums"
                            },[
                                ["aps/FieldSet", {
                                        showLabels: false
                                },[
                                    ["aps/Button",{
                                            id: "assignEmail",
                                            title: _('Assign Staff Member to the Service'),
                                            onClick: function(){
                                                var page = registry.byId("page");
                                                var grid = registry.byId("mozy_grid");
                                                var asemail = registry.byId("assignEmail");
                                                var sel = grid.get("selectionArray");
                                                var counter = sel.length;
                                                var guid ="";
                                                var emailA = null;
                                                // if counter <1 exit because not have selection
                                                if (counter<1){
                                                        page.get("messageList").removeAll();
                                                        page.get("messageList").addChild(new Message({
                                                                description: _("You have not selected any Staff Members, please select one."),
                                                                type: "error"
                                                        }));
                                                        aps.apsc.cancelProcessing();
                                                        asemail.cancel();
                                                        return;
                                                }
                                                var idSelectItem = registry.byId("mozy_grid").get("selectionArray")[0];
                                                var dataStoreGrid = storeGrid.data;
                                                dataStoreGrid.forEach(function(items){

                                                        // The original way is "items.aps.id"
                                                        if(items.id === idSelectItem){
                                                                emailA = [items.email];
                                                                guid = idSelectItem;
                                                        }
                                                });
                                                // convert array to JSON
                                                emailA = JSON.stringify(emailA);
                                                when(xhr("/aps/2/resources/" + model.aps.id + "/checkAvailabilityEmail", {method: "POST", handleAs: "text", query: {json: emailA}}),
                                                    function (res) {
                                                            if (res === "500" || res === 500){
                                                                    page.get("messageList").removeAll();
                                                                    page.get("messageList").addChild(new Message({
                                                                            description: _("Connection with Mozy Endpoint has failed."),
                                                                            type: "error"
                                                                    }));
                                                                    aps.apsc.cancelProcessing();
                                                                    asemail.cancel();
                                                                    return;
                                                            }
                                                            else if (res === "513" || res === 513){
                                                                    page.get("messageList").removeAll();
                                                                    page.get("messageList").addChild(new Message({
                                                                            description: _("This Staff Member email already exists at Mozy. Please, choose another one."),
                                                                            type: "error"
                                                                    }));
                                                                    aps.apsc.cancelProcessing();
                                                                    asemail.cancel();
                                                                    return;
                                                            }
                                                            else{
                                                                page.get("messageList").removeAll();
                                                                page.get("messageList").addChild(new Message({
                                                                        description: _("Provisioning process might take a while."),
                                                                        type: "progress"
                                                                }));
                                                                when(xhr("/aps/2/resources/" + model.aps.id + "/createPartner", {method: "POST", handleAs: "text", query: {staffGUID: guid}}),
                                                                    function () {
                                                                        page.get("messageList").removeAll();
                                                                        page.get("messageList").addChild(new Message({
                                                                                description: _("Create admin user ok."),
                                                                                type: "update"
                                                                        }));
                                                                        asemail.cancel();
                                                                        parent.document.getElementById("refresh_action").onclick();
                                                                        return;
                                                                }
                                                            );
                                                        }
                                                    },
                                                    function (error) {
                                                    page.get("messageList").removeAll();
                                                    page.get("messageList").addChild(new Message({
                                                            description: _(error),
                                                            type: "error"
                                                    }));
                                                    aps.apsc.cancelProcessing();
                                                    asemail.cancel();
                                                    return;
                                                }
                                            );
                                        }
                                    }]]
                                ]]
                            ]
                        ]];
                    when(load(pageContainer), handleEvents);
		});
            }
            else
            {
                var brandingLogo = aps.context.vars.Branding.logoUrl || "./images/mozylogo.png";

                // First Container - Hide or Show Groups tab
                var contShowGroups = ["aps/Container", { visible: (model.accountType === "C") ? true : false },
                    [["aps/ToolbarButton", {
                            id: "showGroups",
                            title: (model.groupview ? _("Hide") : _("Show")) + _(" Groups tab"),
                            iconClass: "iconStart",
                            style: "right: 0px !important;position: absolute !important;overflow: hidden"}
                    ]] ];


                /**
                 * Creates a sync button or an Output with information, depending on account type and sync status
                 * @param {type} model
                 * @returns {aps/Button|aps/Output}
                 */
                var getSyncOption = function (model) {
                    var btnTitle, showButton = true;
                    var syncAvailable = model.syncAvailable || {limit: 0, usage:0};
                    var resellerSync = model.resellerSync || 0;
                    // Reseller always see a button
                    if (model.accountType === "R") {
                        btnTitle = (resellerSync > 0) ? _("Disable Sync"): _("Enable Sync");
                    } else {
                        btnTitle = (syncAvailable.usage > 0) ? _("Disable Sync"): _("Enable Sync");
                        // If there is not synchronization in subscription
                        showButton = (syncAvailable.limit === 1) ? true: false;
                    }
                    // Returns a widget depending on sync availability
                    var responseWidget;
                    if (showButton) {
                        responseWidget = ["aps/Button", { id:"syncWidget", title: btnTitle }];
                    } else {
                        responseWidget = ["aps/Output", { id:"syncWidget", style:"cursor:help;text-decoration:underline", label: _("Sync Service"), innerHTML: _("Not available") }];
                    }
                    return responseWidget;
                };

                // Sync service instructions options
                var syncService, showInstructions = false;
                if (model.syncAvailable) {
                    if (model.syncAvailable.limit && model.syncAvailable.limit > 0 && model.accountType !== "R") {
                        showInstructions = true;
                        syncService = (model.syncAvailable.usage === 1) ? _("Enabled") : _("Disabled");
                    }
                }

                // Second Container - Account Data
                var contCompanyData = ["aps/Container", {cols: 2},
                                        [
                                            ["aps/FieldSet", {style:"width:110%" ,title: _("Account Data")},
                                                [
                                                    ["aps/Output", {label: _("Company name:"), innerHTML: model.companyName}],
                                                    ["aps/Output", {label: _("User Full Name:"), innerHTML: model.userFullName}],
                                                    ["aps/Output", {label: _("User name:"), innerHTML: model.userName}],
                                                    ["aps/Button", {id: "accessUrl", title: _("Control Panel"), visible: (model.accountType === "CR") ? false : true}],
                                                    ["aps/Button", {id: "refreshResources", title: _("Refresh Resources")}],
                                                    ["aps/Output", {label: _("Sync Service") + " " + syncService, visible:showInstructions, innerHTML: _("Once the Sync Service has been enabled, each user will have to be enabled separately.") }],
                                                    getSyncOption(model)
                                                ]
                                            ],
                                            ["aps/FieldSet", [["aps/Output", {innerHTML: "<img src='" + brandingLogo + "' alt='' style='margin: 30px 0px 20px 0px' />"}]]]
                                        ]
                                    ];

                // Third Container - Licenses and Quota information
                var arrFsLicense = [];
                var arrAccountLicenses = JSON.parse(model.accountLicenses);
                    // For each account license type
                for (var i = 0; i < arrAccountLicenses.length; i++) {
                    var account = arrAccountLicenses[i];
                    // Check if the account has licenses
                    if (account.licenses > 0) {
                        var fieldSetBackup = ["aps/FieldSet", {showLabels: false},
                            [
                                getGauge({label: _("Assigned"), type: _("Licenses")}, account.licenses_reserved, account.licenses),
                                getGauge({label: _("Activated"), type: _("Licenses")}, account.licenses_used, account.licenses)
                            ]
                        ];

                        var fieldSetQuota = ["aps/FieldSet", {showLabels: false},
                            [
                                getGauge({label: _("Used"), type: _("GB")}, toGigas(account.quota_used_bytes), account.quota),
                                getGauge({label: _("Assigned"), type: _("GB")}, account.quota_distributed, account.quota)
                            ]
                        ];
                        // Fix: i18n catch fieldset title string
                        var fieldSetTitle = account.license_type + _(" Backup");
                        if (account.license_type === "Desktop") {
                            fieldSetTitle = _("Desktop Backup");
                        } else if (account.license_type === "Server") {
                            fieldSetTitle = _("Server Backup");
                        }
                        arrFsLicense.push(["aps/FieldSet", {title: fieldSetTitle, showLabels: false}, [["aps/Container", {cols: 2}, [fieldSetBackup, fieldSetQuota]]]]);
                    }
                }
                var contLicense = ["aps/Container", arrFsLicense];

                // Check if partner canceled his subscription
                var subscriptionStatus = model.partnerStatus || "enabled";

                // Tooltip displayed with no sync subscription
                var ttText = _("This service lets you schedule cloud backups for your desktop, laptop or server computer and offers automatic file protection. It saves you time and money so you can focus on other important things, like growing your business. Install the service software onto every computer you need to protect and relax knowing that this simple, secure and automatic service is protecting your vital digital assets.");
                if (model.syncAvailable) {
                    if (model.syncAvailable.limit === 1) {
                        ttText += "<br/><br/>";
                        ttText += _("Not only will your most valuable files be protected, but desktop and laptop users can also synchronize their files across multiple computers. File sync makes your old file-transfer methods obsolete. No need to use a USB stick to move your files and gone are the days of emailing files to yourself. You simply place a file in your local sync folder, and it’s immediately available from your other devices. Devices linked to your sync folder will always have your up-to-date files without ever needing to be refreshed.");
                    }
                }
                var tooltipSync = ["aps/Tooltip", { connectId: "syncWidget", label: ttText } ];

                // Prepare the content of the page
                var arrayContent = [contShowGroups, contCompanyData, contLicense, tooltipSync];

                // Page Container - Adding Containers to the page if the subscripton is not disabled
                var pageContainer = ["aps/PageContainer", {id: "page"}, (subscriptionStatus === "disabled") ? [] : arrayContent];

                // on Page Display - Code Logic
                load(pageContainer).then(function () {
                    // Show a message if the partner has disabled the subscription
                    if (subscriptionStatus === "disabled") displayMessage(_("The partner has disabled the subscription"), "error");

                    // Control Panel button - Click
                    registry.byId("accessUrl").on("click", function () {

                        // XHR to obtain Globals (mozyPro) resource
                        xhr("/aps/2/resources/" + aps.context.vars.mozyProAccount.aps.id + "/mozyPro").then(function (globalsResource) {

                            // XHR to obtain Client Url
                            xhr("/aps/2/resources/" + aps.context.vars.mozyProAccount.aps.id + "/getClientUrl",
                                    {method: "GET", query: "title=" + model.userName + "&title2=" + globalsResource[0].aps.id, handleAs: "text"}
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
                            // Error in retrieving Globals (mozyPro) resource
                        }).otherwise(function (xhrGlobalsError) {
                            console.log("Error retrieving Mozy resources");
                            console.dir(xhrGlobalsError);
                            displayMessage(_("Can't retrieve Mozy resource"), "error");
                            aps.apsc.cancelProcessing();
                        });

                    });

                    // Refresh Resources button - Click
                    registry.byId("refreshResources").on("click", function () {
                        xhr("/aps/2/resources/" + model.aps.id + "/manualRetrieve", {method: "GET"}).then(function () {
                            // Refresh the page
                            parent.document.getElementById("refresh_action").click();
                        }).otherwise(function (errorCustomMethod) {
                            console.dir(errorCustomMethod);
                            displayMessage(errorCustomMethod, "error");
                            registry.byId("refreshResources").cancel();
                        });
                    });

                    // Sync Switcher button - Click
                    registry.byId("syncWidget").on("click", function () {
                        // Sync Widget can be an apsOutput if the service is not available
                        if (registry.byId("syncWidget").get("baseClass") === "apsButton") {

                            if(model.resellerSync === 1 || model.syncAvailable.usage === 1){
                                var mess = _("Are you sure you want to remove Sync for this subscription? This will cause the data synced by all users within this subscription to be deleted!");
                                if(confirm(mess))
                                {
                                    // Request to the server custom method
                                    xhr("/aps/2/resources/" + model.aps.id + "/setEnableDisableSync", {method: 'POST', query: "id=" + model.accountType}).then(function () {
                                        // Refresh the page
                                        parent.document.getElementById("refresh_action").click();
                                    }).otherwise(function (errorSyncSwitch) {
                                        displayMessage(errorSyncSwitch, "error");
                                        registry.byId("syncSwitcher").cancel();
                                    });
                                }
                                else{
                                    this.cancel();
                                }
                            }
                            else{
                                // Request to the server custom method
                                xhr("/aps/2/resources/" + model.aps.id + "/setEnableDisableSync", {method: 'POST', query: "id=" + model.accountType}).then(function () {
                                    // Refresh the page
                                    parent.document.getElementById("refresh_action").click();
                                }).otherwise(function (errorSyncSwitch) {
                                    displayMessage(errorSyncSwitch, "error");
                                    registry.byId("syncSwitcher").cancel();
                                });
                            }
                        }
                    });

                    // Hide/show groups tab - Click
                    registry.byId("showGroups").on("click", function () {
                        // Prepare the field to be updated
                        var updateModel = {"aps": {"id": model.aps.id, "type": model.aps.type}, "groupview": (model.groupview) ? false : true};
                        // Store to the resource
                        var accountStore = new Store({
                            apsType: model.aps.type,
                            target: "/aps/2/resources/"
                        });
                        // Update property
                        accountStore.put(updateModel).then(function () {
                            parent.document.getElementById("refresh_action").click();
                            // Error in PUT to resource
                        }).otherwise(function (storeError) {
                            console.log("Error updating tenant resource");
                            console.dir(storeError);
                            displayMessage(_("Cannot update Groups tab status"), "error");
                            registry.byId("showGroups").cancel();
                        });
                    });
                });
            }
        });
