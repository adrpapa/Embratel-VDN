/**
 * View refactorized
 * Let the user introduce information of branding resource and edit that information
 * @parent mozyProConf.edit.html
 * @param {aps/load} load
 * @param {aps/ResourceStore} Store // Prepare a new resource to be created or an existing one to be updated
 * @param {dojox/mvc/getStateful} getStateful // Refresh information of the model on change
 * @param {dojox/mvc/at} at // Handle the onChange event
 * @param {dojox/mvc/getPlainValue} getPlainValue // Cleans context model
 * @param {dijit/registry} registry // Handles page to validation
 * @param {aps/Message} Message // Display error messages
 * @param {dojo/text!./json/mozyProConf.json} json_mozyProConf // base model of mozyProConf resource for creating new resources
 */
require(["aps/load", "aps/ResourceStore", "dojox/mvc/getStateful", "dojox/mvc/at", "dojox/mvc/getPlainValue", "dijit/registry", "aps/Message", "dojo/text!./json/mozyProConf.json", "aps/ready!"],
        function (load, Store, getStateful, at, getPlainValue, registry, Message, json_mozyProConf ) {
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
             * Returns aps/TextBox with data from the parameters
             * @param {string} labelString
             * @param {string} valueString
             * @returns {Array}
             */
            var getTextBox = function (labelString, valueString, required, hint) {
                var parameters = { label: labelString, value: valueString };
                // Add required property if needed
                if (required) parameters.required = true;
                if (hint) parameters.hint = hint;
                var textbox =  ["aps/TextBox", parameters ];
                return textbox;
            };
            
            // Contexts (for existing and new resources)
            var model = aps.context.vars.Branding || JSON.parse(json_mozyProConf);
            model = getStateful(model);
            
            // Prepare the store depending of the resource existence
            var storeTarget = "/aps/2/resources/";
            if (!aps.context.vars.Branding) {
                storeTarget += aps.context.vars.globals.aps.id + "/mozyProConf";
            }
            var store = new Store({
                            apsType: "http://www.mozy.com/mozyProAPS2/mozyProConf/1.1",
                            target: storeTarget
                        });

            // Default Backup Text to display
            if (!model.hasOwnProperty("backupHelp") || model.backupHelp === "") {
                model.backupHelp = _("<p>Welcome to Mozy Backup! If you haven't activated your Mozy Backup product key yet, make sure you follow the steps provided below to download and activate Mozy Backup on your computer :</p>" +
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
            }
            // Default Sync Text to display
            if (!model.hasOwnProperty("syncHelp") || model.syncHelp === "") {
                model.syncHelp = _("<p>Mozy Sync comes in addition to your backup and offers personal sync capabilities to make sure you always have access to your most import files across the devices you have linked to your account and from anywhere. You can follow the few steps provided below to get started with Mozy Sync : </p>" +
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
            }

            // First Container - Customization settings
            var fsCustom = ["aps/FieldSet", { title: "Customization" },
                                [
                                    getTextBox(_("Brand Tab Name"), at(model, "branded_tab_name"), true, _("The text entered will be displayed in the Subscription tab of the Customer's Control Panel.")),
                                    getTextBox(_("Logo URL"), at(model, "logoUrl"), false, "The logo will be displayed in the Subscription's Account tab of the Customer's Control Panel. If it is empty, a Mozy logo will be displayed instead."),
                                    ["aps/TextArea", { label: _("User's backup section text"), style: "height: 100px; width: 100%;", value: at(model, "backupHelp")} ],
                                    ["aps/TextArea", { label: _("User's sync section text"), style: "height: 100px; width: 100%;", value: at(model, "syncHelp"), hint: _("This information is displayed only if the user has Sync enabled")} ]
                                ]
                            ];

            // Second Container - API Credentials
            var fsAPI = ["aps/FieldSet", { title: "API Credentials", visible: aps.context.vars.globals.orderType },
                                [
                                    getTextBox("API Key", at(model, "api_key")),
                                    getTextBox("Root Partner ID", at(model, "root_partner_id")),
                                    getTextBox("Root Role ID", at(model, "root_role_id"))
                                ]
                            ];
                            
            // Page Container - Adding the Customization settings and API credentials
            var pageContainer = ["aps/PageContainer", { id: "page" }, [ fsCustom, fsAPI ]];
            
            // on Page Display
            load(pageContainer).then(function () {
                // Submit button - Click
                aps.app.onSubmit = function () {
                    var page = registry.byId("page");
                    // Check every required field to be no empty
                    if (page.validate()) {
                        // Save new or existing data
                        store.put(getPlainValue(model)).then(function () {
                            aps.apsc.gotoView("mozyProConf.list");
                        // Error in creating or updating the resource
                        }).otherwise(function (confError) {
                            console.log("Error updating/creating the Branding resource");
                            console.dir(confError);
                            displayMessage(_("Cannot update Branding resource"), "error");
                            aps.apsc.cancelProcessing();
                        });
                    // There is some field empty
                    } else {
                        displayMessage(_("Required fields have to be filled"), "warning");
                        aps.apsc.cancelProcessing();
                    }
                };
                
                // Cancel button - Click
                aps.app.onCancel = function () {
                    aps.apsc.gotoView("mozyProConf.list");
                };
            });
            
            
        });

